<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,         // 1. branches (không phụ thuộc gì)
            ShiftSeeder::class,          // 2. shifts (cần branch_id)
            UserSeeder::class,           // 3. users (cần branch_id)
            SalaryConfigSeeder::class,   // 4. cần user_id
            CategorySeeder::class,       // 5. categories (không phụ thuộc gì)
            MenuItemSeeder::class,       // 6. menu_items (cần category_id)
            BranchMenuItemSeeder::class, // 7. cần branch_id + menu_item_id
            MenuItemImageSeeder::class,  // 8. cần menu_item_id
            CouponSeeder::class,         // 9. coupons (không phụ thuộc gì)
            BannerSeeder::class,         // 10. banners (không phụ thuộc gì)
            SettingSeeder::class,        // 11. độc lập
            AttendanceSeeder::class,     // 12. cần user_id + branch_id + shift_id
            PayrollSeeder::class,        // 13. cần user + attendance + salary_config
            FavoriteSeeder::class,       // 14. cần user_id + menu_item_id
            OrderSeeder::class,          // 15. cần tất cả
            OrderHistorySeeder::class, // 16 cần order_id + user_id
            ReviewSeeder::class,       // 17 cần order completed + user_id + branch_id
        ]);
    }
}
