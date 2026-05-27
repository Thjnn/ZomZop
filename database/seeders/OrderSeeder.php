<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // 1. Lấy ra một user và một branch bất kỳ đang có trong database
        $user = \App\Models\User::inRandomOrder()->first();
        $branch = \App\Models\Branch::inRandomOrder()->first();
        $coupon = \App\Models\Coupon::first(); // Lấy đại 1 coupon

        // Kiểm tra nếu chưa có dữ liệu thì không tạo Order (tránh lỗi)
        if (!$user || !$branch) {
            $this->command->error('Cần có User và Branch trước khi tạo Order!');
            return;
        }

        // 2. Tạo Order với ID động
        $order = Order::create([
            'order_code' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => $user->id,      // Sử dụng ID tìm được
            'branch_id' => $branch->id,  // Sử dụng ID tìm được
            'type' => 'delivery',
            'status' => 'pending',
            'subtotal' => 200000,
            'total' => 180000,
            'coupon_id' => $coupon ? $coupon->id : null, // Kiểm tra null
            'payment_status' => 'unpaid',
        ]);

        // 3. Tạo các OrderItem cho đơn hàng đó
        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => 1, // ID món ăn từ bảng menu_items
            'name_snapshot' => 'Pizza Hải Sản',
            'price_snapshot' => 200000,
            'quantity' => 1,
            'subtotal' => 200000,
        ]);
    }
}
