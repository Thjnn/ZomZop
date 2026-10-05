<?php

namespace App\Services;

use App\Models\FaceDescriptor;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * So khớp descriptor khuôn mặt (128 số từ face-api.js) với nhân viên đã đăng ký của một chi nhánh.
 * Khoảng cách Euclid càng nhỏ càng giống; mỗi người lấy mẫu gần nhất trong các mẫu đã đăng ký.
 */
class FaceMatcher
{
    /**
     * Người khớp nhất, hoặc null nếu không đủ chắc chắn.
     *
     * @return array{user: User, distance: float, confidence: float}|null
     */
    public function match(int $branchId, array $descriptor): ?array
    {
        $ranked    = $this->ranked($branchId, $descriptor);
        $threshold = config('attendance.face.threshold');
        $best      = $ranked->first();

        if (!$best || $best['distance'] > $threshold) {
            return null;
        }

        // Hai người quá giống nhau → không đoán
        $second = $ranked->get(1);
        if ($second && $second['distance'] - $best['distance'] < config('attendance.face.margin')) {
            return null;
        }

        return $best + ['confidence' => round(max(0, 1 - $best['distance'] / $threshold) * 100, 2)];
    }

    /** Nhân viên khác (trừ $exceptUserId) có khuôn mặt trùng — dùng để chặn đăng ký nhầm người */
    public function nearestOther(int $branchId, array $descriptor, int $exceptUserId): ?array
    {
        $hit = $this->ranked($branchId, $descriptor)->first(fn ($r) => $r['user']->id !== $exceptUserId);

        return $hit && $hit['distance'] <= config('attendance.face.threshold') ? $hit : null;
    }

    /**
     * Mỗi nhân viên đang làm của chi nhánh kèm khoảng cách nhỏ nhất, sắp tăng dần.
     * ponytail: so tuần tự O(số mẫu) mỗi lần chấm (~150 mẫu/chi nhánh), cần index vector khi lên hàng nghìn
     */
    private function ranked(int $branchId, array $descriptor): Collection
    {
        $users = User::where('branch_id', $branchId)
            ->whereIn('role', ['staff', 'kitchen'])
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        return FaceDescriptor::whereIn('user_id', $users->keys())->get()
            ->groupBy('user_id')
            ->map(fn ($samples, $userId) => [
                'user'     => $users[$userId],
                'distance' => $samples->min(fn ($s) => self::distance($descriptor, $s->descriptor)),
            ])
            ->sortBy('distance')
            ->values();
    }

    public static function distance(array $a, array $b): float
    {
        $sum = 0.0;
        foreach ($a as $i => $v) {
            $d    = $v - ($b[$i] ?? 0);
            $sum += $d * $d;
        }

        return sqrt($sum);
    }
}
