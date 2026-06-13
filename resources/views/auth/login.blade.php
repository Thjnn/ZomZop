@extends('layouts.app')

@section('title', 'Đăng Nhập — ZomZop')

@section('content')

<div class="flex items-center justify-center py-6 flex-1">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-lg overflow-hidden flex">

        {{-- CỘT TRÁI — Form đăng nhập --}}
        <div class="w-full md:w-1/2 p-10">

            {{-- Logo --}}
            <div class="mb-8">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/text-logo.png') }}" alt="ZomZop" class="h-16 w-auto object-contain">
                </a>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-2">Chào mừng trở lại! 👋</h2>
            <p class="text-gray-500 text-sm mb-6">Đăng nhập để đặt món ngon mỗi ngày.</p>

            {{-- Thông báo --}}
            @if (session('success'))
            <div class="bg-green-50 border border-green-300 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
                {{ session('success') }}
            </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('login') }}" method="POST">
                @csrf

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

                {{-- Mật khẩu --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Nhập mật khẩu"
                            class="w-full border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 pr-10
                                   {{ $errors->has('password') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <button type="button" onclick="togglePassword('password')"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 cursor-pointer">

                            {{-- Mắt thường (đang ẩn mật khẩu) --}}
                            <svg id="eye-show-password" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>

                            {{-- Mắt gạch xéo (đang hiện mật khẩu) --}}
                            <svg id="eye-hide-password" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ghi nhớ + Quên mật khẩu --}}
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300">
                        Ghi nhớ đăng nhập
                    </label>
                    <a href="#" class="text-sm text-red-500 hover:underline">Quên mật khẩu?</a>
                </div>

                {{-- Nút đăng nhập --}}
                <button
                    type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition duration-200">
                    Đăng Nhập
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center my-6">
                <div class="flex-1 border-t border-gray-200"></div>
                <span class="px-3 text-sm text-gray-400">hoặc</span>
                <div class="flex-1 border-t border-gray-200"></div>
            </div>

            {{-- Link đăng ký --}}
            <p class="text-center text-sm text-gray-600">
                Chưa có tài khoản?
                <a href="{{ route('register') }}" class="text-red-500 font-semibold hover:underline">Đăng ký ngay</a>
            </p>

            {{-- Back to home --}}
            <p class="text-center mt-4 text-sm text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-red-500">← Quay về trang chủ</a>
            </p>

        </div>

        {{-- CỘT PHẢI — Ảnh đồ ăn --}}
        <div class="hidden md:block w-1/2 relative">
            <img
                src="{{ asset('images/products/pizza-haisan-1.jpg') }}"
                alt="ZomZop Food"
                class="w-full h-full object-cover">
            {{-- Overlay gradient --}}
            <div class="absolute inset-0 bg-gradient-to-t from-red-900/70 to-transparent flex flex-col justify-end p-8">
                <h3 class="text-white text-2xl font-bold mb-2">Đặt món ngon</h3>
                <p class="text-white/80 text-sm">Giao hàng nhanh · Đồ ăn tươi · Giá tốt mỗi ngày</p>
            </div>
        </div>

    </div>
</div>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

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