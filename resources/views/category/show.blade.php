{{-- resources/views/category/show.blade.php --}}

@extends('layouts.app')

@section('title', $category->name . ' - ZomZop')

@section('content')

{{-- ===== BANNER CATEGORY ===== --}}
<section class="relative h-48 rounded-2xl overflow-hidden shadow-sm bg-amber-900">

    {{-- Ảnh nền: lấy ảnh từ các món trong category --}}
    <div class="absolute inset-0 grid grid-cols-5 gap-1 opacity-80">
        @forelse ($items->take(5) as $item)
        <div class="bg-cover bg-center" style="background-image: url('{{ $item->image_url }}')"></div>
        @empty
        <div class="col-span-5 bg-amber-800"></div>
        @endforelse
    </div>

    <div class="absolute inset-0 bg-linear-to-r from-black/70 via-black/30 to-black/50 flex items-end p-6">
        <div>
            <h1 class="text-white text-3xl font-bold tracking-wide">{{ $category->name }}</h1>
            <p class="text-white/70 text-sm mt-1">{{ $items->count() }} món</p>
        </div>
    </div>
</section>

{{-- ===== TAB DANH MỤC ===== --}}
<div class="border-b border-slate-200 overflow-x-auto">
    <div class="flex gap-6 text-sm font-medium min-w-max">
        @foreach ($allCategories as $cat)
        <a href="{{ route('category.show', $cat->slug) }}"
            class="{{ $cat->id === $category->id ? 'text-red-500 border-b-2 border-red-500' : 'text-slate-400 hover:text-slate-600' }} pb-2 px-1 transition">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>
</div>

{{-- ===== FILTER MẶN / CHAY ===== --}}
<div class="flex justify-center">
    <div class="inline-flex bg-white border border-slate-200 rounded-lg p-1 gap-1 text-xs font-semibold shadow-xs">
        <a href="{{ route('category.show', $category->slug) }}"
            class="{{ !request('type') ? 'bg-red-500 text-white' : 'text-slate-600 hover:bg-slate-50' }} px-4 py-1.5 rounded-md cursor-pointer">
            Tất cả
        </a>
        <a href="{{ route('category.show', $category->slug, ['type' => 'man']) }}"
            class="{{ request('type') === 'man' ? 'bg-red-500 text-white' : 'text-slate-600 hover:bg-slate-50' }} px-4 py-1.5 rounded-md flex items-center gap-1 cursor-pointer">
            🍗 Món Mặn
        </a>
        <a href="{{ route('category.show', $category->slug, ['type' => 'chay']) }}"
            class="{{ request('type') === 'chay' ? 'bg-red-500 text-white' : 'text-slate-600 hover:bg-slate-50' }} px-4 py-1.5 rounded-md flex items-center gap-1 cursor-pointer">
            🌿 Món Chay
        </a>
    </div>
</div>

{{-- ===== DANH SÁCH SẢN PHẨM ===== --}}
<section class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 pt-4">

    @forelse ($items as $item)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between">

        <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">

            @if ($item->is_on_sale)
            <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">
                GIẢM {{ $item->discount_percent }}%
            </span>
            @endif

            <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">
                ❤️
            </button>

            <button
                data-id="{{ $item->id }}"
                data-name="{{ $item->name }}"
                data-price="{{ $item->discounted_price }}"
                class="btn-add-cart absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">
                ➕ Thêm
            </button>
        </div>

        <div class="p-3 space-y-2">
            <h4 class="font-bold text-sm text-slate-700 line-clamp-2">{{ $item->name }}</h4>

            {{-- Rating nếu có --}}
            @if ($item->reviews_avg_rating ?? false)
            <div class="flex items-center gap-1 text-xs text-amber-500 font-semibold">
                <span>{{ number_format($item->reviews_avg_rating, 1) }} ★</span>
            </div>
            @endif

            {{-- Giá --}}
            <div class="flex items-baseline gap-2 pt-1">
                @if ($item->is_on_sale)
                <span class="text-[11px] text-slate-400 line-through">{{ $item->display_base_price }}</span>
                <span class="text-sm font-bold text-red-500">{{ $item->display_price }}</span>
                @else
                <span class="text-sm font-bold text-red-500">{{ $item->display_price }}</span>
                @endif
            </div>
        </div>

    </div>
    @empty
    <div class="col-span-5 text-center py-16 text-slate-400">
        <p class="text-4xl mb-3">🍽️</p>
        <p class="font-medium">Chưa có món ăn nào trong danh mục này.</p>
    </div>
    @endforelse

</section>

@endsection