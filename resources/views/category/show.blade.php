{{-- resources/views/category/show.blade.php --}}

@extends('layouts.app')

@section('title', $category->name . ' - ZomZop')

@section('content')

{{-- ===== BANNER CATEGORY ===== --}}
<section class="relative h-48 rounded-2xl overflow-hidden shadow-sm bg-amber-900">

    {{-- Ảnh nền: banner theo category --}}
    <div class="absolute inset-0">
        @php
        $bannerJpg = public_path('images/categories/banner-' . $category->slug . '.jpg');
        $bannerPng = public_path('images/categories/banner-' . $category->slug . '.png');

        if (file_exists($bannerJpg)) {
        $bannerSrc = asset('images/categories/banner-' . $category->slug . '.jpg');
        } elseif (file_exists($bannerPng)) {
        $bannerSrc = asset('images/categories/banner-' . $category->slug . '.png');
        } else {
        $bannerSrc = asset('images/categories/banner-default.jpg');
        }
        @endphp
        <img src="{{ $bannerSrc }}"
            alt="{{ $category->name }}"
            class="w-full h-full object-cover">
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


{{-- ===== DANH SÁCH SẢN PHẨM ===== --}}
<section class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 pt-4">
    @forelse ($items as $item)
    <x-menu-item-card :item="$item" />
    @empty
    <div class="col-span-5 text-center py-16 text-slate-400">
        <p class="text-4xl mb-3">🍽️</p>
        <p class="font-medium">Chưa có món ăn nào trong danh mục này.</p>
    </div>
    @endforelse
</section>

@endsection