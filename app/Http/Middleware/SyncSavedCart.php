<?php

namespace App\Http\Middleware;

use App\Support\SavedCart;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sau mỗi request của người đã đăng nhập, chép giỏ trong session vào tài khoản.
 * Một chỗ duy nhất nên mọi thao tác (thêm/sửa/xóa, đặt lại đơn, đặt hàng xong xóa giỏ) đều được lưu.
 */
class SyncSavedCart
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Request đăng xuất: lúc này đã không còn user → giữ nguyên giỏ đã lưu
        if ($user = $request->user()) {
            SavedCart::store($user);
        }

        return $response;
    }
}
