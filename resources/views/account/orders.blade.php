@extends('layouts.app')
@section('title', 'Đơn hàng — ZomZop')
@section('content')
<div class="max-w-5xl mx-auto space-y-5">
    <x-back-to-account />
    <h1 class="text-3xl font-bold text-slate-800">Đơn hàng của tôi</h1>
    <p class="text-slate-500">Theo dõi bữa ăn, đặt lại món yêu thích và chia sẻ đánh giá.</p>
    @include('account.order-messages')
    <nav class="flex gap-2 overflow-x-auto pb-2">
        <a class="px-4 py-2 rounded-full border {{ !request('status') ? 'bg-red-500 text-white' : 'bg-white' }}" href="{{ route('orders') }}">Tất cả</a>
        @foreach($labels as $key => $label)
        <a class="whitespace-nowrap px-4 py-2 rounded-full border {{ request('status') === $key ? 'bg-red-500 text-white' : 'bg-white' }}" href="{{ route('orders', ['status' => $key]) }}">{{ $label }}</a>
        @endforeach
    </nav>
    @forelse($orders as $order)
    <article class="bg-white border border-slate-100 rounded-2xl p-5 space-y-4">
        <div class="flex flex-wrap justify-between gap-2">
            <div><strong>{{ $order->order_code }}</strong><p class="text-sm text-slate-500">{{ $order->branch?->name }} · {{ $order->created_at->format('d/m/Y H:i') }}</p></div>
            <span class="text-red-600 font-semibold">{{ $labels[$order->status] ?? $order->status }}</span>
        </div>
        @include('account.order-items')
        <div class="flex flex-wrap justify-between items-center gap-3 border-t pt-4">
            <strong>Tổng: {{ number_format($order->total, 0, ',', '.') }}đ</strong>
            <div class="flex flex-wrap gap-3">
                <a class="text-red-600 font-semibold" href="{{ route('orders.show', $order) }}">Theo dõi đơn</a>
                <a class="text-slate-700 font-semibold" href="{{ route('orders.reorder.preview', $order) }}">Đặt lại</a>
                @if($order->isCompleted())<a class="text-red-600 font-semibold" href="{{ route('orders.review', $order) }}">{{ $order->review ? 'Xem đánh giá' : 'Đánh giá' }}</a>@endif
            </div>
        </div>
    </article>
    @empty
    <div class="bg-white rounded-2xl p-10 text-center"><p>Bạn chưa có đơn hàng trong mục này.</p><a class="inline-block mt-4 text-red-600 font-bold" href="{{ route('home') }}">Khám phá món ăn</a></div>
    @endforelse
    {{ $orders->links() }}
</div>
@endsection
