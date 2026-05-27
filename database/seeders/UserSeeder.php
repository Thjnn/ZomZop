<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Kiểm tra nếu đã có user thì không chèn thêm (tránh trùng lặp khi chạy lại)
        if (User::count() > 0) return;

        // Lấy 1 branch mẫu để gán cho staff
        $branch = Branch::first();

        // Tạo Admin
        User::create([
            'name' => 'Admin ZomZop',
            'email' => 'admin@zomzop.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Tạo Staff
        User::create([
            'name' => 'Nhân viên A',
            'email' => 'staff@zomzop.com',
            'password' => Hash::make('12345678'),
            'role' => 'staff',
            'branch_id' => $branch ? $branch->id : null,
            'is_active' => true,
        ]);

        // Tạo Customer
        User::create([
            'name' => 'Khách hàng mẫu',
            'email' => 'customer@zomzop.com',
            'password' => Hash::make('12345678'),
            'role' => 'customer',
            'phone' => '0901234567', // Dữ liệu này giờ sẽ được lưu thành công  
            'is_active' => true,
        ]);
        User::create([
            'name' => 'Quản lý Nhà hàng',
            'email' => 'manager@zomzop.com',
            'password' => bcrypt('12345678'),
            'role' => 'manager',
            'branch_id' => Branch::first()->id, // Manager thường gắn với 1 chi nhánh
            'is_active' => true,
        ]);
        User::create([
            'name' => 'Đầu bếp chính',
            'email' => 'kitchen@zomzop.com',
            'password' => bcrypt('12345678'),
            'role' => 'kitchen',
            'branch_id' => Branch::first()->id, // Bếp cũng thuộc chi nhánh
            'is_active' => true,
        ]);
    }
}
