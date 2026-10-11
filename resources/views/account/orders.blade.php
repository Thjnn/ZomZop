@extends('layouts.app')

@section('title', 'Đơn hàng của tôi - ZomZop')

@section('content')
@php
    $labels = \App\Services\OrderStatusService::LABELS;
    $badge = [
        'pending'   => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'cooking'   => 'bg-orange-100 text-orange-700',
        'ready'     => 'bg-purple-100 text-purple-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-slate-200 text-slate-600',
    ];
    $money = fn ($n) => number_format((int) $n, 0, ',', '.') . ' đ';
@endphp

<div class="max-w-4xl mx-auto py-8 px-4">
    <x-back-to-account />
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Đơn hàng của tôi</h1>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Tab trạng thái --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-4">
        <a href="{{ route('orders') }}"
           class="whitespace-nowrap px-4 py-2 rounded-full text-sm border {{ $status === 'all' ? 'bg-red-500 text-white border-red-500' : 'bg-white text-slate-600 border-slate-200 hover:border-red-300' }}">
            Tất cả <span class="opacity-70">({{ $counts->sum() }})</span>
        </a>
        @foreach ($labels as $key => $text)
            <a href="{{ route('orders', ['status' => $key]) }}"
               class="whitespace-nowrap px-4 py-2 rounded-full text-sm border {{ $status === $key ? 'bg-red-500 text-white border-red-500' : 'bg-white text-slate-600 border-slate-200 hover:border-red-300' }}">
                {{ $text }} <span class="opacity-70">({{ $counts[$key] ?? 0 }})</span>
            </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($orders as $order)
            @php $isActive = !in_array($order->status, ['completed', 'cancelled'], true); @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('orders.show', $order->order_code) }}" class="font-semibold text-slate-800 hover:text-red-500">#{{ $order->order_code }}</a>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                            · {{ $order->isDelivery() ? ($order->delivery_speed === 'express' ? 'Giao hỏa tốc' : 'Giao hàng') : 'Mang đi' }}
                            · {{ $order->branch->name ?? '' }}
                        </p>
                    </div>
                    <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold {{ $badge[$order->status] ?? 'bg-slate-100' }}">
                        {{ $labels[$order->status] ?? $order->status }}
                    </span>
                </div>

                {{-- Các món (kèm hình) --}}
                <div class="mt-3 divide-y divide-slate-50 border-y border-slate-50">
                    @foreach ($order->items->take(3) as $line)
                        <div class="flex items-center gap-3 py-2">
                            @if ($line->menuItem)
                                <img src="{{ $line->menuItem->image_url }}" alt="{{ $line->name_snapshot }}"
                                     class="w-12 h-12 rounded-xl object-cover bg-red-50 shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-xl shrink-0">🍔</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">{{ $line->name_snapshot }}</p>
                                <p class="text-xs text-slate-400">x{{ $line->quantity }}</p>
                            </div>
                            <span class="text-sm text-slate-600">{{ $money($line->subtotal) }}</span>
                        </div>
                    @endforeach
                    @if ($order->items->count() > 3)
                        <p class="py-2 text-xs text-slate-400">và {{ $order->items->count() - 3 }} món khác…</p>
                    @endif
                </div>

                {{-- Tổng + nút thao tác --}}
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">Tổng: <b class="text-red-500 text-base">{{ $money($order->total) }}</b></p>

                    <div class="flex flex-wrap gap-2 text-sm">
                        <a href="{{ route('orders.show', $order->order_code) }}"
                           class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50">Chi tiết</a>

                        @if ($isActive)
                            <a href="{{ route('orders.show', $order->order_code) }}#tracking"
                               class="px-4 py-2 rounded-xl bg-purple-500 text-white hover:bg-purple-600">Theo dõi đơn</a>
                        @endif

                        @if (in_array($order->status, ['pending', 'confirmed'], true))
                            <form method="POST" action="{{ route('orders.cancel', $order->order_code) }}"
                                  onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn {{ $order->order_code }}?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl border border-red-200 text-red-500 hover:bg-red-50">Hủy đơn</button>
                            </form>
                        @endif

                        @if (!$isActive)
                            <form method="POST" action="{{ route('orders.reorder', $order->order_code) }}"
                                  onsubmit="return confirm('Giỏ hàng hiện tại sẽ được thay bằng các món của đơn này. Tiếp tục?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600">Đặt lại</button>
                            </form>
                        @endif

                        @if ($order->isCompleted())
                            @if ($order->review)
                                <a href="{{ route('orders.show', $order->order_code) }}#review"
                                   class="px-4 py-2 rounded-xl border border-yellow-300 text-yellow-600 bg-yellow-50">★ Đã đánh giá ({{ $order->review->rating }}/5)</a>
                            @else
                                <a href="{{ route('orders.review', $order->order_code) }}"
                                   class="px-4 py-2 rounded-xl border border-yellow-300 text-yellow-600 hover:bg-yellow-50">★ Đánh giá</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400">
                <p class="text-4xl mb-2">🧾</p>
                <p>Chưa có đơn hàng nào{{ $status !== 'all' ? ' ở trạng thái này' : '' }}.</p>
                <a href="{{ route('home') }}" class="inline-block mt-4 px-5 py-2 rounded-full bg-red-500 text-white text-sm hover:bg-red-600">Đặt món ngay</a>
            </div>
        @endforelse
    </div>

    @if ($orders->hasPages())
        <div class="flex items-center justify-between mt-6 text-sm">
            @if ($orders->previousPageUrl())
                <a href="{{ $orders->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:border-red-300">← Trang trước</a>
            @else <span></span> @endif
            <span class="text-slate-400">Trang {{ $orders->currentPage() }}/{{ $orders->lastPage() }}</span>
            @if ($orders->nextPageUrl())
                <a href="{{ $orders->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:border-red-300">Trang sau →</a>
            @else <span></span> @endif
        </div>
    @endif
</div>
@endsection
