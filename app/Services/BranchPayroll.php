<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\SalaryConfig;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BranchPayroll
{
    /**
     * Tính (lại) lương tháng cho staff/kitchen của chi nhánh.
     * Chỉ ghi đè dòng còn nháp; giữ thưởng/phạt đã nhập.
     */
    public function calculate(int $branchId, int $month, int $year): void
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::ofBranch($branchId)
            ->whereBetween('check_in', [$start, $end])
            ->whereNotNull('check_out')
            ->get()
            ->groupBy('user_id');

        $existing = Payroll::ofBranch($branchId)->ofMonth($month, $year)->get()->keyBy('user_id');

        // Người đang làm + người đã có giờ/dòng lương tháng này (kể cả đã bị khoá)
        $users = User::where('branch_id', $branchId)
            ->whereIn('role', ['staff', 'kitchen'])
            ->where(fn ($q) => $q->where('is_active', true)
                ->orWhereIn('id', $attendances->keys())
                ->orWhereIn('id', $existing->keys()))
            ->get();

        $configs = SalaryConfig::whereIn('user_id', $users->pluck('id'))
            ->whereDate('effective_from', '<=', $end->toDateString())
            ->orderBy('effective_from')->orderBy('id')
            ->get()
            ->groupBy('user_id');

        foreach ($users as $user) {
            $rows        = $attendances->get($user->id, collect());
            $userConfigs = $configs->get($user->id, collect());
            $payroll     = $existing->get($user->id);

            if (($rows->isEmpty() && $userConfigs->isEmpty()) || ($payroll && !$payroll->isDraft())) {
                continue;
            }

            $hours = 0.0;
            $base  = 0.0;
            foreach ($rows as $a) {
                $h      = $a->check_in->diffInMinutes($a->check_out) / 60;
                $hours += $h;
                $base  += $h * $this->rateAt($user, $userConfigs, $a->check_in);
            }

            $base      = (int) round($base);
            $bonus     = $payroll?->bonus ?? 0;
            $deduction = $payroll?->deduction ?? 0;

            Payroll::updateOrCreate(
                ['user_id' => $user->id, 'branch_id' => $branchId, 'month' => $month, 'year' => $year],
                [
                    'total_hours' => round($hours, 2),
                    'total_days'  => $rows->map(fn ($a) => $a->check_in->toDateString())->unique()->count(),
                    'base_salary' => $base,
                    'bonus'       => $bonus,
                    'deduction'   => $deduction,
                    'total'       => $base + $bonus - $deduction,
                    'status'      => 'draft',
                ]
            );
        }
    }

    /** Lương/giờ áp dụng cho một lượt: config mới nhất tính đến ngày đó, thử việc thì dùng mức thử việc */
    private function rateAt(User $user, Collection $configs, Carbon $at): int
    {
        $config = $configs->last(fn ($c) => $c->effective_from->toDateString() <= $at->toDateString());

        if (!$config) {
            return 0;
        }

        return $user->isOnProbation($at) && $config->probation_rate !== null
            ? (int) $config->probation_rate
            : (int) $config->rate;
    }
}
