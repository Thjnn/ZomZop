<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\KioskDevice;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Nhân viên đã được nhận diện ở máy quầy → quyết định chấm vào hay chấm ra.
 * Giờ luôn lấy theo server, không tin giờ của máy quầy.
 *
 * Kết quả `status`: checked_in | checked_out | confirm_checkout | duplicate | no_shift
 */
class FacePunch
{
    /** @return array{status: string, attendance: ?Attendance} */
    public function punch(KioskDevice $device, User $user, ?float $confidence, bool $forceCheckout = false): array
    {
        return DB::transaction(function () use ($device, $user, $confidence, $forceCheckout) {
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

            // Lượt mở quá lâu là quên chấm ra hôm trước → để manager đóng tay, không tự đóng
            $open = (clone $mine)->whereNull('check_out')
                ->where('check_in', '>=', $now->copy()->subHours($cfg['stale_hours']))
                ->latest('check_in')->first();

            if ($open) {
                if (!$forceCheckout && $open->check_in->diffInMinutes($now) < $cfg['min_shift_minutes']) {
                    return ['status' => 'confirm_checkout', 'attendance' => $open];
                }
                $open->update(['check_out' => $now]);

                return ['status' => 'checked_out', 'attendance' => $open];
            }

            $shift = $this->shiftAt($device->branch_id, $now);
            if (!$shift) {
                return ['status' => 'no_shift', 'attendance' => null];
            }

            $attendance = Attendance::create([
                'user_id'         => $user->id,
                'branch_id'       => $device->branch_id,
                'shift_id'        => $shift->id,
                'check_in'        => $now,
                'method'          => 'face',
                'face_confidence' => $confidence,
                'note'            => "Thiết bị: {$device->name}",
            ]);

            return ['status' => 'checked_in', 'attendance' => $attendance];
        });
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
