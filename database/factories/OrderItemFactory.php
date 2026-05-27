<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'order_id' => \App\Models\Order::factory(), // Tự động tạo Order nếu chưa có
            'menu_item_id' => 1,
            'name_snapshot' => 'Pizza Đặc Biệt',
            'price_snapshot' => 150000,
            'quantity' => 1,
            'subtotal' => 150000,
        ];
    }
}
