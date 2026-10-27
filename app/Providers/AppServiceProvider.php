<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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

        // Chạy qua tunnel (APP_URL https): cloudflared chuyển tiếp với Host = localhost:8000,
        // nên phải ép mọi link/redirect theo APP_URL, không thì đăng nhập FB/GG xong bị đá về localhost
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }

        // Chatbot: mỗi khách tối đa 10 tin/phút (tiết kiệm quota Gemini)
        RateLimiter::for('chatbot', fn (Request $request) => Limit::perMinute(10)->by('chatbot:' . ($request->user()?->id ?? $request->ip())));
    }
}
