<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Shift;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả nhân viên (không phải customer)
        $staffUsers = User::whereIn('role', ['staff', 'kitchen', 'manager'])
            ->get();

        // Tạo dữ liệu chấm công cho 30 ngày gần nhất
        foreach ($staffUsers as $user) {
            // Lấy ca làm việc theo chi nhánh của user
            $shifts = Shift::where('branch_id', $user->branch_id)->get();

            if ($shifts->isEmpty()) continue;

            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);

                // Bỏ qua Chủ Nhật
                if ($date->isSunday()) continue;

                // Mỗi nhân viên làm 1 ca mỗi ngày
                $shift = $shifts->random();

                $checkIn  = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time)
                    ->addMinutes(rand(-5, 15)); // đến sớm/trễ 1 chút

                $checkOut = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->end_time)
                    ->addMinutes(rand(-10, 20));

                // 80% chấm công bằng khuôn mặt, 20% thủ công
                $method = rand(1, 10) <= 8 ? 'face' : 'manual';

                Attendance::create([
                    'user_id'         => $user->id,
                    'branch_id'       => $user->branch_id,
                    'shift_id'        => $shift->id,
                    'check_in'        => $checkIn,
                    'check_out'       => $checkOut,
                    'method'          => $method,
                    'face_confidence' => $method === 'face' ? rand(85, 99) / 100 : null,
                    'note'            => $method === 'manual' ? 'Nhận diện thất bại, xác nhận thủ công' : null,
                ]);
            }
        }
    }
}
