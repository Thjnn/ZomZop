<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title'      => 'Ưu đãi mùa hè — Giảm 20% tất cả Pizza',
                'image'      => 'banner-1.jpeg',
                'link'       => '/category/pizza',
                'sort_order' => 1,
                'is_active'  => 1,
                'started_at' => now(),
                'ended_at'   => now()->addMonths(1),
            ],
            [
                'title'      => 'Burger mới ra mắt — Double Smash chỉ 65k',
                'image'      => 'banner-2.jpeg',
                'link'       => '/category/burger',
                'sort_order' => 2,
                'is_active'  => 1,
                'started_at' => now(),
                'ended_at'   => now()->addMonths(2),
            ],
            [
                'title'      => 'Combo Gia Đình — Tiết kiệm hơn gọi lẻ',
                'image'      => 'banner-3.jpg',
                'link'       => '/category/combo',
                'sort_order' => 3,
                'is_active'  => 1,
                'started_at' => now(),
                'ended_at'   => now()->addMonths(2),
            ],
            [
                'title'      => 'Nhập WELCOME10 — Giảm 10% đơn đầu tiên',
                'image'      => 'banner-1.jpeg',
                'link'       => '/menu',
                'sort_order' => 4,
                'is_active'  => 1,
                'started_at' => now(),
                'ended_at'   => now()->addMonths(3),
            ],
            [
                'title'      => 'Gà Chiên Giòn — Giòn tan từng miếng',
                'image'      => 'banner-2.jpeg',
                'link'       => '/category/ga-chien',
                'sort_order' => 5,
                'is_active'  => 1,
                'started_at' => now(),
                'ended_at'   => now()->addMonths(1),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
