<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // <--- Cần dòng này để nhận diện Model Category

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Burger',    'slug' => 'burger',    'icon' => 'burger.jpg'],
            ['name' => 'Pizza',     'slug' => 'pizza',     'icon' => 'pizza.jpg'],
            ['name' => 'Mỳ Ý',      'slug' => 'my-y',      'icon' => 'my-y.png'],
            ['name' => 'Sandwich',  'slug' => 'sandwich',  'icon' => 'sandwich.jpg'],
            ['name' => 'Gà Chiên',  'slug' => 'ga-chien',  'icon' => 'ga-chien.jpg'],
            ['name' => 'Sides',     'slug' => 'sides',     'icon' => 'sides.jpg'],
            ['name' => 'Đồ uống',   'slug' => 'do-uong',   'icon' => 'do-uong.jpg'],
            ['name' => 'Combo',     'slug' => 'combo',     'icon' => 'combo.jpg'],
        ];

        foreach ($categories as $index => $cat) {
            Category::create([
                'name'      => $cat['name'],
                'slug'      => $cat['slug'],
                'icon'      => $cat['icon'],
                'sort_order' => $index + 1,
                'is_active' => 1
            ]);
        }
    }
}
