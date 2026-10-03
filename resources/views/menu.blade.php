@extends('layouts.app')
 
@section('title', 'Danh mục tài khoản - ZomZop')
 
@section('content')
<div class="max-w-6xl mx-auto py-8">
    
    {{-- ==================== THÔNG TIN NGƯỜI DÙNG ==================== --}}
    <div class="bg-red-50 rounded-2xl p-6 flex items-center gap-6 mb-10">
        <div class="w-20 h-20 bg-red-200 rounded-full flex items-center justify-center overflow-hidden">
            {{-- Icon Avatar --}}
            <svg class="w-12 h-12 text-red-100" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        </div>
        <div>
            @auth
                <h2 class="text-xl font-semibold text-slate-800">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>
            @else
                <h2 class="text-xl font-semibold text-slate-800">Khách</h2>
            @endauth
        </div>
    </div>
 
    {{-- ==================== DANH SÁCH CHỨC NĂNG ==================== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
        
        {{-- Hồ sơ (Bật popup nếu chưa đăng nhập) --}}
        <a @auth href="{{ url('/profile') }}" @else href="#" onclick="openAuthModal(event)" @endauth class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
            <span class="text-sm font-medium">Hồ sơ</span>
        </a>
 
        {{-- Đơn hàng của tôi (Bật popup nếu chưa đăng nhập) --}}
        <a @auth href="{{ url('/orders') }}" @else href="#" onclick="openAuthModal(event)" @endauth class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <span class="text-sm font-medium">Đơn hàng</span>
        </a>
 
        {{-- Yêu thích (Bật popup nếu chưa đăng nhập) --}}
        <a @auth href="{{ route('favorites.index') }}" @else href="#" onclick="openAuthModal(event)" @endauth class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            </svg>
            <span class="text-sm font-medium">Yêu thích</span>
        </a>
 
        {{-- Thông báo --}}
        <a href="{{ route('notifications') }}" class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
            <span class="text-sm font-medium">Thông báo</span>
        </a>
 
        {{-- Mã giảm giá --}}
        <a href="{{ route('coupons') }}" class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
            </svg>
            <span class="text-sm font-medium">Mã giảm giá</span>
        </a>
 
        {{-- Địa chỉ (Bật popup nếu chưa đăng nhập) --}}
        <a @auth href="{{ url('/addresses') }}" @else href="#" onclick="openAuthModal(event)" @endauth class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
            <span class="text-sm font-medium">Địa chỉ</span>
        </a>
 
        {{-- Về chúng tôi --}}
        <a href="{{ route('about-us') }}" class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            <span class="text-sm font-medium">Về chúng tôi</span>
        </a>
 
        {{-- Hỗ trợ --}}
        <a href="{{ route('support') }}" class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
            </svg>
            <span class="text-sm font-medium">Hỗ trợ</span>
        </a>
 
        {{-- Chính sách bảo mật --}}
        <a href="{{ route('privacy-policy') }}" class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
            <svg class="w-10 h-10 mb-4 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <span class="text-sm font-medium text-center leading-tight">Chính sách<br>bảo mật</span>
        </a>
 
        {{-- Nút Đăng Nhập / Đăng Xuất (Hiển thị tự động) --}}
        @auth
            <form action="{{ route('logout') }}" method="POST" class="h-full">
                @csrf
                <button type="submit" class="w-full h-full flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300 cursor-pointer">
                    <svg class="w-10 h-10 mb-4 text-red-500 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                    </svg>
                    <span class="text-sm font-medium">Đăng xuất</span>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border-2 border-transparent text-slate-700 hover:border-red-500 hover:text-red-500 transition-all duration-300">
                <svg class="w-10 h-10 mb-4 text-red-500 stroke-current" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
                <span class="text-sm font-medium">Đăng nhập</span>
            </a>
        @endauth
 
    </div>
</div>
 
{{-- NÚT CHAT MÀU XANH NỔI Ở GÓC DƯỚI BÊN PHẢI --}}
<a href="#" class="fixed bottom-24 right-6 z-50 bg-green-500 hover:bg-green-600 text-white font-medium px-5 py-3 rounded-full shadow-lg flex items-center gap-2 transition-transform hover:scale-105">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12.031 2C6.486 2 2 6.486 2 12.031c0 1.954.551 3.844 1.597 5.46L2 22l4.664-1.57A9.972 9.972 0 0012.031 22c5.545 0 10.031-4.486 10.031-10.031S17.576 2 12.031 2zm0 18.39c-1.636 0-3.238-.42-4.636-1.215l-.33-.188-3.09 1.04 1.056-3.012-.206-.327a8.384 8.384 0 01-1.284-4.529C3.541 7.371 7.372 3.541 12.031 3.541c4.66 0 8.49 3.83 8.49 8.49 0 4.66-3.83 8.359-8.49 8.359zM16.7 13.91c-.256-.128-1.516-.749-1.75-.835-.234-.085-.404-.128-.574.128-.17.255-.66.835-.809 1.006-.149.17-.298.192-.553.064-.255-.128-1.082-.4-2.062-1.277-.763-.681-1.278-1.523-1.427-1.778-.149-.255-.016-.393.111-.52.115-.115.255-.298.383-.447.128-.149.17-.255.255-.426.085-.17.043-.319-.021-.447-.064-.128-.574-1.385-.787-1.896-.208-.498-.42-.43-.574-.438-.149-.009-.319-.009-.489-.009-.17 0-.447.064-.681.319-.234.255-.894.873-.894 2.129s.915 2.469 1.043 2.64c.128.17 1.8 2.747 4.364 3.853 3.012 1.3 3.555 1.043 4.194.98 .639-.064 2.065-.843 2.363-1.661.298-.817.298-1.515.208-1.661-.09-.146-.328-.232-.584-.36z"/>
    </svg>
    Chat
</a>
 
{{-- POPUP YÊU CẦU ĐĂNG NHẬP (Chỉ hiển thị khi chưa đăng nhập) --}}
@guest
<div id="auth-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 opacity-0 invisible transition-all duration-300">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 transform scale-95 transition-transform duration-300 relative">
        <button onclick="closeAuthModal()" class="absolute top-4 right-4 text-slate-400 hover:text-red-500 cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Đăng nhập hoặc Đăng ký</h3>
            <p class="text-sm text-slate-500 mb-6">Đăng nhập để quản lý chi tiết tài khoản và cài đặt của bạn tại ZomZop.</p>
            
            <div class="flex gap-4">
                <a href="{{ route('register') }}" class="flex-1 py-2.5 border border-red-500 text-red-500 rounded-lg font-medium hover:bg-red-50 transition">Đăng ký</a>
                <a href="{{ route('login') }}" class="flex-1 py-2.5 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition">Đăng nhập</a>
            </div>
        </div>
    </div>
</div>
 
<script>
    function openAuthModal(e) {
        e.preventDefault();
        const modal = document.getElementById('auth-modal');
        modal.classList.remove('opacity-0', 'invisible');
        modal.firstElementChild.classList.remove('scale-95');
    }
    function closeAuthModal() {
        const modal = document.getElementById('auth-modal');
        modal.classList.add('opacity-0', 'invisible');
        modal.firstElementChild.classList.add('scale-95');
    }
</script>
@endguest
@endsection