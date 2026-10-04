<?php

namespace App\Http\Controllers\Manager;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttendanceController extends ManagerController
{
    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $date = Validator::make($request->only('date'), ['date' => ['nullable', 'date_format:Y-m-d']])->valid()['date']
            ?? today()->toDateString();

        $attendances = Attendance::ofBranch($branchId)
            ->with(['user', 'shift'])
            ->whereDate('check_in', $date)
            ->orderBy('check_in')
            ->get();

        return view('manager.attendances.index', [
            'date'        => $date,
            'attendances' => $attendances,
            'staff'       => User::where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])->where('is_active', true)->orderBy('name')->get(),
            'shifts'      => Shift::ofBranch($branchId)->orderBy('start_time')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $branchId = $this->branchId();

        $data = $request->validate([
            'user_id'   => ['required', Rule::exists('users', 'id')->where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])],
            'shift_id'  => ['required', Rule::exists('shifts', 'id')->where('branch_id', $branchId)],
            'check_in'  => ['required', 'date_format:Y-m-d\TH:i'],
            'check_out' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:check_in'],
            'note'      => ['nullable', 'string', 'max:255'],
        ], [
            'user_id.exists'        => 'Nhân viên không thuộc chi nhánh của bạn.',
            'shift_id.exists'       => 'Ca làm không thuộc chi nhánh của bạn.',
            'check_in.date_format'  => 'Giờ vào không hợp lệ.',
            'check_out.date_format' => 'Giờ ra không hợp lệ.',
            'check_out.after'       => 'Giờ ra phải sau giờ vào.',
        ]);

        $onShift = Attendance::where('user_id', $data['user_id'])->whereNull('check_out')->exists();
        if ($onShift && empty($data['check_out'])) {
            return back()->withErrors(['user_id' => 'Nhân viên này đang trong ca (chưa chấm ra).'])->withInput();
        }

        Attendance::create([
            'user_id'   => $data['user_id'],
            'branch_id' => $branchId,
            'shift_id'  => $data['shift_id'],
            'check_in'  => Carbon::createFromFormat('Y-m-d\TH:i', $data['check_in']),
            'check_out' => !empty($data['check_out']) ? Carbon::createFromFormat('Y-m-d\TH:i', $data['check_out']) : null,
            'method'    => 'manual',
            'note'      => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Đã chấm công.');
    }

    public function checkout(Attendance $attendance)
    {
        abort_if((int) $attendance->branch_id !== $this->branchId(), 404);

        if ($attendance->check_out) {
            return back()->withErrors(['attendance' => 'Lượt này đã chấm ra rồi.']);
        }

        $attendance->update(['check_out' => now()]);

        return back()->with('success', 'Đã chấm ra lúc ' . now()->format('H:i') . '.');
    }
}
