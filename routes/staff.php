<?php

use App\Http\Controllers\Staff\OrderController;
use App\Http\Controllers\Staff\WorkController;
use Illuminate\Support\Facades\Route;

// Nhân viên & bếp: chỉ xem dữ liệu của chính mình, không đụng tới đơn hàng
Route::middleware(['auth', 'role:staff,kitchen'])
    ->group(function () {
        Route::get('/staff', [WorkController::class, 'me'])->name('staff.dashboard');
        Route::get('/staff/schedule/export', [WorkController::class, 'exportSchedule'])->name('staff.schedule.export');
    });

// Máy POS: trang mặc định của nhân viên sau khi đăng nhập; quản lý cũng bán được tại chi nhánh mình
Route::middleware(['auth', 'role:staff,manager'])
    ->group(function () {
        Route::get('/staff/pos', [WorkController::class, 'pos'])->name('staff.pos');
        Route::get('/staff/pos/orders', [OrderController::class, 'index'])->name('staff.pos.orders.index');
        // Chốt đơn tại quầy: nhận giỏ JSON, giá do server tự tính
        Route::post('/pos/orders', [OrderController::class, 'store'])->name('staff.pos.orders.store');
    });

Route::middleware(['auth', 'role:staff'])
    ->get('/staff/me/orders', [OrderController::class, 'mine'])->name('staff.me.orders');

// Hóa đơn POS: nhân viên/quản lý cùng chi nhánh hoặc admin (kiểm tra chi nhánh trong controller)
Route::middleware(['auth', 'role:staff,manager,admin'])
    ->get('/pos/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('staff.pos.orders.receipt');

// Màn hình bếp: chỉ xem đơn chờ nấu / đang nấu, quản lý vẫn là người đổi trạng thái
Route::middleware(['auth', 'role:kitchen'])
    ->get('/kitchen', [WorkController::class, 'kitchen'])->name('kitchen.dashboard');
