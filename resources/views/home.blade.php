@extends('layouts.app')

@section('title', 'ZomZop - Trang Chủ')

@section('content')

{{-- ===================== BANNER + CATEGORIES ===================== --}}
<section class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:items-stretch">

    {{-- Banner Swiper --}}
    <div class="lg:col-span-2 relative w-full overflow-hidden rounded-2xl shadow-xl h-[220px] sm:h-[280px] lg:h-full lg:min-h-[320px]">
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

    {{-- Categories từ DB --}}
    <div class="bg-slate-100/60 rounded-2xl p-6 border border-slate-200/50">
        <h3 class="font-bold text-slate-700 mb-4 text-center">Khám Phá Thực Đơn</h3>
        <div class="grid grid-cols-3 gap-3">

            {{-- Nút "Bán Chạy" cố định --}}
            <a href="{{ route('home', ['sort' => 'popular']) }}"
                class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer no-underline">
                <span class="text-2xl mb-1">🔥</span>
                <span class="text-xs font-medium text-slate-600">Bán Chạy</span>
            </a>

            @foreach ($categories as $category)
            <a href="{{ route('category.show', $category->slug) }}"
                class="bg-white p-3 rounded-xl flex flex-col items-center justify-center text-center shadow-xs border border-slate-100 hover:scale-105 transition cursor-pointer no-underline">
                @if ($category->icon)
                <img src="{{ asset('images/categories/' . $category->icon) }}" alt="{{ $category->name }}" class="w-10 h-auto mb-1">
                @else
                <span class="text-2xl mb-1">🍽️</span>
                @endif
                <span class="text-xs font-medium text-slate-700">{{ $category->name }}</span>
            </a>
            @endforeach

        </div>
    </div>
</section>

{{-- ===================== MÓN NGON YÊU THÍCH ===================== --}}
<section class="space-y-4">
    <div class="flex justify-between items-end">
        <h2 class="text-xl font-bold text-slate-800">Món Ngon Được Yêu Thích</h2>
        <a href="{{ route('home', ['sort' => 'popular']) }}" class="text-xs font-semibold text-red-500 hover:underline">Xem Tất Cả</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @forelse ($popularItems as $item)
        @php
        $image = $item->images->first();
        $imgSrc = $image ? $image->image_url : $item->image_url;
        $hasDiscount = $item->discount_percent > 0;
        $discountedPrice = $hasDiscount
        ? $item->base_price * (1 - $item->discount_percent / 100)
        : null;
        @endphp

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#f6e6d6]">
                    <img src="{{ $imgSrc }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">

                    @if ($hasDiscount)
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">
                        GIẢM {{ $item->discount_percent }}%
                    </span>
                    @endif

                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500" aria-label="Thêm vào yêu thích">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>

                <button
                    data-id="{{ $item->id }}"
                    data-name="{{ $item->name }}"
                    data-price="{{ $discountedPrice ?? $item->base_price }}"
                    class="btn-add-cart absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>

            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">{{ $item->name }}</h4>

                @if (isset($item->reviews_avg_rating) && $item->reviews_avg_rating)
                <div class="mt-1 flex items-center justify-center gap-1 text-sm text-slate-700">
                    <span>{{ number_format($item->reviews_avg_rating, 1) }}</span>
                    <span class="text-amber-400">★</span>
                </div>
                @endif

                <div class="mt-2 flex items-center justify-center gap-2 text-sm">
                    @if ($hasDiscount)
                    <span class="text-slate-400 line-through">{{ number_format($item->base_price, 0, ',', '.') }} đ</span>
                    <span class="font-semibold text-slate-800">{{ number_format($discountedPrice, 0, ',', '.') }} đ</span>
                    @else
                    <span class="font-semibold text-slate-800">{{ number_format($item->base_price, 0, ',', '.') }} đ</span>
                    @endif
                </div>
            </div>
        </article>
        @empty
        <p class="col-span-5 text-center text-slate-400 py-8">Chưa có món ăn nào.</p>
        @endforelse
    </div>
</section>

{{-- ===================== COMBO NGON MÊ LY ===================== --}}
@if ($comboItems->isNotEmpty())
<section class="mt-10">
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-2xl font-extrabold text-slate-900">Combo Ngon Mê Ly</h3>
        <a href="{{ route('home', ['category' => 'combo']) }}" class="text-red-500 font-semibold hover:underline">Xem Tất Cả</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($comboItems as $item)
        @php
        $image = $item->images->first();
        $imgSrc = $image ? $image->image_url : $item->image_url;
        $hasDiscount = $item->discount_percent > 0;
        $finalPrice = $hasDiscount
        ? $item->base_price * (1 - $item->discount_percent / 100)
        : $item->base_price;
        @endphp

        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-44 h-32 overflow-hidden rounded-2xl flex-shrink-0">
                <img src="{{ $imgSrc }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
            </div>
            <div class="flex-1">
                <h4 class="text-xl font-extrabold text-slate-900">{{ $item->name }}</h4>
                @if ($item->description)
                <p class="text-slate-500 mt-1 text-sm line-clamp-2">{{ $item->description }}</p>
                @endif
                <div class="mt-3 text-red-500 text-xl font-extrabold">
                    {{ number_format($finalPrice, 0, ',', '.') }} đ
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ===================== ƯU ĐÃI ĐẶC BIỆT ===================== --}}
@if ($specialOffers->isNotEmpty())
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
                @foreach ($specialOffers as $item)
                @php
                $image = $item->images->first();
                $imgSrc = $image ? $image->image_url : $item->image_url;
                $discountedPrice = $item->base_price * (1 - $item->discount_percent / 100);
                @endphp

                <article class="swiper-slide rounded-[1.7rem] bg-white shadow-[0_20px_45px_rgba(253,164,175,0.18)]">
                    <div class="relative px-6 pt-6">
                        <div class="relative h-72 overflow-hidden rounded-[1.7rem] bg-[#f6e6d6]">
                            <img src="{{ $imgSrc }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition duration-300 hover:scale-105">
                            <span class="absolute left-4 top-4 rounded-full bg-[#ff6a61] px-4 py-2 text-sm font-bold text-white">
                                Giảm {{ $item->discount_percent }}%
                            </span>
                            <button class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                                </svg>
                            </button>
                        </div>
                        <button
                            data-id="{{ $item->id }}"
                            data-name="{{ $item->name }}"
                            data-price="{{ $discountedPrice }}"
                            class="btn-add-cart absolute -bottom-5 left-12 z-10 inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-[1.05rem] font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                            Thêm
                        </button>
                    </div>
                    <div class="px-6 pb-8 pt-10 text-center">
                        <h3 class="text-[1.05rem] font-medium tracking-[0.01em] text-slate-800">{{ $item->name }}</h3>
                        @if (isset($item->reviews_avg_rating) && $item->reviews_avg_rating)
                        <div class="mt-2 flex items-center justify-center gap-1 text-sm text-slate-700">
                            <span>{{ number_format($item->reviews_avg_rating, 1) }}</span>
                            <span class="text-amber-400">★</span>
                        </div>
                        @endif
                        <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
                            <span class="text-slate-400 line-through">{{ number_format($item->base_price, 0, ',', '.') }} đ</span>
                            <span class="font-semibold text-slate-800">{{ number_format($discountedPrice, 0, ',', '.') }} đ</span>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ===================== CHI NHÁNH ===================== --}}
<section class="space-y-4">
    <div class="flex justify-between items-end">
        <h2 class="text-lg font-bold text-slate-800">Tìm món ăn từ các Chi nhánh</h2>
        <a href="{{ route('home') }}" class="text-xs font-semibold text-red-500 hover:underline">Xem Tất Cả</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($branches as $branch)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden flex flex-col hover:shadow-md transition">
            <div class="h-32 bg-cover bg-center bg-slate-200"
                @if ($branch->image) style="background-image: url('{{ asset('images/branches/' . $branch->image) }}')" @endif>
            </div>
            <div class="p-4 flex items-center gap-3">
                <img src="{{ asset('images/branches/logo.png') }}"
                    class="w-12 h-12 rounded-xl object-cover border border-slate-100 shadow-2xs" alt="{{ $branch->name }}">
                <div>
                    <h4 class="font-bold text-sm text-slate-800">{{ $branch->name }}</h4>
                    <p class="text-xs text-slate-400 mt-0.5">📍 {{ $branch->address }}</p>
                </div>
            </div>
        </div>
        @empty
        <p class="col-span-3 text-center text-slate-400 py-8">Chưa có chi nhánh nào.</p>
        @endforelse
    </div>
</section>

{{-- ===================== MÓN MỚI NHẤT ===================== --}}
<section class="space-y-4">
    <div class="flex justify-between items-end">
        <h2 class="text-lg font-bold text-slate-800">Món Ăn Mới Nhất</h2>
        <a href="{{ route('home', ['sort' => 'newest']) }}" class="text-xs font-semibold text-red-500 hover:underline">Xem Tất Cả</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
        @forelse ($newArrivals as $item)
        @php
        $image = $item->images->first();
        $imgSrc = $image ? $image->image_url : $item->image_url;
        $hasDiscount = $item->discount_percent > 0;
        $discountedPrice = $hasDiscount
        ? $item->base_price * (1 - $item->discount_percent / 100)
        : null;
        @endphp

        <article class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
            <div class="relative px-4 pt-4">
                <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#fff2e4]">
                    <img src="{{ $imgSrc }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">

                    @if ($hasDiscount)
                    <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">
                        GIẢM {{ $item->discount_percent }}%
                    </span>
                    @endif

                    <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                        </svg>
                    </button>
                </div>

                <button
                    data-id="{{ $item->id }}"
                    data-name="{{ $item->name }}"
                    data-price="{{ $discountedPrice ?? $item->base_price }}"
                    class="btn-add-cart absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
                    Thêm
                </button>
            </div>

            <div class="px-4 pb-5 pt-7 text-center">
                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800">{{ $item->name }}</h4>

                @if (isset($item->reviews_avg_rating) && $item->reviews_avg_rating)
                <div class="mt-1 flex items-center justify-center gap-1 text-sm text-slate-700">
                    <span>{{ number_format($item->reviews_avg_rating, 1) }}</span>
                    <span class="text-amber-400">★</span>
                </div>
                @endif

                <div class="mt-2 flex items-center justify-center gap-2 text-sm">
                    @if ($hasDiscount)
                    <span class="text-slate-400 line-through">{{ number_format($item->base_price, 0, ',', '.') }} đ</span>
                    <span class="font-semibold text-slate-800">{{ number_format($discountedPrice, 0, ',', '.') }} đ</span>
                    @else
                    <span class="font-semibold text-slate-800">{{ number_format($item->base_price, 0, ',', '.') }} đ</span>
                    @endif
                </div>
            </div>
        </article>
        @empty
        <p class="col-span-5 text-center text-slate-400 py-8">Chưa có món mới.</p>
        @endforelse
    </div>
</section>

@endsection