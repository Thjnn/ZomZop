@extends('layouts.app')

@section('title', 'ZomZop - Trang Chủ')

@section('content')
<!-- Hero Section -->
<!-- Banner -->
<section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 relative w-full overflow-hidden rounded-2xl shadow-xl h-[220px] sm:h-[280px] lg:h-[300px]">
        <div class="swiper mySwiper h-full w-full">
            <div class="swiper-wrapper">
                <div class="swiper-slide h-full">
                    <img src="{{ asset('images/banners/banner-1.jpeg') }}" class="h-full w-full object-cover object-center" alt="Banner khuyến mãi 1">
                </div>
                <div class="swiper-slide h-full">
                    <img src="{{ asset('images/banners/banner-2.jpeg') }}" class="h-full w-full object-cover object-center" alt="Banner khuyến mãi 2">
                </div>
                <div class="swiper-slide h-full">
                    <img src="{{ asset('images/banners/banner-3.jpg') }}" class="h-full w-full object-cover object-center" alt="Banner khuyến mãi 3">
                </div>
            </div>
        </div>
    </div>
    <!-- Categories -->
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
<!-- Popular Items -->
<section class="space-y-4">
    <div class="flex justify-between items-end">
        <h2 class="text-xl font-bold text-slate-800">Món Ngon Địa Phương</h2>
        <a href="#" class="text-xs font-semibold text-red-500 hover:underline">Xem Tất Cả</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#f6e6d6]">
                    <img src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=300" alt="Cafe" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 5%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Combo Gà Cuộn & Khoai Tây Chiên</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                    <span class="rounded-sm bg-green-100 px-1.5 py-0.5 text-[8px] font-bold text-green-700">HALAL</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#fce8d9]">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300" alt="Burger" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 10%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Cơm Gà Popcorn Giòn Rụm</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#f6e6d6]">
                    <img src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=300" alt="Cafe" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 5%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Combo Gà Cuộn & Khoai Tây Chiên</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                    <span class="rounded-sm bg-green-100 px-1.5 py-0.5 text-[8px] font-bold text-green-700">HALAL</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#fce8d9]">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300" alt="Burger" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 10%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Cơm Gà Popcorn Giòn Rụm</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#fce8d9]">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300" alt="Burger" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 10%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Cơm Gà Popcorn Giòn Rụm</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                </div>
            </div>
        </article>

    </div>
</section>
<!-- Special Offers By Chef -->
<section class="space-y-4">
    <div class="text-center">
        <h2 class="text-2xl font-bold text-[#5b2d2d]">Ưu Đãi Đặc Biệt Từ Đầu Bếp</h2>
        <p class="mt-1 text-sm text-slate-500">Đề xuất nổi bật trong hôm nay</p>
    </div>
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-[#fff1f1] via-[#fff7f5] to-[#fff1f1] px-5 pb-10 pt-8 shadow-sm ring-1 ring-rose-100 sm:px-8 lg:px-20">
        <button class="special-offers-prev absolute left-4 top-1/2 z-10 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border-2 border-[#ff7b76] bg-white/70 text-[#ff7b76] transition hover:bg-[#ff7b76] hover:text-white lg:flex" aria-label="Ưu đãi trước">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6" />
            </svg>
        </button>
        <button class="special-offers-next absolute right-4 top-1/2 z-10 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border-2 border-[#ff7b76] bg-white/70 text-[#ff7b76] transition hover:bg-[#ff7b76] hover:text-white lg:flex" aria-label="Ưu đãi tiếp theo">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6" />
            </svg>
        </button>

        <div class="swiper specialOffersSwiper pb-4">
            <div class="swiper-wrapper">
                <article class="swiper-slide rounded-[1.7rem] bg-white shadow-[0_20px_45px_rgba(253,164,175,0.18)]">
                    <div class="relative px-6 pt-6">
                        <div class="relative h-72 overflow-hidden rounded-[1.7rem] bg-[#ed1c24]">
                            <img src="{{ asset('images/products/mi-kem-ga-1.png') }}" alt="Chizza Gà Phô Mai" class="h-full w-full object-cover transition duration-300 hover:scale-105">
                            <span class="absolute left-4 top-4 rounded-full bg-[#ff6a61] px-4 py-2 text-sm font-bold text-white">Giảm 14%</span>
                            <button class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                                </svg>
                            </button>
                        </div>
                        <button class="absolute -bottom-5 left-12 z-10 inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-[1.05rem] font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                            Thêm
                        </button>
                    </div>
                    <div class="px-6 pb-8 pt-10 text-center">
                        <h3 class="text-[1.05rem] font-medium tracking-[0.01em] text-slate-800">Chizza Gà Phô Mai</h3>
                        <div class="mt-2 flex items-center justify-center gap-1 text-sm text-slate-700">
                            <span>3.0</span>
                            <span class="text-amber-400">★</span>
                        </div>
                        <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                            <span class="text-slate-400 line-through">300.00 $</span>
                            <span class="font-semibold text-slate-800">258.00 $</span>
                        </div>
                    </div>
                </article>

                <article class="swiper-slide rounded-[1.7rem] bg-white shadow-[0_20px_45px_rgba(253,164,175,0.18)]">
                    <div class="relative px-6 pt-6">
                        <div class="relative h-72 overflow-hidden rounded-[1.7rem] bg-[#f6e6d6]">
                            <img src="{{ asset('images/products/ga-chien.webp') }}" alt="Cơm Gà Popcorn" class="h-full w-full object-cover transition duration-300 hover:scale-105">
                            <button class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                                </svg>
                            </button>
                        </div>
                        <button class="absolute -bottom-5 left-12 z-10 inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-[1.05rem] font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                            Thêm
                        </button>
                    </div>
                    <div class="px-6 pb-8 pt-10 text-center">
                        <h3 class="text-[1.05rem] font-medium tracking-[0.01em] text-slate-800">Cơm Gà Popcorn</h3>
                        <div class="mt-3 flex items-center justify-center gap-2 text-lg font-semibold text-slate-800">
                            <span>130.00 $</span>
                        </div>
                    </div>
                </article>

                <article class="swiper-slide rounded-[1.7rem] bg-white shadow-[0_20px_45px_rgba(253,164,175,0.18)]">
                    <div class="relative px-6 pt-6">
                        <div class="relative h-72 overflow-hidden rounded-[1.7rem] bg-[#c8131f]">
                            <img src="{{ asset('images/products/buger-ga-crispy-2.jpg') }}" alt="Burger Gà Giòn và Pop" class="h-full w-full object-cover transition duration-300 hover:scale-105">
                            <button class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                                </svg>
                            </button>
                        </div>
                        <button class="absolute -bottom-5 left-12 z-10 inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-[1.05rem] font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                            Thêm
                        </button>
                    </div>
                    <div class="px-6 pb-8 pt-10 text-center">
                        <h3 class="text-[1.05rem] font-medium tracking-[0.01em] text-slate-800">Burger Gà Giòn &amp; Pop</h3>
                        <div class="mt-2 flex items-center justify-center gap-1 text-sm text-slate-700">
                            <span>3.0</span>
                            <span class="text-amber-400">★</span>
                        </div>
                        <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                            <span class="font-semibold text-slate-800">100.00 $</span>
                        </div>
                    </div>
                </article>

                <article class="swiper-slide rounded-[1.7rem] bg-white shadow-[0_20px_45px_rgba(253,164,175,0.18)]">
                    <div class="relative px-6 pt-6">
                        <div class="relative h-72 overflow-hidden rounded-[1.7rem] bg-[#f8dfc7]">
                            <img src="{{ asset('images/products/pizza-phomai-1.jpg') }}" alt="Pizza Phô Mai" class="h-full w-full object-cover transition duration-300 hover:scale-105">
                            <span class="absolute left-4 top-4 rounded-full bg-[#ff6a61] px-4 py-2 text-sm font-bold text-white">Giảm 20%</span>
                            <button class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                                </svg>
                            </button>
                        </div>
                        <button class="absolute -bottom-5 left-12 z-10 inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-[1.05rem] font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                            Thêm
                        </button>
                    </div>
                    <div class="px-6 pb-8 pt-10 text-center">
                        <h3 class="text-[1.05rem] font-medium tracking-[0.01em] text-slate-800">Pizza Phô Mai</h3>
                        <div class="mt-2 flex items-center justify-center gap-1 text-sm text-slate-700">
                            <span>4.5</span>
                            <span class="text-amber-400">★</span>
                        </div>
                        <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                            <span class="text-slate-400 line-through">250.00 $</span>
                            <span class="font-semibold text-slate-800">200.00 $</span>
                        </div>
                    </div>
                </article>

                <article class="swiper-slide rounded-[1.7rem] bg-white shadow-[0_20px_45px_rgba(253,164,175,0.18)]">
                    <div class="relative px-6 pt-6">
                        <div class="relative h-72 overflow-hidden rounded-[1.7rem] bg-[#f4e5dd]">
                            <img src="{{ asset('images/products/sandwick-blt-1.jpg') }}" alt="Sandwich BLT" class="h-full w-full object-cover transition duration-300 hover:scale-105">
                            <button class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                                </svg>
                            </button>
                        </div>
                        <button class="absolute -bottom-5 left-12 z-10 inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-[1.05rem] font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                            Thêm
                        </button>
                    </div>
                    <div class="px-6 pb-8 pt-10 text-center">
                        <h3 class="text-[1.05rem] font-medium tracking-[0.01em] text-slate-800">Sandwich BLT</h3>
                        <div class="mt-2 flex items-center justify-center gap-1 text-sm text-slate-700">
                            <span>4.2</span>
                            <span class="text-amber-400">★</span>
                        </div>
                        <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                            <span class="font-semibold text-slate-800">145.00 $</span>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
<!-- Branches -->
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
<!-- New Arrivals -->
<section class="space-y-4">
    <div class="flex justify-between items-end">
        <h2 class="text-lg font-bold text-slate-800">Món Ăn Mới Nhất</h2>
        <div class="flex items-center gap-2 text-slate-400">
            <button class="p-1.5 bg-white border border-slate-200 rounded-md text-red-500 shadow-2xs">⣿</button>
            <button class="p-1.5 bg-white border border-slate-200 rounded-md hover:text-slate-600">☰</button>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#fff2e4]">
                    <img src="https://images.unsplash.com/photo-1501443762994-82bd5dace89a?w=300" alt="Kem" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 30k</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Ice Cream Trái Cây</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                    <span class="rounded-sm bg-green-50 px-1.5 py-0.5 text-[8px] font-bold text-green-700">CHAY</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="text-slate-400 line-through">100.000 đ</span>
                    <span class="font-semibold text-slate-800">70.000 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#f6e6d6]">
                    <img src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=300" alt="Cafe" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 5%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Special Cold Coffee</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="text-slate-400 line-through">60.000 đ</span>
                    <span class="font-semibold text-slate-800">57.000 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#fce8d9]">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300" alt="Burger" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 10%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Buddy Zinger Combo</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-red-600 flex items-center justify-center text-[6px] text-red-600 font-bold">●</span>
                    <span class="rounded-sm bg-red-50 px-1.5 py-0.5 text-[8px] font-bold text-red-700">MẶN</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="text-slate-400 line-through">120.000 đ</span>
                    <span class="font-semibold text-slate-800">108.000 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#ffe6e5]">
                    <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=300" alt="Chizza" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 14%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Chizza Meal Thượng Hạng</h4>
                <div class="mt-2 flex items-center justify-center gap-1 text-sm text-slate-700">
                    <span>3.0</span>
                    <span class="text-amber-400">★</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="text-slate-400 line-through">180.000 đ</span>
                    <span class="font-semibold text-slate-800">154.800 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#f3eadf]">
                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300" alt="Rice Bowl" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Popcorn Rice Bowl</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-red-600 flex items-center justify-center text-[6px] text-red-600 font-bold">●</span>
                    <span class="rounded-sm bg-green-100 px-1.5 py-0.5 text-[8px] font-bold text-green-700">HALAL</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="font-semibold text-slate-800">95.000 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#fff2e4]">
                    <img src="https://images.unsplash.com/photo-1501443762994-82bd5dace89a?w=300" alt="Kem" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 30k</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Ice Cream Trái Cây</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                    <span class="rounded-sm bg-green-50 px-1.5 py-0.5 text-[8px] font-bold text-green-700">CHAY</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="text-slate-400 line-through">100.000 đ</span>
                    <span class="font-semibold text-slate-800">70.000 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#f6e6d6]">
                    <img src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=300" alt="Cafe" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 5%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Special Cold Coffee</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="text-slate-400 line-through">60.000 đ</span>
                    <span class="font-semibold text-slate-800">57.000 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#fce8d9]">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300" alt="Burger" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 10%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Buddy Zinger Combo</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-red-600 flex items-center justify-center text-[6px] text-red-600 font-bold">●</span>
                    <span class="rounded-sm bg-red-50 px-1.5 py-0.5 text-[8px] font-bold text-red-700">MẶN</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="text-slate-400 line-through">120.000 đ</span>
                    <span class="font-semibold text-slate-800">108.000 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#ffe6e5]">
                    <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=300" alt="Chizza" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">GIẢM 14%</span>
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Chizza Meal Thượng Hạng</h4>
                <div class="mt-2 flex items-center justify-center gap-1 text-sm text-slate-700">
                    <span>3.0</span>
                    <span class="text-amber-400">★</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="text-slate-400 line-through">180.000 đ</span>
                    <span class="font-semibold text-slate-800">154.800 đ</span>
                </div>
            </div>
        </article>

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#f3eadf]">
                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300" alt="Rice Bowl" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>
                <button class="absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>
            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">Popcorn Rice Bowl</h4>
                <div class="mt-2 flex items-center justify-center gap-1.5">
                    <span class="w-3 h-3 border border-red-600 flex items-center justify-center text-[6px] text-red-600 font-bold">●</span>
                    <span class="rounded-sm bg-green-100 px-1.5 py-0.5 text-[8px] font-bold text-green-700">HALAL</span>
                </div>
                <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                    <span class="font-semibold text-slate-800">95.000 đ</span>
                </div>
            </div>
        </article>
    </div>
</section>
@endsection
