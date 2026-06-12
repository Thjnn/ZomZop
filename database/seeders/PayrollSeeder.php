<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Attendance;
use App\Models\SalaryConfig;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $month = now()->month;
        $year  = now()->year;

        $staffUsers = User::whereIn('role', ['manager', 'staff', 'kitchen'])->get();

        foreach ($staffUsers as $user) {
            // Lấy config lương mới nhất
            $config = SalaryConfig::ofUser($user->id)->latest()->first();
            if (!$config) continue;

            // Lấy chấm công tháng này
            $attendances = Attendance::ofUser($user->id)
                ->ofMonth($month, $year)
                ->whereNotNull('check_out')
                ->get();

            $totalDays  = $attendances->count();
            $totalHours = $attendances->sum(fn($a) => $a->working_hours);

            // Tính lương cơ bản
            if ($config->isHourly()) {
                $baseSalary = (int) round($totalHours * $config->rate);
            } else {
                $baseSalary = (int) $config->rate;
            }

            // Thưởng + khấu trừ mẫu
            $bonus     = $totalDays >= 25 ? 500000 : 0; // chuyên cần
            $deduction = 0;
            $total     = $baseSalary + $bonus - $deduction;

            Payroll::create([
                'user_id'     => $user->id,
                'branch_id'   => $user->branch_id,
                'month'       => $month,
                'year'        => $year,
                'total_hours' => round($totalHours, 2),
                'total_days'  => $totalDays,
                'base_salary' => $baseSalary,
                'bonus'       => $bonus,
                'deduction'   => $deduction,
                'total'       => $total,
                'status'      => 'draft',
            ]);
        }
    }
}
