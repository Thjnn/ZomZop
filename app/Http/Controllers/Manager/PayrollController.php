<?php

namespace App\Http\Controllers\Manager;

use App\Models\Payroll;
use App\Models\PayrollAdjustment;
use App\Services\BranchPayroll;
use App\Support\ExcelReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayrollController extends ManagerController
{
    public const STATUS = ['draft' => 'Nháp', 'confirmed' => 'Đã chốt', 'paid' => 'Đã trả'];

    /** Tháng đang xem (ngày 1); sai định dạng → tháng hiện tại */
    private function month(Request $request): Carbon
    {
        $month = Validator::make($request->only('month'), ['month' => ['nullable', 'date_format:Y-m']])->valid()['month'] ?? null;

        return $month ? Carbon::parse("{$month}-01") : today()->startOfMonth();
    }

    private function rows(int $branchId, Carbon $month): Collection
    {
        return Payroll::ofBranch($branchId)->ofMonth($month->month, $month->year)
            ->with(['user.latestSalary', 'adjustments.creator'])
            ->get()
            ->sortBy('user.name')
            ->values();
    }

    private function ensureOwn(Payroll $payroll): void
    {
        abort_if((int) $payroll->branch_id !== $this->branchId(), 404);
    }

    /** Tính lại dòng nháp mỗi lần xem để số liệu không cũ; tháng tương lai chưa có gì để tính */
    private function ensureCalculated(int $branchId, Carbon $month): void
    {
        if ($month->lte(today()->startOfMonth())) {
            app(BranchPayroll::class)->calculate($branchId, $month->month, $month->year);
        }
    }

    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $month    = $this->month($request);
        $this->ensureCalculated($branchId, $month);

        return view('manager.payrolls.index', ['month' => $month, 'payrolls' => $this->rows($branchId, $month), 'status' => self::STATUS]);
    }

    public function export(Request $request)
    {
        $branchId = $this->branchId();
        $p = $this->period($request, $this->month($request), ['month', 'year']);

        $months = [];
        for ($m = $p['from']->copy(); $m->lte($p['to']) && $m->lte(today()); $m->addMonth()) {
            $this->ensureCalculated($branchId, $m);
            $months[$m->format('Y-m')] = $this->rows($branchId, $m);
        }
        $all = collect($months)->flatten(1);

        $x = (new ExcelReport('Bảng lương', $this->branchName() . ' · ' . $p['label']))->sheet('Tổng hợp');
        $x->summary([
            'Số nhân viên'   => [$all->pluck('user_id')->unique()->count(), 'int'],
            'Tổng giờ làm'   => [(float) $all->sum('total_hours'), 'dec'],
            'Tổng thưởng'    => [(int) $all->sum('bonus'), 'money'],
            'Tổng phạt'      => [(int) $all->sum('deduction'), 'money'],
            'Tổng chi lương' => [(int) $all->sum('total'), 'money'],
        ]);

        $perStaff = $all->groupBy('user_id')->map(fn ($g) => [
            $g->first()->user->name,
            StaffController::ROLES[$g->first()->user->role] ?? $g->first()->user->role,
            (float) $g->sum('total_hours'),
            (int) $g->sum('total_days'),
            (int) $g->sum('base_salary'),
            (int) $g->sum('bonus'),
            (int) $g->sum('deduction'),
            (int) $g->sum('total'),
            $p['period'] === 'month' ? self::STATUS[$g->first()->status] : $g->count() . ' tháng',
        ])->sortBy(0)->values();
        $t = $x->table(['Nhân viên', 'Vai trò', 'Số giờ', 'Ngày công', 'Lương cơ bản', 'Thưởng', 'Phạt', 'Tổng', 'Trạng thái'], $perStaff,
            ['C' => 'dec', 'D' => 'int', 'E' => 'money', 'F' => 'money', 'G' => 'money', 'H' => 'money'], ['C', 'D', 'E', 'F', 'G', 'H'],
            fn ($r) => ['Đã trả' => ExcelReport::GREEN, 'Nháp' => ExcelReport::AMBER][$r[8]] ?? null, 'Theo nhân viên');
        $x->chart('bar', 'Tổng lương theo nhân viên', $t, 'A', ['H']);

        if ($p['period'] === 'year') {
            $rows = collect(range(1, 12))->map(function ($m) use ($p, $months) {
                $g = $months[$p['from']->copy()->month($m)->format('Y-m')] ?? collect();

                return [sprintf('%02d/%d', $m, $p['from']->year), $g->count(), (float) $g->sum('total_hours'), (int) $g->sum('total')];
            });
            $t = $x->table(['Tháng', 'Số NV', 'Số giờ', 'Tổng lương'], $rows, ['B' => 'int', 'C' => 'dec', 'D' => 'money'], ['C', 'D'], null, 'Theo tháng');
            $x->chart('line', 'Chi lương theo tháng', $t, 'A', ['D']);

            $x->sheet('Chi tiết')->table(['Tháng', 'Nhân viên', 'Số giờ', 'Ngày công', 'Lương cơ bản', 'Thưởng', 'Phạt', 'Tổng', 'Trạng thái'],
                $all->map(fn (Payroll $r) => [sprintf('%02d/%d', $r->month, $r->year), $r->user->name, (float) $r->total_hours, $r->total_days,
                    $r->base_salary, $r->bonus, $r->deduction, $r->total, self::STATUS[$r->status]]),
                ['C' => 'dec', 'E' => 'money', 'F' => 'money', 'G' => 'money', 'H' => 'money'], ['H']);
        }

        return $x->download("bang-luong-{$p['slug']}.xlsx");
    }

    public function recalculate(Request $request, BranchPayroll $service)
    {
        $month = $this->month($request);
        $service->calculate($this->branchId(), $month->month, $month->year);

        return redirect()->route('manager.payrolls.index', ['month' => $month->format('Y-m')])
            ->with('success', 'Đã tính lại lương tháng ' . $month->format('m/Y') . ' (các dòng còn nháp).');
    }

    public function storeAdjustment(Request $request, Payroll $payroll)
    {
        return $this->saveAdjustment($request, $payroll, new PayrollAdjustment(['created_by' => $request->user()->id]), 'Đã thêm');
    }

    public function updateAdjustment(Request $request, PayrollAdjustment $adjustment)
    {
        return $this->saveAdjustment($request, $adjustment->payroll, $adjustment, 'Đã sửa');
    }

    public function destroyAdjustment(PayrollAdjustment $adjustment)
    {
        $payroll = $adjustment->payroll;
        if ($error = $this->adjustable($payroll)) {
            return $error;
        }

        return $this->withinTotal($payroll, fn () => $adjustment->delete(), 'Đã xoá');
    }

    private function saveAdjustment(Request $request, Payroll $payroll, PayrollAdjustment $adjustment, string $verb)
    {
        if ($error = $this->adjustable($payroll)) {
            return $error;
        }

        $data = $request->validate([
            'type'   => ['required', Rule::in(array_keys(PayrollAdjustment::TYPES))],
            'amount' => ['required', 'integer', 'min:1000', 'max:999999999'],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'type.*'          => 'Chọn thưởng hoặc phạt.',
            'amount.*'        => 'Số tiền phải là số nguyên từ 1.000 đến 999.999.999.',
            'reason.required' => 'Vui lòng ghi lý do thưởng / phạt.',
            'reason.max'      => 'Lý do tối đa 255 ký tự.',
        ]);

        return $this->withinTotal($payroll, fn () => $adjustment->fill($data + ['payroll_id' => $payroll->id])->save(), $verb);
    }

    /** Dòng lương của chi nhánh mình và còn nháp mới được thêm/sửa/xoá khoản */
    private function adjustable(Payroll $payroll)
    {
        $this->ensureOwn($payroll);

        return $payroll->isDraft() ? null : back()->withErrors(['payroll' => 'Chỉ sửa được dòng lương còn nháp.']);
    }

    /** Ghi thay đổi rồi cộng lại tổng; tổng âm thì huỷ cả thay đổi */
    private function withinTotal(Payroll $payroll, callable $change, string $verb)
    {
        try {
            DB::transaction(function () use ($payroll, $change) {
                $change();
                $payroll->recalcAdjustments() ?? throw new \RangeException();
            });
            $ok = true;
        } catch (\RangeException) {
            $ok = false;
        }

        $back = redirect()->to(route('manager.payrolls.index', ['month' => sprintf('%d-%02d', $payroll->year, $payroll->month)]) . '#adj-' . $payroll->id);

        return $ok
            ? $back->with('success', "{$verb} khoản thưởng / phạt của {$payroll->user->name}.")
            : $back->withErrors(['amount' => 'Phạt không được lớn hơn lương cơ bản + thưởng.'])->withInput();
    }

    public function confirm(Payroll $payroll)
    {
        return $this->move($payroll, 'draft', 'confirmed', 'Đã chốt lương');
    }

    public function pay(Payroll $payroll)
    {
        return $this->move($payroll, 'confirmed', 'paid', 'Đã đánh dấu trả lương');
    }

    private function move(Payroll $payroll, string $from, string $to, string $message)
    {
        $this->ensureOwn($payroll);

        if ($payroll->status !== $from) {
            return back()->withErrors(['payroll' => 'Dòng lương đang "' . self::STATUS[$payroll->status] . '", không thể chuyển sang "' . self::STATUS[$to] . '".']);
        }

        $payroll->update(['status' => $to]);

        return back()->with('success', "{$message} {$payroll->user->name}.");
    }
}
