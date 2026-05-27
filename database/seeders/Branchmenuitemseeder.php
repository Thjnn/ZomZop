<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BranchMenuItem;
use App\Models\MenuItem;
use App\Models\Branch;

class BranchMenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $branchIds   = Branch::pluck('id')->toArray();       // [1, 2, 3]
        $menuItemIds = MenuItem::pluck('id')->toArray();     // tất cả món

        // Override giá riêng theo chi nhánh (null = dùng base_price)
        // branch_id => [ menu_item_slug => price ]
        $priceOverrides = [
            // ZomZop - Mỹ Tho 1 (branch 1): không override, dùng giá gốc
            1 => [],

            // ZomZop - Bến Tre (branch 2): một số món rẻ hơn vì chi phí thấp hơn
            2 => [
                'classic-burger'   => 42000,
                'double-smash'     => 62000,
                'pizza-pho-mai'    => 85000,
                'pizza-bbq-ga'     => 95000,
                'pizza-hai-san'    => 105000,
                'pizza-cay-kieu-y' => 90000,
            ],

            // ZomZop - Mỹ Tho 2 (branch 3): không override, dùng giá gốc
            3 => [],
        ];

        // Món tạm hết hàng tại chi nhánh cụ thể (để demo is_available = 0)
        // branch_id => [slug, slug, ...]
        $unavailable = [
            1 => ['pizza-hai-san'],         // Mỹ Tho 1: hết hải sản
            2 => ['combo-gia-dinh'],        // Bến Tre: chưa phục vụ combo gia đình
            3 => [],
        ];

        // Lấy map slug => id để tra nhanh
        $slugToId = MenuItem::pluck('id', 'slug')->toArray();

        foreach ($branchIds as $branchId) {
            foreach ($menuItemIds as $menuItemId) {
                // Tìm slug của item này để check override
                $slug = array_search($menuItemId, $slugToId);

                $price       = $priceOverrides[$branchId][$slug] ?? null;
                $isAvailable = in_array($slug, $unavailable[$branchId] ?? []) ? 0 : 1;

                BranchMenuItem::create([
                    'branch_id'    => $branchId,
                    'menu_item_id' => $menuItemId,
                    'price'        => $price,
                    'is_available' => $isAvailable,
                    'stock_qty'    => $isAvailable ? rand(20, 100) : 0,
                ]);
            }
        }
    }
}
