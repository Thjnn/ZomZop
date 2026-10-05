<?php

use App\Models\Attendance;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ảnh bằng chứng chấm công khuôn mặt chỉ giữ FACE_PHOTO_DAYS ngày (mặc định 30).
// Thư mục: attendance-photos/{branch_id}/{Y-m-d}/ — xoá nguyên thư mục ngày đã quá hạn.
Artisan::command('attendance:prune-photos', function () {
    $cutoff = today()->subDays(config('attendance.face.photo_days'))->toDateString();
    $disk   = Storage::disk('local');
    $days   = 0;

    foreach ($disk->directories('attendance-photos') as $branchDir) {
        foreach ($disk->directories($branchDir) as $dayDir) {
            if (basename($dayDir) < $cutoff) {
                $disk->deleteDirectory($dayDir);
                $days++;
            }
        }
    }

    Attendance::whereNotNull('photo_path')->where('check_in', '<', $cutoff)->update(['photo_path' => null]);

    $this->info("Đã xoá ảnh chấm công của {$days} ngày (trước {$cutoff}).");
})->purpose('Xoá ảnh bằng chứng chấm công khuôn mặt quá hạn');

Schedule::command('attendance:prune-photos')->daily();
