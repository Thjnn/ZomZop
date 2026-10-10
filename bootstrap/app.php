<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Khu vực quản lý chi nhánh, tách file để không đụng routes/web.php
            Route::middleware('web')->group(base_path('routes/manager.php'));
            // Khu vực quản trị toàn chuỗi
            Route::middleware('web')->group(base_path('routes/admin.php'));
            // Máy chấm công khuôn mặt ở quầy (API riêng, không qua nhóm 'web')
            Route::group([], base_path('routes/kiosk.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tunnel (cloudflared) chạy ngay trên máy này và chuyển tiếp HTTPS -> HTTP,
        // nên chỉ tin header X-Forwarded-* đến từ loopback.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        $middleware->alias([
            'role'  => \App\Http\Middleware\EnsureRole::class,
            'kiosk' => \App\Http\Middleware\AuthenticateKiosk::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
