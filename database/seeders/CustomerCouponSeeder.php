<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CustomerCouponSeeder extends Seeder
{
    /**
     * Chạy lại được nhiều lần (khớp theo code, không đụng tới used_count):
     *   php artisan db:seed --class=CustomerCouponSeeder
     */
    public function run(): void
    {
        $coupons = [
            // ── Miễn phí ship (giao tiêu chuẩn) ──
            [
                'code' => 'SHIPFREE', 'type' => 'percent', 'value' => 100, 'scope' => 'ship',
                'description' => 'Miễn phí vận chuyển cho đơn giao tiêu chuẩn',
                'min_order_value' => 99000, 'max_uses' => 500, 'expired_at' => now()->addMonths(3),
            ],
            // ── Ship hỏa tốc ──
            [
                'code' => 'HOATOC15', 'type' => 'fixed', 'value' => 15000, 'scope' => 'express',
                'description' => 'Giảm 15.000đ phí giao hỏa tốc',
                'min_order_value' => 120000, 'max_uses' => 300, 'expired_at' => now()->addMonths(3),
            ],
            // ── Giảm giá món ăn ──
            [
                'code' => 'ZOMZOP15', 'type' => 'percent', 'value' => 15, 'scope' => 'food', 'max_discount' => 40000,
                'description' => 'Giảm 15% tiền món, tối đa 40.000đ',
                'min_order_value' => 100000, 'max_uses' => 300, 'expired_at' => now()->addMonths(3),
            ],
            [
                'code' => 'MON30K', 'type' => 'fixed', 'value' => 30000, 'scope' => 'food',
                'description' => 'Giảm 30.000đ cho đơn từ 150.000đ',
                'min_order_value' => 150000, 'max_uses' => 300, 'expired_at' => now()->addMonths(2),
            ],
            [
                'code' => 'MON10', 'type' => 'percent', 'value' => 10, 'scope' => 'food', 'max_discount' => 30000,
                'description' => 'Giảm 10% tiền món, tối đa 30.000đ',
                'min_order_value' => 50000, 'max_uses' => 500, 'expired_at' => now()->addMonths(3),
            ],
        ];

        foreach ($coupons as $data) {
            $coupon = Coupon::firstOrNew(['code' => $data['code']]);
            if ($coupon->exists) { continue; }
            $coupon->fill($data + [
                'max_discount'      => null,
                'max_uses_per_user' => 1,
                'is_active'         => true,
                'is_public'         => true,
                'started_at'        => now()->startOfDay(),
            ]);
            $coupon->save();
        }

    }
}
