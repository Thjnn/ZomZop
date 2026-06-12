<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code'              => 'WELCOME10',
                'type'              => 'percent',
                'value'             => 10,
                'min_order_value'   => 50000,
                'max_uses'          => 100,
                'used_count'        => 0,
                'max_uses_per_user' => 1,
                'is_active'         => 1,
                'started_at'        => now(),
                'expired_at'        => now()->addMonths(3),
            ],
            [
                'code'              => 'ZOMZOP20',
                'type'              => 'percent',
                'value'             => 20,
                'min_order_value'   => 100000,
                'max_uses'          => 50,
                'used_count'        => 0,
                'max_uses_per_user' => 1,
                'is_active'         => 1,
                'started_at'        => now(),
                'expired_at'        => now()->addMonths(1),
            ],
            [
                'code'              => 'GIAM30K',
                'type'              => 'fixed',
                'value'             => 30000,
                'min_order_value'   => 150000,
                'max_uses'          => 200,
                'used_count'        => 0,
                'max_uses_per_user' => 1,
                'is_active'         => 1,
                'started_at'        => now(),
                'expired_at'        => now()->addMonths(2),
            ],
            [
                'code'              => 'FREESHIP',
                'type'              => 'fixed',
                'value'             => 15000,
                'min_order_value'   => 80000,
                'max_uses'          => 300,
                'used_count'        => 0,
                'max_uses_per_user' => 2,
                'is_active'         => 1,
                'started_at'        => now(),
                'expired_at'        => now()->addWeeks(2),
            ],
            [
                'code'              => 'SUMMER50',
                'type'              => 'percent',
                'value'             => 50,
                'min_order_value'   => 200000,
                'max_uses'          => 30,
                'used_count'        => 0,
                'max_uses_per_user' => 1,
                'is_active'         => 1,
                'started_at'        => now(),
                'expired_at'        => now()->addWeeks(1),
            ],
            [
                'code'              => 'EXPIRED',
                'type'              => 'percent',
                'value'             => 15,
                'min_order_value'   => 50000,
                'max_uses'          => 100,
                'used_count'        => 100,
                'max_uses_per_user' => 1,
                'is_active'         => 0,
                'started_at'        => now()->subMonths(2),
                'expired_at'        => now()->subMonth(),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
