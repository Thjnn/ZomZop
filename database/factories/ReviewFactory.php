<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Order;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition()
    {
        return [
            // Liên kết tới các bảng khác (tự tạo dữ liệu nếu chưa có)
            'order_id'        => Order::factory(),
            'user_id'         => User::factory(),
            'branch_id'       => Branch::factory(),

            // Dữ liệu ngẫu nhiên
            'rating'          => $this->faker->numberBetween(1, 5),
            'delivery_rating' => $this->faker->numberBetween(1, 5),
            'comment'         => $this->faker->paragraph(),
        ];
    }
}
