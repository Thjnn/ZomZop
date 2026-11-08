<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\MenuItemTopping;
use Illuminate\Database\Seeder;

class MenuItemToppingSeeder extends Seeder
{
    /** [tên topping, giá, icon] — gán cho mọi món thuộc danh mục được bán topping (Burger) */
    private const TOPPINGS = [
        ['Thêm cà chua', 5000, 'icon-ca-chua.png'],
        ['Thêm thịt bò', 20000, 'icon-thit-bo.png'],
        ['Thêm xà lách', 5000, 'icon-xa-lach.png'],
        ['Thêm Phô mai', 10000, 'icon-phomai.png'],
    ];

    public function run(): void
    {
        $items = MenuItem::whereHas('category', fn ($q) => $q->whereIn('slug', config('zomzop.topping_categories', [])))->pluck('id');

        // Chạy lại không tạo trùng; topping admin tự thêm khác tên được giữ nguyên
        foreach ($items as $itemId) {
            foreach (self::TOPPINGS as $i => [$name, $price, $icon]) {
                MenuItemTopping::updateOrCreate(
                    ['menu_item_id' => $itemId, 'name' => $name],
                    ['price' => $price, 'sort_order' => $i, 'icon' => $icon],
                );
            }
        }
    }
}
