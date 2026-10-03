<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        $addressCount = Address::where('user_id', $user->id)->count();

        return view('profile', compact('user', 'addressCount'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->merge(['phone' => preg_replace('/\s+/', '', (string) $request->phone)]);

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'birthday' => 'nullable|date|before:today',
            'gender'   => 'nullable|in:male,female,other',
            'avatar'   => 'nullable|image|max:2048',
        ], [
            'name.required'   => 'Vui lòng nhập họ tên.',
            'email.required'  => 'Vui lòng nhập email.',
            'email.email'     => 'Email không hợp lệ.',
            'email.unique'    => 'Email này đã có người sử dụng.',
            'phone.regex'     => 'Số điện thoại không hợp lệ (VD: 0901234567).',
            'birthday.before' => 'Ngày sinh phải trước hôm nay.',
            'avatar.image'    => 'Tệp tải lên phải là hình ảnh.',
            'avatar.max'      => 'Ảnh đại diện tối đa 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $name = 'u' . $user->id . '_' . time() . '.' . $file->extension();
            $file->move(public_path('uploads/avatars'), $name);

            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }
            $data['avatar'] = 'uploads/avatars/' . $name;
        } else {
            unset($data['avatar']);
        }

        $user->forceFill($data)->save();

        return back()->with('success', 'Đã lưu thông tin cá nhân.');
    }

    public function updatePassword(Request $request)
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'min:6', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required'         => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.required'                 => 'Vui lòng nhập mật khẩu mới.',
            'password.min'                      => 'Mật khẩu mới tối thiểu 6 ký tự.',
            'password.confirmed'                => 'Nhập lại mật khẩu mới không khớp.',
            'password.different'                => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        $request->user()->forceFill(['password' => Hash::make($request->password)])->save();

        return back()->with('success_password', 'Đã đổi mật khẩu thành công.');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user->avatar && file_exists(public_path($user->avatar))) {
            @unlink(public_path($user->avatar));
        }
        Address::where('user_id', $user->id)->delete();
        $user->delete();

        return redirect('/');
    }
}