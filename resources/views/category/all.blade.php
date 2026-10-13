@extends('layouts.app')

@section('title', 'Thực đơn - ZomZop')

@section('content')

<section class="rounded-2xl bg-linear-to-r from-red-600 to-orange-500 p-6 shadow-sm">
    <h1 class="text-white text-3xl font-bold tracking-wide">🍽️ Thực Đơn</h1>
    <p class="text-white/80 text-sm mt-1">
        {{ $items->count() }} món
        @unless (session('selected_branch_id')) · đang xem toàn chuỗi, <a href="{{ route('branches.select') }}" class="underline">chọn chi nhánh</a> để xem đúng món và giá gần bạn @endunless
    </p>
</section>

<div class="border-b border-slate-200 overflow-x-auto">
    <div class="flex gap-6 text-sm font-medium min-w-max">
        <span class="text-red-500 border-b-2 border-red-500 pb-2 px-1">Tất cả</span>
        <a href="{{ route('bestsellers') }}" class="text-slate-400 hover:text-slate-600 pb-2 px-1 transition">🔥 Bán Chạy</a>
        @foreach ($allCategories as $cat)
        <a href="{{ route('category.show', $cat->slug) }}" class="text-slate-400 hover:text-slate-600 pb-2 px-1 transition">{{ $cat->name }}</a>
        @endforeach
    </div>
</div>

<nav aria-label="Sắp xếp" class="flex flex-wrap gap-2 text-sm">
    @foreach ($sorts as $value => $label)
    <a href="{{ route('menu.all', ['sort' => $value]) }}" @if ($sort === $value) aria-current="true" @endif
        class="px-4 py-1.5 rounded-full border transition {{ $sort === $value ? 'bg-red-500 border-red-500 text-white' : 'border-slate-200 text-slate-600 hover:border-red-300' }}">{{ $label }}</a>
    @endforeach
</nav>

<section class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
    @forelse ($items as $item)
    <x-menu-item-card :item="$item" />
    @empty
    <div class="col-span-5 text-center py-16 text-slate-400">
        <p class="text-4xl mb-3">🍽️</p>
        <p class="font-medium">Chưa có món nào đang bán.</p>
    </div>
    @endforelse
</section>

@endsection
