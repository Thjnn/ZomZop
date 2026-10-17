<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Manager\PayrollController;
use App\Models\Attendance;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\Shift;
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

        // Sai định dạng → tháng hiện tại
        $m     = Validator::make($request->only('month'), ['month' => ['nullable', 'date_format:Y-m']])->valid()['month'] ?? null;
        $month = $m ? Carbon::parse("{$m}-01") : today()->startOfMonth();

        return view('staff.me', [
            'user'        => $user,
            'month'       => $month,
            'shifts'      => Shift::ofBranch($user->branch_id)->orderBy('start_time')->get(),
            'today'       => Attendance::ofUser($user->id)->whereDate('check_in', today())->with('shift')->oldest('check_in')->get(),
            'attendances' => Attendance::ofUser($user->id)->ofMonth($month->month, $month->year)->with('shift')->latest('check_in')->get(),
            'payroll'     => Payroll::ofUser($user->id)->ofMonth($month->month, $month->year)->with('adjustments')->first(),
            'status'      => PayrollController::STATUS,
        ]);
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
