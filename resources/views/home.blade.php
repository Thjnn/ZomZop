@extends('layouts.app')

@section('title', 'ZomZop - Trang Chủ')

@section('content')
<section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 relative w-full h-[300px] rounded-2xl overflow-hidden shadow-xl">
        <div class="swiper mySwiper w-full h-full">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="{{ asset('images/banners/banner-1.jpeg') }}" class="w-full h-full object-cover">
                </div>
                <div class="swiper-slide">
                    <img src="{{ asset('images/banners/banner-2.jpeg') }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>

    <div class="bg-slate-100/60 rounded-2xl p-6 border border-slate-200/50">
        <h3 class="font-bold text-slate-700 mb-4 text-center">Khám Phá Thực Đơn</h3>
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer">
                <img src="{{ asset('images/categories/icon-buger.jpg') }}" alt="Buger" class="w-10 h-auto">
                <span class="text-xs font-medium text-slate-600">Buger</span>
            </div>
            <div class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer">
                <span class="text-2xl mb-1">🔥</span>
                <span class="text-xs font-medium text-slate-600">Bán Chạy</span>
            </div>
            <a href="/category/pizza" class="bg-[#fff] p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-transparent hover:scale-105 transition cursor-pointer no-underline">
                <img src="{{ asset('images/categories/icon-my-y.png') }}" alt="Mỳ ý" class="w-10 h-auto">
                <span class="text-xs font-bold text-slate-800">Mỳ ý</span>
            </a>
            <a href="/category/do-uong" class="bg-[#fff] p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-transparent hover:scale-105 transition cursor-pointer no-underline">
                <img src="{{ asset('images/categories/icon-ga-chien.jpg') }}" alt="Ga Chien" class="w-10 h-auto">
                <span class="text-xs font-bold text-slate-800">Gà rán</span>
            </a>
            <a href="/category/pizza" class="bg-[#fff] p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-transparent hover:scale-105 transition cursor-pointer no-underline">
                <img src="{{ asset('images/categories/icon-pizza.jpg') }}" alt="Pizza" class="w-10 h-auto">
                <span class="text-xs font-bold text-slate-800">Pizza</span>
            </a>
            <a href="/category/sandwich" class="bg-[#fff] p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-transparent hover:scale-105 transition cursor-pointer no-underline">
                <img src="{{ asset('images/categories/icon-sandwich.jpg') }}" alt="Sandwich" class="w-10 h-auto">
                <span class="text-xs font-bold text-slate-800">Sandwich</span>
            </a>
        </div>
    </div>
</section>

<section class="space-y-4">
    <div class="flex justify-between items-end">
        <h2 class="text-xl font-bold text-slate-800">Món Ngon Địa Phương</h2>
        <a href="#" class="text-xs font-semibold text-red-500 hover:underline">Xem Tất Cả</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group">
            <div class="relative bg-slate-100 h-36 flex items-center justify-center text-5xl">
                🍔🍟
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 40%</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-3 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-1">
                <h4 class="font-semibold text-sm text-slate-700 truncate">Combo Gà Cuộn & Khoai Tây Chiên</h4>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                    <span class="bg-green-100 text-green-700 text-[8px] font-bold px-1 rounded-sm">HALAL</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group">
            <div class="relative bg-slate-100 h-36 flex items-center justify-center text-5xl">
                🍚🍗
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-3 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-1">
                <h4 class="font-semibold text-sm text-slate-700 truncate">Cơm Gà Popcorn Giòn Rụm</h4>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="space-y-4">
    <div class="text-center">
        <h2 class="text-xl font-bold text-slate-800 flex items-center justify-center gap-1">Đầu Bếp Đề Xuất 👨‍🍳</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden p-4 space-y-3">
            <div class="relative bg-slate-100 h-44 rounded-xl flex items-center justify-center text-6xl">
                🍛
                <span class="absolute top-2 left-2 bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md">GIẢM 25.000 đ</span>
                <button class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
            </div>
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-bold text-slate-800">Cơm Bò Biriyani Đậm Vị Ý</h4>
                    <span class="w-3 h-3 border border-green-600 inline-flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                </div>
                <button class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1 hover:bg-red-600 transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                <span class="text-xs text-slate-400 line-through">125.000 đ</span>
                <span class="text-base font-bold text-red-500">100.000 đ</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden p-4 space-y-3">
            <div class="relative bg-slate-100 h-44 rounded-xl flex items-center justify-center text-6xl">
                🍨
                <span class="absolute top-2 left-2 bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md">GIẢM 10.000 đ</span>
                <button class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
            </div>
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-bold text-slate-800">Kem Ý Tráng Miệng Thượng Hạng</h4>
                    <div class="flex gap-1.5">
                        <span class="w-3 h-3 border border-green-600 inline-flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                        <span class="bg-green-100 text-green-700 text-[8px] font-bold px-1 rounded-sm">HALAL</span>
                    </div>
                </div>
                <button class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1 hover:bg-red-600 transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                <span class="text-xs text-slate-400 line-through">60.000 đ</span>
                <span class="text-base font-bold text-red-500">50.000 đ</span>
            </div>
        </div>

    </div>
</section>
<section class="space-y-4">
    <div class="flex justify-between items-end">
        <h2 class="text-lg font-bold text-slate-800">Tìm món ăn từ các Chi nhánh</h2>
        <a href="#" class="text-xs font-semibold text-red-500 hover:underline">Xem Tất Cả</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden flex flex-col hover:shadow-md transition">
            <div class="h-32 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=500')"></div>
            <div class="p-4 flex items-center gap-3">
                <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=100" class="w-12 h-12 rounded-xl object-cover border border-slate-100 shadow-2xs" alt="Branch">
                <div>
                    <h4 class="font-bold text-sm text-slate-800">Chi Nhánh Trung Tâm</h4>
                    <p class="text-xs text-slate-400 flex items-center gap-0.5 mt-0.5">📍 Quận 1, TP. HCM</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden flex flex-col hover:shadow-md transition">
            <div class="h-32 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1549488344-1f9b8d2bd1f3?w=500')"></div>
            <div class="p-4 flex items-center gap-3">
                <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=100" class="w-12 h-12 rounded-xl object-cover border border-slate-100 shadow-2xs" alt="Branch">
                <div>
                    <h4 class="font-bold text-sm text-slate-800">ZomZop Drip & Dusk</h4>
                    <p class="text-xs text-slate-400 flex items-center gap-0.5 mt-0.5">📍 Quận 3, TP. HCM</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden flex flex-col hover:shadow-md transition">
            <div class="h-32 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1552566626-52f8b828add9?w=500')"></div>
            <div class="p-4 flex items-center gap-3">
                <img src="https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=100" class="w-12 h-12 rounded-xl object-cover border border-slate-100 shadow-2xs" alt="Branch">
                <div>
                    <h4 class="font-bold text-sm text-slate-800">ZomZop Windy Bean</h4>
                    <p class="text-xs text-slate-400 flex items-center gap-0.5 mt-0.5">📍 Bình Thạnh, TP. HCM</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="space-y-4">
    <div class="flex justify-between items-end">
        <h2 class="text-lg font-bold text-slate-800">Món Ăn Mới Nhất</h2>
        <div class="flex items-center gap-2 text-slate-400">
            <button class="p-1.5 bg-white border border-slate-200 rounded-md text-red-500 shadow-2xs">⣿</button>
            <button class="p-1.5 bg-white border border-slate-200 rounded-md hover:text-slate-600">☰</button>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1501443762994-82bd5dace89a?w=300" alt="Kem" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 30k</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Ice Cream Trái Cây</h4>
                <div class="flex items-center gap-1 text-[10px] text-green-600 font-bold">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px]">●</span>
                    <span class="bg-green-50 text-green-700 text-[8px] px-1 rounded-xs">CHAY</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 line-through">100.000 đ</span>
                    <span class="text-sm font-bold text-red-500">70.000 đ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=300" alt="Cafe" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 5%</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Special Cold Coffee</h4>
                <div class="flex items-center gap-1 text-[10px] text-green-600 font-bold">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px]">●</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 line-through">60.000 đ</span>
                    <span class="text-sm font-bold text-red-500">57.000 đ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300" alt="Burger" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 10%</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Buddy Zinger Combo</h4>
                <div class="flex items-center gap-1 text-[10px] text-red-600 font-bold">
                    <span class="w-3 h-3 border border-red-600 flex items-center justify-center text-[6px]">●</span>
                    <span class="bg-red-50 text-red-700 text-[8px] px-1 rounded-xs">MẶN</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 line-through">120.000 đ</span>
                    <span class="text-sm font-bold text-red-500">108.000 đ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=300" alt="Chizza" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 14%</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Chizza Meal Thượng Hạng</h4>
                <div class="flex items-center gap-1 text-xs text-amber-500 font-semibold">
                    <span>3.0 ★</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 line-through">180.000 đ</span>
                    <span class="text-sm font-bold text-red-500">154.800 đ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300" alt="Rice Bowl" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Popcorn Rice Bowl</h4>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 border border-red-600 flex items-center justify-center text-[6px] text-red-600 font-bold">●</span>
                    <span class="bg-green-100 text-green-700 text-[8px] font-bold px-1 rounded-sm">HALAL</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-sm font-bold text-slate-800">95.000 đ</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1501443762994-82bd5dace89a?w=300" alt="Kem" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 30k</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Ice Cream Trái Cây</h4>
                <div class="flex items-center gap-1 text-[10px] text-green-600 font-bold">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px]">●</span>
                    <span class="bg-green-50 text-green-700 text-[8px] px-1 rounded-xs">CHAY</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 line-through">100.000 đ</span>
                    <span class="text-sm font-bold text-red-500">70.000 đ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=300" alt="Cafe" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 5%</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Special Cold Coffee</h4>
                <div class="flex items-center gap-1 text-[10px] text-green-600 font-bold">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px]">●</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 line-through">60.000 đ</span>
                    <span class="text-sm font-bold text-red-500">57.000 đ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300" alt="Burger" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 10%</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Buddy Zinger Combo</h4>
                <div class="flex items-center gap-1 text-[10px] text-red-600 font-bold">
                    <span class="w-3 h-3 border border-red-600 flex items-center justify-center text-[6px]">●</span>
                    <span class="bg-red-50 text-red-700 text-[8px] px-1 rounded-xs">MẶN</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 line-through">120.000 đ</span>
                    <span class="text-sm font-bold text-red-500">108.000 đ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=300" alt="Chizza" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 14%</span>
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Chizza Meal Thượng Hạng</h4>
                <div class="flex items-center gap-1 text-xs text-amber-500 font-semibold">
                    <span>3.0 ★</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 line-through">180.000 đ</span>
                    <span class="text-sm font-bold text-red-500">154.800 đ</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between hover:shadow-sm transition">
            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300" alt="Rice Bowl" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
            </div>
            <div class="p-3 space-y-2">
                <h4 class="font-bold text-sm text-slate-700 line-clamp-1">Popcorn Rice Bowl</h4>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 border border-red-600 flex items-center justify-center text-[6px] text-red-600 font-bold">●</span>
                    <span class="bg-green-100 text-green-700 text-[8px] font-bold px-1 rounded-sm">HALAL</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-sm font-bold text-slate-800">95.000 đ</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection