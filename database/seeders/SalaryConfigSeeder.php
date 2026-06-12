<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryConfig;
use App\Models\User;

class SalaryConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Cấu hình lương theo role
        $salaryByRole = [
            'manager' => ['type' => 'fixed',  'rate' => 12000000], // 12 triệu/tháng
            'staff'   => ['type' => 'hourly', 'rate' => 25000],    // 25k/giờ
            'kitchen' => ['type' => 'hourly', 'rate' => 22000],    // 22k/giờ
        ];

        $staffUsers = User::whereIn('role', ['manager', 'staff', 'kitchen'])->get();

        foreach ($staffUsers as $user) {
            $config = $salaryByRole[$user->role] ?? null;
            if (!$config) continue;

            SalaryConfig::create([
                'user_id'        => $user->id,
                'type'           => $config['type'],
                'rate'           => $config['rate'],
                'effective_from' => now()->startOfMonth(),
            ]);
        }
    }
}
