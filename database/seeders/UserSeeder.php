<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Kiểm tra nếu đã có user thì không chèn thêm (tránh trùng lặp khi chạy lại)
        if (User::count() > 0) return;

        $password = Hash::make('12345678');
        $make = fn (array $attrs) => User::create($attrs + ['password' => $password, 'is_active' => true]);

        $make(['name' => 'Admin ZomZop', 'email' => 'admin@zomzop.com', 'role' => 'admin']);
        $make(['name' => 'Khách hàng mẫu', 'email' => 'customer@zomzop.com', 'role' => 'customer', 'phone' => '0901234567']);

        // Mỗi chi nhánh: 2 quản lý, 2 nhân viên, 1 bếp.
        // Chi nhánh đầu tiên giữ các email cũ (manager@, staff@, kitchen@) để không đổi thói quen đăng nhập.
        foreach (Branch::orderBy('id')->get() as $i => $branch) {
            $slug  = Str::slug(Str::after($branch->name, 'ZomZop - '), '');   // "Mỹ Tho 1" → "mytho1"
            $place = Str::after($branch->name, 'ZomZop - ');
            $email = fn (string $legacy, string $prefix) => $i === 0 && $legacy ? $legacy : "{$prefix}.{$slug}@zomzop.com";

            $accounts = [
                ['manager', 'manager@zomzop.com', 'ql1', "Quản lý 1 - {$place}"],
                ['manager', null,                 'ql2', "Quản lý 2 - {$place}"],
                ['staff',   'staff@zomzop.com',   'nv1', "Nhân viên 1 - {$place}"],
                ['staff',   null,                 'nv2', "Nhân viên 2 - {$place}"],
                ['kitchen', 'kitchen@zomzop.com', 'bep', "Bếp - {$place}"],
            ];

            foreach ($accounts as [$role, $legacy, $prefix, $name]) {
                $make(['name' => $name, 'email' => $email((string) $legacy, $prefix), 'role' => $role, 'branch_id' => $branch->id]);
            }
        }
    }
}
