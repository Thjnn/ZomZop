<?php

use App\Http\Controllers\KioskController;
use Illuminate\Support\Facades\Route;

// API cho máy quầy: không dùng nhóm 'web' (không session/CSRF — máy quầy mở cả ngày, token CSRF sẽ hết hạn).
// Xác thực bằng header X-Kiosk-Token, giới hạn tần suất theo thiết bị và IP.
Route::prefix('kiosk/api')
    ->middleware(['throttle:kiosk', 'kiosk'])
    ->group(function () {
        Route::get('/status', [KioskController::class, 'status']);
        Route::post('/punch', [KioskController::class, 'punch']);
    });

// Trang máy quầy (không cần đăng nhập; thiết bị được ghép bằng token trong link manager đưa)
Route::middleware('web')->get('/kiosk', fn () => view('kiosk.index'))->name('kiosk');
