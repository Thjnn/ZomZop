<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZomZop - Trang Chủ Gọi Món</title>

    <link class="rounded-full" rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800">

    <header class="sticky top-0 z-50 bg-white shadow-xs border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-6 flex-shrink-0">
                <a href="/" class="flex items-center gap-2 hover:opacity-90 transition">
                    <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop Avatar" class="h-9 w-auto object-contain">
                    <img src="{{ asset('images/text-logo.png') }}" alt="ZomZop Fast Food" class="h-7 w-auto object-contain">
                </a>
            </div>

            <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="/" class="hover:text-red-500 transition">Trang Chủ</a>
                <a href="#" class="text-red-500">Danh Mục</a>
            </nav>

            <div class="flex-1 max-w-md mx-4">
                <div class="relative">
                    <input type="text" placeholder="Bạn đang đói bụng cồn cào?" class="w-full bg-slate-100 pl-10 pr-4 py-2 rounded-full text-sm border border-transparent focus:bg-white focus:border-red-400 focus:outline-hidden transition">
                    <span class="absolute left-3.5 top-2.5 text-slate-400 text-sm">🔍</span>
                </div>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <div class="hidden sm:block text-right">
                    <p class="text-[10px] text-slate-400">Chi nhánh</p>
                    <select class="text-xs font-semibold text-slate-700 bg-transparent focus:outline-hidden cursor-pointer">
                        <option>Chi nhánh chính</option>
                    </select>
                </div>
                <button class="relative p-2 hover:bg-slate-100 rounded-full transition cursor-pointer">
                    <span class="text-xl">❤️</span>
                    <span class="absolute top-1 right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">0</span>
                </button>
                <button class="relative p-2 hover:bg-slate-100 rounded-full transition cursor-pointer">
                    <span class="text-xl">🛍️</span>
                    <span class="absolute top-1 right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">0</span>
                </button>
                <button class="p-2 hover:bg-slate-100 rounded-full transition cursor-pointer hidden sm:block">👤</button>
                <button class="p-2 hover:bg-slate-100 rounded-full transition cursor-pointer">☰</button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 space-y-10">

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-red-500 rounded-2xl p-6 text-white relative overflow-hidden flex flex-col md:flex-row justify-between items-center min-h-[280px]">
                <div class="space-y-4 max-w-xs z-10 text-center md:text-left">
                    <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">Gợi Ý Hôm Nay</span>
                    <h1 class="text-3xl font-black tracking-tight leading-tight">Đại Tiệc <br>THỨC ĂN NHANH</h1>
                    <p class="text-yellow-300 font-bold text-xl">Giảm ngay 50% <span class="text-xs block text-white font-normal">Áp dụng cho các thực đơn được chọn.</span></p>
                    <div class="bg-white text-red-500 inline-block px-4 py-1.5 rounded-full font-bold text-sm shadow-md">
                        Đặt ngay! Hotline: 1900 xxxx
                    </div>
                </div>
                <div class="relative w-64 h-48 mt-4 md:mt-0 bg-yellow-500/20 rounded-full border-4 border-dashed border-white/30 flex items-center justify-center text-6xl">
                    🍔🍟
                    <div class="absolute -top-2 -left-2 bg-yellow-400 text-red-600 font-black rounded-full w-16 h-14 flex flex-col items-center justify-center text-xs shadow-lg transform -rotate-12">
                        <span class="line-through text-[9px] text-red-500/70">200k</span>
                        <span>100k</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-100/60 rounded-2xl p-6 border border-slate-200/50">
                <h3 class="font-bold text-slate-700 mb-4 text-center">Khám Phá Thực Đơn</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer">
                        <span class="text-2xl mb-1">🍱</span>
                        <span class="text-xs font-medium text-slate-600">Combo</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer">
                        <span class="text-2xl mb-1">🔥</span>
                        <span class="text-xs font-medium text-slate-600">Bán Chạy</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer">
                        <span class="text-2xl mb-1">🍛</span>
                        <span class="text-xs font-medium text-slate-600">Cơm Gà</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer">
                        <span class="text-2xl mb-1">🍹</span>
                        <span class="text-xs font-medium text-slate-600">Nước Giải Khát</span>
                    </div>
                    <a href="/category/pizza" class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer no-underline text-inherit">
                        <span class="text-2xl mb-1">🍕</span>
                        <span class="text-xs font-medium text-slate-600">Pizza</span>
                    </a>
                    <div class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer">
                        <span class="text-2xl mb-1">🥪</span>
                        <span class="text-xs font-medium text-slate-600">Sandwich</span>
                    </div>
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

    </main>

    <footer class="bg-white border-t border-slate-100 mt-16 text-slate-600 text-sm">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

                <div class="space-y-4">
                    <a href="/" class="flex items-center gap-2 hover:opacity-90 transition">
                        <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop Avatar" class="h-9 w-auto object-contain">
                        <img src="{{ asset('images/text-logo.png') }}" alt="ZomZop Fast Food" class="h-7 w-auto object-contain">
                    </a>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        ZomZop - Chuỗi hệ thống thức ăn nhanh chất lượng cao, mang đến những bữa ăn ngon miệng, nhanh chóng và tràn đầy năng lượng cho ngày dài của bạn.
                    </p>
                    <div class="flex items-center gap-3 text-lg select-none">
                        <a href="#" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition">🌐</a>
                        <a href="#" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition">💬</a>
                        <a href="#" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition">📮</a>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm tracking-wide uppercase">Thực Đơn</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#" class="hover:text-red-500 transition">Combo Ưu Đãi</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Gà Rán Giòn Rụm</a></li>
                        <li><a href="/category/pizza" class="hover:text-red-500 transition">Pizza Ý Thượng Hạng</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Tráng Miệng & Đồ Uống</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm tracking-wide uppercase">Chính Sách & Hỗ Trợ</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#" class="hover:text-red-500 transition">Hình thức thanh toán</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Chính sách giao hàng</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Chính sách bảo mật thông tin</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Phản hồi & Góp ý</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm tracking-wide uppercase">Thông Tin Liên Hệ</h4>
                    <ul class="space-y-2 text-xs text-slate-500">
                        <li class="flex items-center gap-1.5">
                            <span>📞</span> <strong class="text-slate-700">Hotline:</strong> 1900 xxxx
                        </li>
                        <li class="flex items-center gap-1.5">
                            <span>✉️</span> <strong class="text-slate-700">Email:</strong> contact@zomzop.com
                        </li>
                        <li class="flex items-center gap-1.5">
                            <span>⏰</span> <strong class="text-slate-700">Giờ mở cửa:</strong> 09:00 - 22:00
                        </li>
                        <li class="flex items-start gap-1.5">
                            <span class="mt-0.5">📍</span> <span><strong class="text-slate-700">Trụ sở:</strong> Toà nhà ZomZop, Quận 1, TP. Hồ Chí Minh</span>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="border-t border-slate-100 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>© 2026 ZomZop Fast Food. Tất cả các quyền được bảo lưu.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:underline">Điều khoản sử dụng</a>
                    <a href="#" class="hover:underline">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>