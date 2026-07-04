{{-- resources/views/favorites/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Yêu Thích — ZomZop')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Món Yêu Thích ❤️</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $items->count() }} món đã lưu</p>
        </div>
        @if ($items->isNotEmpty())
        <a href="{{ route('home') }}"
            class="text-xs font-semibold text-red-500 hover:underline">
            ← Tiếp tục mua sắm
        </a>
        @endif
    </div>

    {{-- Danh sách --}}
    @if ($items->isEmpty())

    {{-- Empty state --}}
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="text-7xl mb-4">🤍</div>
        <h2 class="text-xl font-bold text-slate-700 mb-2">Chưa có món yêu thích</h2>
        <p class="text-slate-400 text-sm mb-6">Bấm vào icon ❤️ trên các thẻ sản phẩm để lưu món yêu thích</p>
        <a href="{{ route('home') }}"
            class="bg-red-500 hover:bg-red-600 text-white font-semibold px-8 py-3 rounded-full transition">
            Khám Phá Ngay
        </a>
    </div>

    @else

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach ($items as $item)
        <x-menu-item-card :item="$item" />
        @endforeach
    </div>

    @endif

</div>

@endsection