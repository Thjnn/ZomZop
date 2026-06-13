<?php
// ============================================================
// database/seeders/CouponNotificationSeeder.php
// ============================================================
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CouponNotification;
use App\Models\User;
use App\Models\Coupon;

class CouponNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $coupons   = Coupon::all();

        if ($customers->isEmpty() || $coupons->isEmpty()) return;

        foreach ($coupons as $coupon) {
            // Gửi thông báo cho 70% khách hàng
            $selectedCustomers = $customers->random(max(1, (int)($customers->count() * 0.7)));

            foreach ($selectedCustomers as $customer) {
                $status = collect(['sent', 'sent', 'sent', 'failed', 'pending'])->random();

                CouponNotification::create([
                    'user_id'   => $customer->id,
                    'coupon_id' => $coupon->id,
                    'channel'   => 'zalo',
                    'status'    => $status,
                    'sent_at'   => $status === 'sent' ? now()->subDays(rand(1, 10)) : null,
                ]);
            }
        }
    }
}
