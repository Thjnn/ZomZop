<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryConfig;
use App\Models\User;

class SalaryConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Lương theo giờ: [thử việc, chính thức]
        $salaryByRole = [
            'staff'   => [20000, 25000],
            'kitchen' => [18000, 22000],
        ];
        $startedAt = now()->subMonth()->startOfMonth();

        // Chạy lại được: chỉ thêm lương cho người chưa có, không đụng mức lương manager đã đặt
        $users = User::whereIn('role', array_keys($salaryByRole))->whereDoesntHave('salaryConfigs')->get();

        foreach ($users as $user) {
            [$probation, $rate] = $salaryByRole[$user->role];
            $user->started_at ?? $user->update(['started_at' => $startedAt]);

            SalaryConfig::create([
                'user_id'        => $user->id,
                'type'           => 'hourly',
                'rate'           => $rate,
                'probation_rate' => $probation,
                'effective_from' => $startedAt,
            ]);
        }
    }
}
