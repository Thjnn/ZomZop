<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\KioskDevice;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Nhân viên đã bấm nút (Chấm vào / Chấm ra) và được nhận diện ở máy quầy → ghi chấm công.
 * Giờ luôn lấy theo server, không tin giờ của máy quầy.
 *
 * $action: 'in' | 'out'. $reason: lý do đi trễ / ra sớm (gửi lại sau khi server yêu cầu).
 *
 * Kết quả `status`:
 *   checked_in | checked_out          — đã ghi
 *   need_late_reason | need_early_reason — chưa ghi, cần lý do (kèm `minutes`)
 *   already_in | not_in | no_shift | duplicate — không ghi gì
 */
class FacePunch
{
    /** @return array{status: string, attendance: ?Attendance, minutes?: int} */
    public function punch(KioskDevice $device, User $user, ?float $confidence, string $action, ?string $reason = null): array
    {
        $reason = trim((string) $reason) ?: null;

        return DB::transaction(function () use ($device, $user, $confidence, $action, $reason) {
            // Khoá theo người để 2 lần gửi cùng lúc không tạo 2 lượt
            User::whereKey($user->id)->lockForUpdate()->first();

            $now  = now();
            $cfg  = config('attendance.face');
            $mine = Attendance::where('user_id', $user->id)->where('branch_id', $device->branch_id);

            $recent = (clone $mine)
                ->where(fn ($q) => $q->where('check_in', '>=', $now->copy()->subMinutes($cfg['cooldown_minutes']))
                    ->orWhere('check_out', '>=', $now->copy()->subMinutes($cfg['cooldown_minutes'])))
                ->latest('check_in')->first();
            if ($recent) {
                return ['status' => 'duplicate', 'attendance' => $recent];
            }

            // Lượt mở quá lâu là quên chấm ra hôm trước → để manager đóng tay, không tính là đang trong ca
            $open = (clone $mine)->whereNull('check_out')
                ->where('check_in', '>=', $now->copy()->subHours($cfg['stale_hours']))
                ->with('shift')->latest('check_in')->first();

            return $action === 'in'
                ? $this->checkIn($device, $user, $confidence, $open, $reason, $now)
                : $this->checkOut($open, $reason, $now);
        });
    }

    private function checkIn(KioskDevice $device, User $user, ?float $confidence, ?Attendance $open, ?string $reason, Carbon $now): array
    {
        if ($open) {
            return ['status' => 'already_in', 'attendance' => $open];
        }

        $shift = $this->shiftAt($device->branch_id, $now);
        if (!$shift) {
            return ['status' => 'no_shift', 'attendance' => null];
        }

        [$start] = $shift->occurrenceAround($now);
        $late = $now->gt($start) ? (int) floor($start->diffInMinutes($now)) : 0;
        $isLate = $late > config('attendance.face.late_minutes');
        if ($isLate && !$reason) {
            return ['status' => 'need_late_reason', 'attendance' => null, 'minutes' => $late];
        }

        $attendance = Attendance::create([
            'user_id'         => $user->id,
            'branch_id'       => $device->branch_id,
            'shift_id'        => $shift->id,
            'check_in'        => $now,
            'method'          => 'face',
            'face_confidence' => $confidence,
            'late_reason'     => $isLate ? $reason : null,
            'note'            => "Thiết bị: {$device->name}",
        ]);

        return ['status' => 'checked_in', 'attendance' => $attendance];
    }

    private function checkOut(?Attendance $open, ?string $reason, Carbon $now): array
    {
        if (!$open) {
            return ['status' => 'not_in', 'attendance' => null];
        }

        $early = 0;
        if ($open->shift) {
            [, $end] = $open->shift->occurrenceAround($open->check_in);
            $early = $now->lt($end) ? (int) floor($now->diffInMinutes($end)) : 0;
        }
        $isEarly = $early > config('attendance.face.early_minutes');
        if ($isEarly && !$reason) {
            return ['status' => 'need_early_reason', 'attendance' => $open, 'minutes' => $early];
        }

        $open->update(['check_out' => $now, 'early_reason' => $isEarly ? $reason : null]);

        return ['status' => 'checked_out', 'attendance' => $open];
    }

    /**
     * Ca đang diễn ra (cho phép vào sớm 60 phút); nhiều ca thoả thì lấy ca có giờ bắt đầu gần nhất.
     * Ca qua đêm (giờ kết thúc ≤ giờ bắt đầu) kéo sang hôm sau; xét cả ca bắt đầu từ hôm qua.
     */
    public function shiftAt(int $branchId, Carbon $now): ?Shift
    {
        $best = null;
        $bestGap = null;

        foreach (Shift::ofBranch($branchId)->get() as $shift) {
            foreach ([-1, 0] as $dayOffset) {
                $day   = $now->copy()->addDays($dayOffset)->toDateString();
                $start = Carbon::parse("{$day} {$shift->start_time}");
                $end   = Carbon::parse("{$day} {$shift->end_time}");
                if ($end->lte($start)) {
                    $end->addDay();
                }

                if ($now->between($start->copy()->subMinutes(60), $end)) {
                    $gap = abs($now->diffInMinutes($start));
                    if ($bestGap === null || $gap < $bestGap) {
                        [$best, $bestGap] = [$shift, $gap];
                    }
                }
            }
        }

        return $best;
    }
}
