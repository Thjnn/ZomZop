@extends('layouts.app')

@section('title', 'Bán chạy - ZomZop')

@section('content')

<section class="rounded-2xl bg-linear-to-r from-red-600 to-orange-500 p-6 shadow-sm">
    <h1 class="text-white text-3xl font-bold tracking-wide">🔥 Bán Chạy</h1>
    <p class="text-white/80 text-sm mt-1">
        {{ $items->count() }} món
        @unless (session('selected_branch_id')) · đang xem toàn chuỗi, <a href="{{ route('branches.select') }}" class="underline">chọn chi nhánh</a> để xem món bán chạy gần bạn @endunless
    </p>
</section>

<div class="border-b border-slate-200 overflow-x-auto">
    <div class="flex gap-6 text-sm font-medium min-w-max">
        <span class="text-red-500 border-b-2 border-red-500 pb-2 px-1">Bán Chạy</span>
        @foreach ($allCategories as $cat)
        <a href="{{ route('category.show', $cat->slug) }}" class="text-slate-400 hover:text-slate-600 pb-2 px-1 transition">{{ $cat->name }}</a>
        @endforeach
    </div>
</div>

<section class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 pt-4">
    @forelse ($items as $item)
    <x-menu-item-card :item="$item" />
    @empty
    <div class="col-span-5 text-center py-16 text-slate-400">
        <p class="text-4xl mb-3">🔥</p>
        <p class="font-medium">Chi nhánh chưa chọn món bán chạy nào.</p>
    </div>
    @endforelse
</section>

@endsection
