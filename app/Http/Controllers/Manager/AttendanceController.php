<?php

namespace App\Http\Controllers\Manager;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use App\Support\XlsxExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttendanceController extends ManagerController
{
    private function date(Request $request): string
    {
        return Validator::make($request->only('date'), ['date' => ['nullable', 'date_format:Y-m-d']])->valid()['date']
            ?? today()->toDateString();
    }

    private function attendancesOn(int $branchId, string $date)
    {
        return Attendance::ofBranch($branchId)
            ->with(['user', 'shift'])
            // Kèm cả lượt chưa chấm ra của ngày trước, để manager thấy và đóng được
            ->where(fn ($q) => $q->whereDate('check_in', $date)
                ->orWhere(fn ($q) => $q->whereNull('check_out')->whereDate('check_in', '<', $date)))
            ->orderBy('check_in')
            ->get();
    }

    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $date = $this->date($request);

        return view('manager.attendances.index', [
            'date'        => $date,
            'attendances' => $this->attendancesOn($branchId, $date),
            'staff'       => User::where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])->where('is_active', true)->orderBy('name')->get(),
            'shifts'      => Shift::ofBranch($branchId)->orderBy('start_time')->get(),
        ]);
    }

    public function export(Request $request)
    {
        $date = $this->date($request);

        $rows = $this->attendancesOn($this->branchId(), $date)->map(fn (Attendance $a) => [
            $a->user?->name,
            $a->shift?->name,
            $a->check_in->format('d/m/Y H:i'),
            $a->check_out?->format('d/m/Y H:i'),
            $a->check_out ? $a->working_hours : null,
            $a->method === 'face' ? 'Khuôn mặt' : 'Thủ công',
            $a->note,
        ]);

        return XlsxExport::download("cham-cong-{$date}.xlsx", ['Nhân viên', 'Ca', 'Giờ vào', 'Giờ ra', 'Số giờ', 'Cách chấm', 'Ghi chú'], $rows);
    }

    public function store(Request $request)
    {
        $branchId = $this->branchId();

        $data = $request->validate([
            'user_id'   => ['required', Rule::exists('users', 'id')->where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])],
            'shift_id'  => ['required', Rule::exists('shifts', 'id')->where('branch_id', $branchId)],
            'check_in'  => ['required', 'date_format:Y-m-d\TH:i', 'before_or_equal:now'],
            'check_out' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:check_in', 'before_or_equal:now'],
            'note'      => ['nullable', 'string', 'max:255'],
        ], [
            'user_id.exists'        => 'Nhân viên không thuộc chi nhánh của bạn.',
            'shift_id.exists'       => 'Ca làm không thuộc chi nhánh của bạn.',
            'check_in.date_format'  => 'Giờ vào không hợp lệ.',
            'check_out.date_format' => 'Giờ ra không hợp lệ.',
            'check_out.after'       => 'Giờ ra phải sau giờ vào.',
            'check_in.before_or_equal'  => 'Giờ vào không được ở tương lai.',
            'check_out.before_or_equal' => 'Giờ ra không được ở tương lai.',
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

    /** Ảnh bằng chứng chấm công khuôn mặt (thư mục riêng tư, không có link trực tiếp) */
    public function photo(Attendance $attendance, string $kind)
    {
        abort_if((int) $attendance->branch_id !== $this->branchId(), 404);

        $path = $attendance->photoFile($kind);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function checkout(Attendance $attendance)
    {
        abort_if((int) $attendance->branch_id !== $this->branchId(), 404);

        if ($attendance->check_out) {
            return back()->withErrors(['attendance' => 'Lượt này đã chấm ra rồi.']);
        }

        if (now()->lte($attendance->check_in)) {
            return back()->withErrors(['attendance' => 'Giờ vào của lượt này ở tương lai, không chấm ra được.']);
        }

        $attendance->update(['check_out' => now()]);

        return back()->with('success', 'Đã chấm ra lúc ' . now()->format('H:i') . '.');
    }
}
