<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** Dùng: ->middleware('role:manager') hoặc 'role:admin,manager' */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // is_active: tài khoản bị khoá giữa chừng vẫn còn session / remember-me cũ
        abort_unless($user && $user->is_active && in_array($user->role, $roles, true), 403, 'Bạn không có quyền truy cập trang này.');

        return $next($request);
    }
}
