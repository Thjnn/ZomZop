<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── HIỂN THỊ TRANG ───────────────────────────────────────

    /** Hiển thị trang đăng nhập */
    public function showLogin()
    {
        // Nếu đã đăng nhập rồi thì redirect về trang chủ
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /** Hiển thị trang đăng ký */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.register');
    }

    // ── XỬ LÝ LOGIC ─────────────────────────────────────────

    /** Xử lý đăng nhập */
    public function login(Request $request)
    {
        // Validate dữ liệu từ form
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required'    => 'Vui lòng nhập email.',
            'email.email'       => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min'      => 'Mật khẩu phải ít nhất 6 ký tự.',
        ]);

        // Thử đăng nhập
        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember'); // checkbox "Ghi nhớ đăng nhập"

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Kiểm tra tài khoản có bị khóa không
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
                ]);
            }

            // Redirect theo role
            return $this->redirectByRole($user);
        }

        // Sai email hoặc mật khẩu
        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không đúng.',
        ])->withInput($request->only('email'));
    }

    /** Xử lý đăng ký */
    public function register(Request $request)
    {
        // Validate
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:15',
            'password' => 'required|min:6|confirmed', // confirmed = phải có password_confirmation
        ], [
            'name.required'      => 'Vui lòng nhập họ tên.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.email'        => 'Email không đúng định dạng.',
            'email.unique'       => 'Email này đã được sử dụng.',
            'phone.required'     => 'Vui lòng nhập số điện thoại.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        // Tạo user mới với role customer
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => 'customer',
            'is_active' => true,
        ]);

        // Đăng nhập luôn sau khi đăng ký
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Đăng ký thành công! Chào mừng bạn đến với ZomZop 🎉');
    }

    /** Xử lý đăng xuất */
    public function logout(Request $request)
    {
        Auth::logout();

        // Xóa session để tránh lỗi bảo mật
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất thành công!');
    }

    // ── HELPER ───────────────────────────────────────────────

    /** Redirect đúng trang theo role sau khi login */
    private function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            'staff'   => redirect()->route('staff.dashboard'),
            'kitchen' => redirect()->route('kitchen.dashboard'),
            default   => redirect()->route('home'), // customer
        };
    }
}
