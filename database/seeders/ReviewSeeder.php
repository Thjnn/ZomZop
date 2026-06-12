<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Order;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Chỉ lấy đơn đã completed mới được review
        $completedOrders = Order::where('status', 'completed')->get();

        $comments = [
            'Đồ ăn ngon, giao hàng nhanh, rất hài lòng!',
            'Burger giòn, thịt tươi, sẽ quay lại lần sau.',
            'Pizza phô mai béo ngậy, ăn là ghiền luôn!',
            'Gà chiên giòn rụm, không bị ngấy, tuyệt vời!',
            'Combo gia đình rất đáng tiền, đủ ăn cho cả nhà.',
            'Đóng gói cẩn thận, đồ ăn còn nóng khi nhận.',
            'Nhân viên thân thiện, phục vụ nhanh.',
            'Vị hơi mặn một chút nhưng nhìn chung ổn.',
            'Giao hơi trễ nhưng đồ ăn vẫn ngon.',
            'Sẽ giới thiệu cho bạn bè, quá ngon!',
        ];

        foreach ($completedOrders as $order) {
            // 80% đơn completed sẽ có review
            if (rand(1, 10) > 8) continue;

            $rating = rand(3, 5); // rating từ 3-5 sao

            Review::create([
                'order_id'        => $order->id,
                'user_id'         => $order->user_id,
                'branch_id'       => $order->branch_id,
                'rating'          => $rating,
                'delivery_rating' => $order->type === 'delivery' ? rand(3, 5) : null,
                'comment'         => $comments[array_rand($comments)],
            ]);
        }
    }
}
