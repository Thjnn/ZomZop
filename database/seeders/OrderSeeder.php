<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Branch;
use App\Models\MenuItem;
use App\Models\Coupon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $branches  = Branch::all();
        $menuItems = MenuItem::where('is_available', true)->get();
        $coupons   = Coupon::all();

        if ($customers->isEmpty() || $branches->isEmpty() || $menuItems->isEmpty()) {
            $this->command->error('Cần có Customer, Branch, MenuItem trước!');
            return;
        }

        // Tạo 30 đơn hàng mẫu
        for ($i = 1; $i <= 30; $i++) {
            $customer = $customers->random();
            $branch   = $branches->random();
            $coupon   = rand(0, 1) ? $coupons->random() : null;
            $type     = rand(0, 1) ? 'takeaway' : 'delivery';

            // Phân bổ status đa dạng
            $status = match (true) {
                $i <= 12 => 'completed',   // 12 đơn completed → có review
                $i <= 17 => 'cancelled',   // 5 đơn cancelled
                $i <= 20 => 'ready',       // 3 đơn ready
                $i <= 23 => 'cooking',     // 3 đơn cooking
                $i <= 26 => 'confirmed',   // 3 đơn confirmed
                default  => 'pending',     // 4 đơn pending
            };

            // Tạo 1-3 món ngẫu nhiên
            $selectedItems = $menuItems->random(rand(1, 3));
            $subtotal = 0;

            foreach ($selectedItems as $item) {
                $subtotal += $item->base_price * 1;
            }

            $discount = $coupon ? min($coupon->value, $subtotal) : 0;
            $total    = $subtotal - $discount;

            $order = Order::create([
                'order_code'      => 'ORD-' . strtoupper(uniqid()),
                'user_id'         => $customer->id,
                'branch_id'       => $branch->id,
                'type'            => $type,
                'status'          => $status,
                'subtotal'        => $subtotal,
                'discount'        => $discount,
                'total'           => $total,
                'payment_method'  => collect(['cash', 'momo', 'vnpay'])->random(),
                'payment_status'  => $status === 'completed' ? 'paid' : 'unpaid',
                'delivery_address' => $type === 'delivery' ? '123 Đường Nguyễn Huệ, Q.1, TP.HCM' : null,
                'pickup_code'     => $type === 'takeaway' ? strtoupper(substr(uniqid(), -6)) : null,
                'coupon_id'       => $coupon?->id,
                'note'            => null,
            ]);

            // Tạo order items
            foreach ($selectedItems as $item) {
                $qty = rand(1, 2);
                OrderItem::create([
                    'order_id'       => $order->id,
                    'menu_item_id'   => $item->id,
                    'name_snapshot'  => $item->name,
                    'price_snapshot' => $item->base_price,
                    'quantity'       => $qty,
                    'subtotal'       => $item->base_price * $qty,
                    'note'           => null,
                ]);
            }
        }
    }
}
