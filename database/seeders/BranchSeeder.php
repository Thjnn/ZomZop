<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'ZomZop - Mỹ Tho 1',
                'address' => 'Số 1, Đường Tết Mậu Thân, P. Đạo Thạnh, Đồng Tháp',
                'phone' => '0326313224',
                'open_time' => '07:00:00',
                'close_time' => '22:00:00',
                'is_active' => 1,
            ],
            [
                'name' => 'ZomZop - Bến Tre',
                'address' => 'Số 10, Đường Phan Văn Trị, Xã Giồng Trôm, Vĩnh Long',
                'phone' => '0877790085',
                'open_time' => '07:30:00',
                'close_time' => '21:30:00',
                'is_active' => 1,
            ],
            [
                'name' => 'ZomZop - Mỹ Tho 2',
                'address' => 'Số 1, Đường Ấp Bắc, P. Đạo Thạnh, Đồng Tháp',
                'phone' => '0326313224',
                'open_time' => '07:00:00',
                'close_time' => '22:00:00',
                'is_active' => 1,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
