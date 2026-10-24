<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Manager\PayrollController;
use App\Http\Controllers\Manager\ScheduleController;
use App\Models\Attendance;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Support\ExcelReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class WorkController extends Controller
{
    /** Trang "Của tôi": ca hôm nay, chấm công và lương trong tháng */
    public function me(Request $request)
    {
        $user = $request->user();
        abort_unless($user->branch_id, 403, 'Tài khoản chưa được gán chi nhánh.');

        $month = $this->month($request);

        return view('staff.me', [
            'user'        => $user,
            'month'       => $month,
            'shifts'      => Shift::ofBranch($user->branch_id)->orderBy('start_time')->get(),
            'today'       => Attendance::ofUser($user->id)->whereDate('check_in', today())->with('shift')->oldest('check_in')->get(),
            'attendances' => Attendance::ofUser($user->id)->ofMonth($month->month, $month->year)->with('shift')->latest('check_in')->get(),
            'payroll'     => Payroll::ofUser($user->id)->ofMonth($month->month, $month->year)->with('adjustments')->first(),
            'status'      => PayrollController::STATUS,
            'schedule'    => $this->schedule($user->id, $month)->groupBy('work_date'),
        ]);
    }

    /** Tháng đang xem; sai định dạng → tháng hiện tại */
    private function month(Request $request): Carbon
    {
        $m = Validator::make($request->only('month'), ['month' => ['nullable', 'date_format:Y-m']])->valid()['month'] ?? null;

        return $m ? Carbon::parse("{$m}-01") : today()->startOfMonth();
    }

    /** Lịch làm được xếp của chính mình trong tháng, sắp theo ngày rồi giờ bắt đầu ca */
    private function schedule(int $userId, Carbon $month)
    {
        return ShiftAssignment::where('user_id', $userId)
            ->betweenDates($month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString())
            ->with('shift')->get()
            ->sortBy(fn ($a) => $a->work_date . $a->shift->start_time)->values();
    }

    public function exportSchedule(Request $request)
    {
        $user  = $request->user();
        $month = $this->month($request);
        $list  = $this->schedule($user->id, $month);

        $x = (new ExcelReport('Lịch làm việc', $user->name . ' · Tháng ' . $month->format('m/Y')))->sheet('Lịch làm');
        $x->summary(['Số ca được xếp' => [$list->count(), 'int']]);
        $x->table(['Ngày', 'Thứ', 'Ca', 'Giờ'], $list->map(fn ($a) => [
            ($d = Carbon::parse($a->work_date))->format('d/m/Y'),
            ScheduleController::DOW[$d->dayOfWeekIso - 1],
            $a->shift->name,
            substr($a->shift->start_time, 0, 5) . ' – ' . substr($a->shift->end_time, 0, 5),
        ]));

        return $x->download('lich-lam-' . $month->format('Y-m') . '.xlsx');
    }

    /** Màn hình bếp: đơn đã xác nhận (chờ nấu) và đang nấu của chi nhánh, đơn cũ trước */
    public function kitchen(Request $request)
    {
        $branchId = $request->user()->branch_id;
        abort_unless($branchId, 403, 'Tài khoản chưa được gán chi nhánh.');

        $orders = Order::ofBranch($branchId)
            ->whereIn('status', ['confirmed', 'cooking'])
            ->with('items')
            ->oldest()
            ->get()
            ->groupBy('status');

        return view('staff.kitchen', [
            'waiting' => $orders->get('confirmed', collect()),
            'cooking' => $orders->get('cooking', collect()),
        ]);
    }
}
