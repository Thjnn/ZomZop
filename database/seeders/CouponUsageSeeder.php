<?php
// ============================================================
// database/seeders/CouponUsageSeeder.php
// ============================================================
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CouponUsage;
use App\Models\Order;

class CouponUsageSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy các đơn completed có dùng coupon
        $orders = Order::where('status', 'completed')
            ->whereNotNull('coupon_id')
            ->get();

        foreach ($orders as $order) {
            // Tránh trùng unique key (coupon_id + user_id)
            $exists = CouponUsage::where('coupon_id', $order->coupon_id)
                ->where('user_id', $order->user_id)
                ->exists();
            if ($exists) continue;

            CouponUsage::create([
                'coupon_id' => $order->coupon_id,
                'user_id'   => $order->user_id,
                'order_id'  => $order->id,
                'used_at'   => $order->created_at,
            ]);
        }
    }
}
