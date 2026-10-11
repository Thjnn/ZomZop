<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;

/**
 * Tính phí giao hàng và kiểm tra / tính giá trị giảm của mã giảm giá.
 */
class CheckoutPricing
{
    public function shippingFee(string $type, string $speed): int
    {
        if ($type !== 'delivery') {
            return 0;
        }

        return (int) config('zomzop.shipping_fee.' . ($speed === 'express' ? 'express' : 'standard'), 0);
    }

    /**
     * @return array{coupon: ?Coupon, discount: int, error: ?string}
     */
    public function evaluate(?string $code, User $user, int $subtotal, string $type, string $speed, bool $lock = false): array
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return ['coupon' => null, 'discount' => 0, 'error' => null];
        }

        $query = Coupon::where('code', $code);
        if ($lock) {
            $query->lockForUpdate();
        }
        $coupon = $query->first();

        if (!$coupon) {
            return $this->fail('Mã giảm giá không tồn tại.');
        }
        if (!$coupon->isValid()) {
            return $this->fail('Mã giảm giá đã hết hạn hoặc hết lượt sử dụng.');
        }
        if ($subtotal < $coupon->min_order_value) {
            return $this->fail('Đơn hàng cần tối thiểu ' . number_format($coupon->min_order_value, 0, ',', '.') . 'đ để dùng mã này.');
        }
        if ($coupon->scope === 'ship' && !($type === 'delivery' && $speed === 'standard')) {
            return $this->fail('Mã này chỉ áp dụng cho đơn giao hàng tiêu chuẩn.');
        }
        if ($coupon->scope === 'express' && !($type === 'delivery' && $speed === 'express')) {
            return $this->fail('Mã này chỉ áp dụng cho đơn giao hàng hỏa tốc.');
        }
        if (CouponUsage::where('coupon_id', $coupon->id)->where('user_id', $user->id)->exists()) {
            return $this->fail('Bạn đã sử dụng mã này rồi.');
        }

        $discount = $coupon->discountFor($subtotal, $this->shippingFee($type, $speed));
        if ($discount <= 0) {
            return $this->fail('Mã này không giảm được cho đơn hàng hiện tại.');
        }

        return ['coupon' => $coupon, 'discount' => $discount, 'error' => null];
    }

    private function fail(string $message): array
    {
        return ['coupon' => null, 'discount' => 0, 'error' => $message];
    }
}
