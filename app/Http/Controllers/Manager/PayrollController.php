<?php

namespace App\Http\Controllers\Manager;

use App\Models\Payroll;
use App\Services\BranchPayroll;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

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
            ->with('user.latestSalary')
            ->get()
            ->sortBy('user.name')
            ->values();
    }

    private function ensureOwn(Payroll $payroll): void
    {
        abort_if((int) $payroll->branch_id !== $this->branchId(), 404);
    }

    public function index(Request $request, BranchPayroll $service)
    {
        $branchId = $this->branchId();
        $month    = $this->month($request);

        if (!Payroll::ofBranch($branchId)->ofMonth($month->month, $month->year)->exists()) {
            $service->calculate($branchId, $month->month, $month->year);
        }

        return view('manager.payrolls.index', ['month' => $month, 'payrolls' => $this->rows($branchId, $month), 'status' => self::STATUS]);
    }

    public function recalculate(Request $request, BranchPayroll $service)
    {
        $month = $this->month($request);
        $service->calculate($this->branchId(), $month->month, $month->year);

        return redirect()->route('manager.payrolls.index', ['month' => $month->format('Y-m')])
            ->with('success', 'Đã tính lại lương tháng ' . $month->format('m/Y') . ' (các dòng còn nháp).');
    }

    public function update(Request $request, Payroll $payroll)
    {
        $this->ensureOwn($payroll);

        if (!$payroll->isDraft()) {
            return back()->withErrors(['payroll' => 'Chỉ sửa được dòng lương còn nháp.']);
        }

        $data = $request->validate([
            'bonus'     => ['required', 'integer', 'min:0', 'max:999999999'],
            'deduction' => ['required', 'integer', 'min:0', 'max:999999999'],
        ], [
            'bonus.*'     => 'Thưởng phải là số nguyên từ 0 đến 999.999.999.',
            'deduction.*' => 'Phạt phải là số nguyên từ 0 đến 999.999.999.',
        ]);

        $total = $payroll->base_salary + (int) $data['bonus'] - (int) $data['deduction'];
        if ($total < 0) {
            return back()->withErrors(['deduction' => 'Phạt không được lớn hơn lương cơ bản + thưởng.']);
        }

        $payroll->update(['bonus' => $data['bonus'], 'deduction' => $data['deduction'], 'total' => $total]);

        return back()->with('success', "Đã cập nhật lương {$payroll->user->name}.");
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
