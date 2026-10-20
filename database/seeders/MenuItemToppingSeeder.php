<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\MenuItemTopping;
use Illuminate\Database\Seeder;

class MenuItemToppingSeeder extends Seeder
{
    public function run(): void
    {
        // Món => [tên topping, giá, icon]; chạy lại không tạo trùng
        $toppings = [
            'Classic Burger' => [
                ['Thêm cà chua', 5000, 'icon-ca-chua.png'],
                ['Thêm thịt bò', 20000, 'icon-thit-bo.png'],
                ['Thêm xà lách', 5000, 'icon-xa-lach.png'],
                ['Thêm Phô mai', 10000, 'icon-phomai.png'],
            ],
            'BBQ Bacon' => [
                ['Thêm cà chua', 5000, 'icon-ca-chua.png'],
                ['Thêm thịt bò', 20000, 'icon-thit-bo.png'],
            ],
        ];

        foreach ($toppings as $itemName => $rows) {
            $itemId = MenuItem::where('name', $itemName)->value('id');
            if (! $itemId) {
                continue;
            }

            foreach ($rows as $i => [$name, $price, $icon]) {
                MenuItemTopping::updateOrCreate(
                    ['menu_item_id' => $itemId, 'name' => $name],
                    ['price' => $price, 'sort_order' => $i, 'icon' => $icon],
                );
            }
        }
    }
}
