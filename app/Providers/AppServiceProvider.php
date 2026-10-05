<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Máy chấm công khuôn mặt: chặn dò thử descriptor bằng cách gọi API liên tục
        RateLimiter::for('kiosk', fn (Request $request) => [
            Limit::perMinute(20)->by('kiosk-device:' . $request->header('X-Kiosk-Token')),
            Limit::perMinute(30)->by('kiosk-ip:' . $request->ip()),
        ]);
    }
}
