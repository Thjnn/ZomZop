@extends('layouts.app')

@section('title', 'Đăng Ký — ZomZop')

@section('content')

<div class="flex items-center justify-center py-6 flex-1">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-lg overflow-hidden flex">

        {{-- CỘT TRÁI — Ảnh đồ ăn --}}
        <div class="hidden md:block w-1/2 relative">
            <img
                src="{{ asset('images/products/buger-ga-crispy-2.jpg') }}"
                alt="ZomZop Food"
                class="w-full h-full object-cover">
            {{-- Overlay gradient --}}
            <div class="absolute inset-0 bg-gradient-to-t from-red-900/70 to-transparent flex flex-col justify-end p-8">
                <h3 class="text-white text-2xl font-bold mb-2">Tham gia ZomZop</h3>
                <p class="text-white/80 text-sm">Đăng ký miễn phí · Ưu đãi hấp dẫn · Giao hàng nhanh</p>
            </div>
        </div>

        {{-- CỘT PHẢI — Form đăng ký --}}
        <div class="w-full md:w-1/2 p-10 overflow-y-auto">

            {{-- Logo --}}
            <div class="mb-6">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/text-logo.png') }}" alt="ZomZop" class="h-16 w-auto object-contain">
                </a>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-2">Tạo tài khoản mới 🎉</h2>
            <p class="text-gray-500 text-sm mb-6">Đăng ký để bắt đầu đặt món ngon mỗi ngày.</p>

            {{-- Form --}}
            <form action="{{ route('register') }}" method="POST">
                @csrf

                {{-- Họ tên --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Nguyễn Văn A"
                        class="w-full border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-400
                               {{ $errors->has('name') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="example@email.com"
                        class="w-full border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-400
                               {{ $errors->has('email') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Số điện thoại --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="0xxxxxxxxx"
                        class="w-full border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-400
                               {{ $errors->has('phone') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mật khẩu --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Ít nhất 6 ký tự"
                            class="w-full border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 pr-10
                                   {{ $errors->has('password') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <button type="button" onclick="togglePassword('password')"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                            {{-- Mắt thường (đang ẩn) --}}
                            <svg id="eye-show-password" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{-- Mắt gạch xéo (đang hiện) --}}
                            <svg id="eye-hide-password" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Xác nhận mật khẩu --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            placeholder="Nhập lại mật khẩu"
                            class="w-full border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 pr-10 border-gray-300">
                        <button type="button" onclick="togglePassword('password_confirmation')"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                            {{-- Mắt thường (đang ẩn) --}}
                            <svg id="eye-show-password_confirmation" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{-- Mắt gạch xéo (đang hiện) --}}
                            <svg id="eye-hide-password_confirmation" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Nút đăng ký --}}
                <button
                    type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition duration-200">
                    Tạo Tài Khoản
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center my-6">
                <div class="flex-1 border-t border-gray-200"></div>
                <span class="px-3 text-sm text-gray-400">hoặc</span>
                <div class="flex-1 border-t border-gray-200"></div>
            </div>

            {{-- Link đăng nhập --}}
            <p class="text-center text-sm text-gray-600">
                Đã có tài khoản?
                <a href="{{ route('login') }}" class="text-red-500 font-semibold hover:underline">Đăng nhập</a>
            </p>

            {{-- Back to home --}}
            <p class="text-center mt-4 text-sm text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-red-500">← Quay về trang chủ</a>
            </p>

        </div>

    </div>
</div>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const eyeShow = document.getElementById('eye-show-' + id);
        const eyeHide = document.getElementById('eye-hide-' + id);

        if (input.type === 'password') {
            input.type = 'text';
            eyeShow.classList.add('hidden');
            eyeHide.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeShow.classList.remove('hidden');
            eyeHide.classList.add('hidden');
        }
    }
</script>

@endsection