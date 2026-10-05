<?php

namespace App\Http\Middleware;

use App\Models\KioskDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Máy quầy xác thực bằng header X-Kiosk-Token (không dùng session/đăng nhập) */
class AuthenticateKiosk
{
    public function handle(Request $request, Closure $next): Response
    {
        $device = KioskDevice::findByToken($request->header('X-Kiosk-Token'));

        if (!$device) {
            return response()->json(['message' => 'Thiết bị chưa được ghép hoặc đã bị thu hồi.'], 401);
        }

        $device->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('kiosk', $device);

        return $next($request);
    }
}
