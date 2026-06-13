@extends('layouts.app')

@section('title', 'Đăng Nhập — ZomZop')

@section('content')

<body class="min-h-screen flex items-center justify-center bg-gray-100 py-10">

    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}">
                <h1 class="text-4xl font-bold text-brand">ZomZop</h1>
                <p class="text-gray-500 mt-1">Ăn ngon mỗi ngày!</p>
            </a>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg p-8">

            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Tạo Tài Khoản</h2>

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
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 pr-10">
                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Nút đăng ký --}}
                <button
                    type="submit"
                    class="w-full bg-brand text-white font-semibold py-3 rounded-lg hover-brand transition duration-200">
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
                <a href="{{ route('login') }}" class="text-brand font-semibold hover:underline">Đăng nhập</a>
            </p>

        </div>

        {{-- Back to home --}}
        <p class="text-center mt-4 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-brand">← Quay về trang chủ</a>
        </p>

    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>

@endsection