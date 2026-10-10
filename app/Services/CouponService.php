<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * Mã hợp lệ cho khách với tạm tính này, sai thì ném lỗi gắn vào ô coupon_code.
     * $lock = true khi gọi trong transaction lúc đặt đơn (khoá dòng, chống 2 người dùng lượt cuối).
     */
    public function find(string $code, User $user, int $subtotal, bool $lock = false): Coupon
    {
        $query = Coupon::where('code', strtoupper(trim($code)));
        $coupon = ($lock ? $query->lockForUpdate() : $query)->first();

        $error = match (true) {
            !$coupon || !$coupon->is_active                              => 'Mã giảm giá không tồn tại hoặc đã ngừng áp dụng.',
            $coupon->started_at?->isFuture() ?? false                    => 'Mã chưa đến ngày sử dụng.',
            $coupon->expired_at?->isPast() ?? false                      => 'Mã đã hết hạn.',
            $coupon->max_uses > 0 && $coupon->used_count >= $coupon->max_uses => 'Mã đã hết lượt sử dụng.',
            $subtotal < $coupon->min_order_value                         => 'Đơn tối thiểu ' . number_format($coupon->min_order_value, 0, ',', '.') . 'đ để dùng mã này.',
            CouponUsage::where('coupon_id', $coupon->id)->where('user_id', $user->id)->count() >= $coupon->max_uses_per_user
                                                                         => 'Bạn đã dùng hết lượt của mã này.',
            default                                                      => null,
        };

        if ($error) {
            throw ValidationException::withMessages(['coupon_code' => $error]);
        }

        return $coupon;
    }

    public function redeem(Coupon $coupon, User $user, Order $order): void
    {
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
            'order_id'  => $order->id,
            'used_at'   => now(),
        ]);
        $coupon->increment('used_count');
    }

    /** Đơn bị huỷ: trả lại lượt dùng mã */
    public function release(Order $order): void
    {
        if (!$order->coupon_id) {
            return;
        }

        if (CouponUsage::where('order_id', $order->id)->delete()) {
            Coupon::whereKey($order->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
        }
    }
}
