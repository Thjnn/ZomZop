<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Khách đăng nhập / đăng ký bằng Google hoặc Facebook ({provider} = google | facebook).
 * Chỉ dành cho khách hàng: email trùng tài khoản nội bộ thì từ chối, tránh ai chiếm Gmail của manager là vào được trang quản lý.
 */
class SocialAuthController extends AuthController
{
    public function redirect(string $provider)
    {
        if (!config("services.{$provider}.client_id")) {
            return redirect()->route('login')->withErrors(['email' => 'Đăng nhập bằng '.ucfirst($provider).' chưa được cấu hình.']);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(config("services.{$provider}.client_id"), 404);
        $name = ucfirst($provider);

        try {
            $social = Socialite::driver($provider)->user();
        } catch (Throwable) {
            // Khách bấm Huỷ, hết hạn phiên, hoặc sai cấu hình
            return redirect()->route('login')->withErrors(['email' => "Không đăng nhập được bằng {$name}, vui lòng thử lại."]);
        }

        $email = $social->getEmail();
        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => "Tài khoản {$name} không cung cấp email, vui lòng đăng ký bằng email."]);
        }

        $column = "{$provider}_id";
        $user   = User::where($column, $social->getId())->first()
            ?? User::where('email', $email)->first();

        if ($user && $user->role !== 'customer') {
            return redirect()->route('login')->withErrors(['email' => 'Tài khoản nhân viên/quản lý vui lòng đăng nhập bằng mật khẩu.']);
        }
        if ($user && !$user->is_active) {
            return redirect()->route('login')->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
        }

        if ($user) {
            // Lần đầu dùng mạng xã hội với tài khoản email có sẵn → nối vào
            $user->forceFill([$column => $social->getId()])->save();
        } else {
            $user = User::create([
                'name'      => $social->getName() ?: Str::before($email, '@'),
                'email'     => $email,
                'password'  => Str::random(40), // khách không biết mật khẩu này; chỉ đăng nhập bằng mạng xã hội
                'role'      => 'customer',
                'is_active' => true,
            ]);
            $user->forceFill([$column => $social->getId(), 'email_verified_at' => now()])->save();
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return $this->redirectByRole($user);
    }
}
