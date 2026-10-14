<?php

namespace App\Http\Controllers\Manager;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use App\Support\ExcelReport;
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

        // Lịch tháng chứa ngày đang xem, tuần bắt đầu thứ Hai
        $month     = Carbon::parse($date)->startOfMonth();
        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        return view('manager.attendances.index', [
            'date'        => $date,
            'month'       => $month,
            'calendar'    => $gridStart->daysUntil($gridEnd),
            'byDay'       => Attendance::ofBranch($branchId)->with('user:id,name')
                ->whereBetween('check_in', [$gridStart, $gridEnd->copy()->endOfDay()])
                ->orderBy('check_in')->get()
                ->groupBy(fn (Attendance $a) => $a->check_in->toDateString()),
            'attendances' => $this->attendancesOn($branchId, $date),
            'staff'       => User::where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])->where('is_active', true)->orderBy('name')->get(),
            'shifts'      => Shift::ofBranch($branchId)->orderBy('start_time')->get(),
        ]);
    }

    public function export(Request $request)
    {
        $p = $this->period($request, Carbon::parse($this->date($request)));
        $list = Attendance::ofBranch($this->branchId())->with(['user', 'shift'])
            ->whereBetween('check_in', [$p['from'], $p['to']])->orderBy('check_in')->get();

        $x = (new ExcelReport('Bảng chấm công', $this->branchName() . ' · ' . $p['label']))->sheet('Tổng hợp');
        $closed = $list->whereNotNull('check_out');
        $x->summary([
            'Số lượt chấm công' => [$list->count(), 'int'],
            'Số nhân viên'      => [$list->pluck('user_id')->unique()->count(), 'int'],
            'Tổng giờ làm'      => [$closed->sum('working_hours'), 'dec'],
            'Lượt đi trễ'       => [$list->whereNotNull('late_reason')->count(), 'int'],
            'Tỉ lệ đúng giờ'    => [$list->count() ? round(100 * $list->whereNull('late_reason')->count() / $list->count(), 1) : 0, 'pct'],
        ]);

        $perStaff = $list->groupBy('user_id')->map(fn ($g) => [
            $g->first()->user?->name,
            StaffController::ROLES[$g->first()->user?->role] ?? '',
            $g->count(),
            round($g->whereNotNull('check_out')->sum('working_hours'), 2),
            $g->whereNotNull('late_reason')->count(),
            $g->whereNotNull('early_reason')->count(),
            $g->whereNull('check_out')->count(),
        ])->sortBy(0)->values();
        $t = $x->table(['Nhân viên', 'Vai trò', 'Số lượt', 'Số giờ', 'Đi trễ', 'Ra sớm', 'Chưa chấm ra'], $perStaff,
            ['C' => 'int', 'D' => 'dec', 'E' => 'int', 'F' => 'int', 'G' => 'int'], ['C', 'D', 'E', 'F', 'G'],
            fn ($r) => $r[4] >= 3 ? ExcelReport::AMBER : null, 'Theo nhân viên');
        $x->chart('bar', 'Số giờ làm theo nhân viên', $t, 'A', ['D']);

        if ($p['period'] !== 'day') {
            $grain = ExcelReport::grain($p['from'], $p['to']);
            $byTime = $list->groupBy(fn ($a) => ExcelReport::bucketOf($a->check_in, $grain));
            $rows = collect(ExcelReport::buckets($p['from'], $p['to']))->map(fn ($label, $key) => [
                $label, $byTime->get($key)?->count() ?? 0,
                round($byTime->get($key)?->whereNotNull('check_out')->sum('working_hours') ?? 0, 2),
                $byTime->get($key)?->whereNotNull('late_reason')->count() ?? 0,
            ])->values();
            $t = $x->table([$grain === 'day' ? 'Ngày' : 'Tháng', 'Số lượt', 'Số giờ', 'Đi trễ'], $rows,
                ['B' => 'int', 'C' => 'dec', 'D' => 'int'], ['B', 'C', 'D'], null, 'Theo thời gian');
            $x->chart('line', 'Số lượt chấm công & đi trễ', $t, 'A', ['B', 'D']);
        }

        $x->sheet('Chi tiết')->table(
            ['Nhân viên', 'Ca', 'Giờ vào', 'Giờ ra', 'Số giờ', 'Cách chấm', 'Ghi chú', 'Lý do trễ', 'Lý do ra sớm'],
            $list->map(fn (Attendance $a) => [
                $a->user?->name,
                $a->shift?->name,
                $a->check_in->format('d/m/Y H:i'),
                $a->check_out?->format('d/m/Y H:i') ?? 'Chưa chấm ra',
                $a->check_out ? $a->working_hours : null,
                $a->method === 'face' ? 'Khuôn mặt' : 'Thủ công',
                $a->note,
                $a->late_reason ? "Trễ {$a->lateMinutes()}' · {$a->late_reason}" : null,
                $a->early_reason ? "Sớm {$a->earlyMinutes()}' · {$a->early_reason}" : null,
            ]),
            ['E' => 'dec'], ['E'],
            fn ($r) => $r[3] === 'Chưa chấm ra' ? ExcelReport::RED : ($r[7] || $r[8] ? ExcelReport::AMBER : null),
        );

        return $x->download("cham-cong-{$p['slug']}.xlsx");
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
