<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code' => strtoupper(\Illuminate\Support\Str::random(8)),
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
            'expired_at' => now()->addDays(30),
        ];
    }
}
