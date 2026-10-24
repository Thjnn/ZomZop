<?php

namespace App\Services;

use App\Models\FaceDescriptor;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * So khớp descriptor khuôn mặt (128 số từ face-api.js) với nhân viên đã đăng ký.
 * Khoảng cách Euclid càng nhỏ càng giống; mỗi người lấy mẫu gần nhất trong các mẫu đã đăng ký.
 * So với nhân viên MỌI chi nhánh: nếu chỉ so trong chi nhánh, người chi nhánh khác đứng trước máy
 * sẽ bị gán nhầm cho người giống nhất ở đây.
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
        $ranked    = $this->ranked($descriptor);
        $threshold = config('attendance.face.threshold');
        $best      = $ranked->first();

        // Giống nhất là người chi nhánh khác → không phải nhân viên ở đây
        if (!$best || $best['distance'] > $threshold || (int) $best['user']->branch_id !== $branchId) {
            return null;
        }

        // Hai người quá giống nhau → không đoán
        $second = $ranked->get(1);
        if ($second && $second['distance'] - $best['distance'] < config('attendance.face.margin')) {
            return null;
        }

        // Độ tương đồng (1 − d) chỉ để hiển thị; việc nhận/từ chối đã quyết ở threshold + margin phía trên
        return $best + ['confidence' => round(max(0, 1 - $best['distance']) * 100, 2)];
    }

    /** Nhân viên khác (trừ $exceptUserId, mọi chi nhánh) có khuôn mặt trùng — dùng để chặn đăng ký nhầm người */
    public function nearestOther(array $descriptor, int $exceptUserId): ?array
    {
        $hit = $this->ranked($descriptor)->first(fn ($r) => $r['user']->id !== $exceptUserId);

        return $hit && $hit['distance'] <= config('attendance.face.threshold') ? $hit : null;
    }

    /**
     * Mỗi nhân viên đang làm (mọi chi nhánh) kèm khoảng cách nhỏ nhất, sắp tăng dần.
     * ponytail: so tuần tự O(số mẫu) mỗi lần chấm (~150 mẫu/chi nhánh), cần index vector khi lên hàng nghìn
     */
    private function ranked(array $descriptor): Collection
    {
        $users = User::whereIn('role', ['staff', 'kitchen'])
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
