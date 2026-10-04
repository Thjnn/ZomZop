@extends('layouts.app')
 
@section('title', 'Hồ sơ - ZomZop')
 
@section('content')
@php
    $parts    = preg_split('/\s+/', trim($user->name));
    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    $pwErr    = $errors->getBag('password');
    $input    = 'w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-red-400 focus:outline-none';
@endphp
 
<div class="max-w-4xl mx-auto py-8 px-4">
    <a href="{{ url('/menu') }}" class="text-sm text-slate-500 hover:text-red-500">← Quay lại tài khoản</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2 mb-6">Hồ sơ của tôi</h1>
 
    {{-- Thông báo thành công --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('success_password'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success_password') }}</div>
    @endif
 
    {{-- 3 ô thống kê: bấm vào sẽ chuyển trang --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <a href="{{ route('orders') }}" class="bg-white rounded-2xl border border-slate-100 p-4 text-center hover:border-red-400 transition">
            <p class="text-2xl font-bold text-red-500">12</p>{{-- TODO: thay bằng số đơn thật khi module đơn hàng xong --}}
            <p class="text-xs text-slate-400">Đơn hàng</p>
        </a>
        <a href="{{ route('favorites.index') }}" class="bg-white rounded-2xl border border-slate-100 p-4 text-center hover:border-red-400 transition">
            <p class="text-2xl font-bold text-red-500">5</p>{{-- TODO: thay bằng số món yêu thích thật --}}
            <p class="text-xs text-slate-400">Món yêu thích</p>
        </a>
        <a href="{{ route('addresses.index') }}" class="bg-white rounded-2xl border border-slate-100 p-4 text-center hover:border-red-400 transition">
            <p class="text-2xl font-bold text-red-500">{{ $addressCount }}</p>
            <p class="text-xs text-slate-400">Địa chỉ đã lưu</p>
        </a>
    </div>
 
    {{-- Thông tin cá nhân --}}
    <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        @csrf
        @method('PUT')
        <h2 class="font-semibold text-slate-800 mb-4">Thông tin cá nhân</h2>
 
        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm space-y-1">
                @foreach ($errors->all() as $e) <p>• {{ $e }}</p> @endforeach
            </div>
        @endif
 
        <div class="flex items-center gap-4 mb-6">
            <div id="avatarBox" class="w-20 h-20 rounded-full bg-red-100 text-red-500 text-2xl flex items-center justify-center overflow-hidden shrink-0">
                @if ($user->avatar)
                    <img src="{{ asset($user->avatar) }}" class="w-full h-full object-cover" alt="avatar">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div>
                <label class="cursor-pointer inline-block px-4 py-2 rounded-xl border border-slate-200 text-sm hover:bg-slate-50">
                    Đổi ảnh đại diện
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" class="hidden">
                </label>
                <p id="avatarHint" class="text-xs text-slate-400 mt-1">JPG, PNG tối đa 2MB. Chọn ảnh xong nhớ bấm "Lưu thay đổi".</p>
            </div>
        </div>
 
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-slate-500">Họ và tên</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="text-sm text-slate-500">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="text-sm text-slate-500">Số điện thoại</label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0901 234 567" class="{{ $input }}">
            </div>
            <div>
                <label class="text-sm text-slate-500">Ngày sinh</label>
                <input type="date" name="birthday" value="{{ old('birthday', $user->birthday ? substr($user->birthday, 0, 10) : '') }}" class="{{ $input }}">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm text-slate-500">Giới tính</label>
                <div class="flex gap-6 mt-2 text-sm text-slate-700">
                    @foreach (['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'] as $val => $text)
                        <label><input type="radio" name="gender" value="{{ $val }}" class="accent-red-500"
                            {{ old('gender', $user->gender) === $val ? 'checked' : '' }}> {{ $text }}</label>
                    @endforeach
                </div>
            </div>
        </div>
 
        <button type="submit" class="mt-6 px-6 py-2.5 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-600 transition">
            Lưu thay đổi
        </button>
    </form>
 
    {{-- Đổi mật khẩu --}}
    <form method="POST" action="{{ route('account.password') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        @csrf
        @method('PUT')
        <h2 class="font-semibold text-slate-800 mb-4">Đổi mật khẩu</h2>
 
        @if ($pwErr->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm space-y-1">
                @foreach ($pwErr->all() as $e) <p>• {{ $e }}</p> @endforeach
            </div>
        @endif
 
        <div class="grid md:grid-cols-3 gap-4">
            <input type="password" name="current_password" placeholder="Mật khẩu hiện tại" class="px-4 py-2.5 rounded-xl border border-slate-200 focus:border-red-400 focus:outline-none">
            <input type="password" name="password" placeholder="Mật khẩu mới" class="px-4 py-2.5 rounded-xl border border-slate-200 focus:border-red-400 focus:outline-none">
            <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu mới" class="px-4 py-2.5 rounded-xl border border-slate-200 focus:border-red-400 focus:outline-none">
        </div>
        <button type="submit" class="mt-4 px-6 py-2.5 rounded-xl border border-red-500 text-red-500 font-semibold hover:bg-red-50 transition">
            Cập nhật mật khẩu
        </button>
    </form>
 
    {{-- Xóa tài khoản --}}
    <form method="POST" action="{{ route('account.destroy') }}"
          onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác.')"
          class="bg-white rounded-2xl border border-red-100 p-6 flex items-center justify-between gap-4">
        @csrf
        @method('DELETE')
        <div>
            <p class="font-semibold text-slate-800">Xóa tài khoản</p>
            <p class="text-sm text-slate-400">Hành động này không thể hoàn tác.</p>
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl bg-red-50 text-red-500 text-sm font-semibold hover:bg-red-100">Xóa</button>
    </form>
</div>
 
<script>
    document.getElementById('avatarInput').addEventListener('change', function () {
        if (!this.files[0]) return;
        const box = document.getElementById('avatarBox');
        box.innerHTML = '<img class="w-full h-full object-cover" src="' + URL.createObjectURL(this.files[0]) + '">';
        document.getElementById('avatarHint').textContent = 'Đã chọn: ' + this.files[0].name + ' — bấm "Lưu thay đổi" để cập nhật.';
    });
</script>
@endsection