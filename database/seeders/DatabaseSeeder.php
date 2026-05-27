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
            BranchSeeder::class,    // 1. Phải chạy cái này trước để tạo ID chi nhánh
            UserSeeder::class,      // 2. Sau đó tạo User (liên kết với branch_id)
            CategorySeeder::class,  // 3. Các bảng khác
            MenuItemSeeder::class,
            FavoriteSeeder::class,
            OrderSeeder::class,     // 4. Cuối cùng mới tạo Đơn hàng
        ]);
    }
}
