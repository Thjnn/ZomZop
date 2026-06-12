<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;
use App\Models\Branch;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $branchIds = Branch::pluck('id')->toArray();

        $shifts = [
            [
                'name'       => 'Ca Sáng',
                'start_time' => '07:00:00',
                'end_time'   => '11:30:00',
            ],
            [
                'name'       => 'Ca Trưa',
                'start_time' => '11:30:00',
                'end_time'   => '17:00:00',
            ],
            [
                'name'       => 'Ca Tối',
                'start_time' => '17:00:00',
                'end_time'   => '22:00:00',
            ],
        ];

        foreach ($branchIds as $branchId) {
            foreach ($shifts as $shift) {
                Shift::create([
                    'branch_id'  => $branchId,
                    'name'       => $shift['name'],
                    'start_time' => $shift['start_time'],
                    'end_time'   => $shift['end_time'],
                ]);
            }
        }
    }
}
