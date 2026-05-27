<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tìm user có ID là 1 (khách hàng)
        $user = User::find(1);

        // 2. Tìm món ăn có ID là 6
        $menuItem = MenuItem::find(6);

        // 3. Nếu cả 2 đều tồn tại, thì tạo quan hệ yêu thích
        if ($user && $menuItem) {
            // Kiểm tra xem đã tồn tại chưa để tránh lỗi trùng lặp (unique)
            $exists = DB::table('favorites')
                ->where('user_id', 1)
                ->where('menu_item_id', 6)
                ->exists();

            if (!$exists) {
                DB::table('favorites')->insert([
                    'user_id' => 1,
                    'menu_item_id' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            $this->command->warn('Không tìm thấy User ID 1 hoặc MenuItem ID 6, bỏ qua FavoriteSeeder.');
        }
    }
}
