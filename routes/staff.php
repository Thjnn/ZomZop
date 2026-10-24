<?php

use App\Http\Controllers\Staff\WorkController;
use Illuminate\Support\Facades\Route;

// Nhân viên & bếp: chỉ xem dữ liệu của chính mình, không đụng tới đơn hàng
Route::middleware(['auth', 'role:staff,kitchen'])
    ->group(function () {
        Route::get('/staff', [WorkController::class, 'me'])->name('staff.dashboard');
        Route::get('/staff/schedule/export', [WorkController::class, 'exportSchedule'])->name('staff.schedule.export');
    });

// Màn hình bếp: chỉ xem đơn chờ nấu / đang nấu, quản lý vẫn là người đổi trạng thái
Route::middleware(['auth', 'role:kitchen'])
    ->get('/kitchen', [WorkController::class, 'kitchen'])->name('kitchen.dashboard');
