<?php

namespace App\Http\Controllers\Manager;

use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Support\ExcelReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/** Xếp lịch làm theo tuần cho nhân viên/bếp của chi nhánh (hiển thị trong trang Ca làm) */
class ScheduleController extends ManagerController
{
    public const DOW = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];

    /** Thứ Hai của tuần đang xem; sai định dạng → tuần hiện tại */
    public static function weekStart(Request $request): Carbon
    {
        $w = Validator::make($request->only('week'), ['week' => ['nullable', 'date_format:Y-m-d']])->valid()['week'] ?? null;

        return ($w ? Carbon::parse($w) : today())->startOfWeek(Carbon::MONDAY);
    }

    /** @return array<string, Carbon> 7 ngày của tuần, khoá Y-m-d */
    public static function days(Carbon $monday): array
    {
        return collect(range(0, 6))->mapWithKeys(fn ($i) => [($d = $monday->copy()->addDays($i))->toDateString() => $d])->all();
    }

    /** Dữ liệu lịch tuần cho view: nhân viên, ca, và tập "userId|ngày|shiftId" đã xếp */
    public function weekData(Carbon $monday): array
    {
        $branchId = $this->branchId();
        $days = self::days($monday);

        return [
            'week'     => $monday,
            'days'     => $days,
            'staff'    => User::where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])->where('is_active', true)->orderBy('name')->get(),
            'assigned' => ShiftAssignment::ofBranch($branchId)->betweenDates(array_key_first($days), array_key_last($days))
                ->get()->mapWithKeys(fn ($a) => ["{$a->user_id}|{$a->work_date}|{$a->shift_id}" => true]),
        ];
    }

    /** Ghi đè toàn bộ lịch của tuần; id người/ca không thuộc chi nhánh bị bỏ qua */
    public function update(Request $request)
    {
        $branchId = $this->branchId();
        $monday = self::weekStart($request);
        $days = self::days($monday);

        $request->validate(['slots' => ['nullable', 'array']]);

        $userIds  = User::where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])->pluck('id')->flip();
        $shiftIds = Shift::ofBranch($branchId)->pluck('id')->flip();

        $rows = [];
        foreach ((array) $request->input('slots', []) as $userId => $byDate) {
            if (!isset($userIds[$userId]) || !is_array($byDate)) continue;
            foreach ($byDate as $date => $shifts) {
                if (!isset($days[$date]) || !is_array($shifts)) continue;
                foreach (array_unique($shifts) as $shiftId) {
                    if (isset($shiftIds[$shiftId])) {
                        $rows[] = ['user_id' => (int) $userId, 'shift_id' => (int) $shiftId, 'work_date' => $date, 'created_at' => now(), 'updated_at' => now()];
                    }
                }
            }
        }

        DB::transaction(function () use ($branchId, $days, $rows) {
            ShiftAssignment::ofBranch($branchId)->betweenDates(array_key_first($days), array_key_last($days))->delete();
            ShiftAssignment::insert($rows);
        });

        return redirect()->route('manager.shifts.index', ['week' => $monday->toDateString()])
            ->with('success', 'Đã lưu lịch tuần ' . $monday->format('d/m') . ' – ' . $monday->copy()->addDays(6)->format('d/m') . '.');
    }

    public function export(Request $request)
    {
        $monday = self::weekStart($request);
        $data = $this->weekData($monday);
        $shifts = Shift::ofBranch($this->branchId())->orderBy('start_time')->get();
        $label = $monday->format('d/m/Y') . ' – ' . $monday->copy()->addDays(6)->format('d/m/Y');

        $x = (new ExcelReport('Lịch làm việc', $this->branchName() . ' · Tuần ' . $label))->sheet('Lịch tuần');
        $x->table(
            array_merge(['Nhân viên', 'Vai trò'], array_map(fn ($d) => self::DOW[$d->dayOfWeekIso - 1] . ' ' . $d->format('d/m'), array_values($data['days']))),
            $data['staff']->map(fn (User $u) => array_merge(
                [$u->name, StaffController::ROLES[$u->role] ?? ''],
                array_map(fn ($date) => $shifts->filter(fn ($s) => isset($data['assigned']["{$u->id}|{$date}|{$s->id}"]))->pluck('name')->implode(', '),
                    array_keys($data['days'])),
            )),
        );

        $detail = ShiftAssignment::ofBranch($this->branchId())->with(['user', 'shift'])
            ->betweenDates(array_key_first($data['days']), array_key_last($data['days']))->get()
            ->sortBy(fn ($a) => $a->work_date . $a->shift->start_time . $a->user->name)
            ->map(fn ($a) => [Carbon::parse($a->work_date)->format('d/m/Y'), $a->user->name, $a->shift->name,
                substr($a->shift->start_time, 0, 5) . ' – ' . substr($a->shift->end_time, 0, 5)]);
        $x->sheet('Chi tiết')->table(['Ngày', 'Nhân viên', 'Ca', 'Giờ'], $detail);

        return $x->download('lich-lam-' . $monday->toDateString() . '.xlsx');
    }
}
