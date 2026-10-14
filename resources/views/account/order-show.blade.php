@extends('layouts.app')
@section('title', 'Theo dõi đơn — ZomZop')
@section('content')
<div class="max-w-3xl mx-auto space-y-5">
<a href="{{ route('orders') }}" class="text-red-600">← Danh sách đơn hàng</a>
<h1 class="text-2xl font-bold">Đơn {{ $order->order_code }}</h1>
@include('account.order-messages')
<section class="bg-white rounded-2xl border p-5 space-y-3">
<h2 class="font-bold text-red-600 text-xl">{{ $labels[$order->status] ?? $order->status }}</h2>
<p>{{ $order->branch?->name }} · {{ $order->isDelivery() ? 'Giao hàng' : 'Mang đi' }}</p>
@if($order->isDelivery())<p>Địa chỉ: {{ $order->delivery_address }}</p><p>Tốc độ: {{ $order->delivery_speed === 'express' ? 'Hỏa tốc' : 'Tiêu chuẩn' }}</p>@else<p>Mã nhận món: <strong>{{ $order->pickup_code }}</strong></p>@endif
@if($order->estimated_time)<p>Dự kiến: {{ $order->estimated_time->format('d/m/Y H:i') }}</p>@endif
<p class="text-sm text-slate-500">Trạng thái được cập nhật bởi cửa hàng. Bấm làm mới để lấy thông tin mới nhất.</p>
<a class="inline-block text-red-600 font-semibold" href="{{ route('orders.show', $order) }}">Làm mới trạng thái</a>
<ol class="border-l-2 border-red-200 pl-4 space-y-4">
@forelse($order->histories as $history)
<li><strong>{{ $labels[$history->to_status] ?? $history->to_status }}</strong><p class="text-sm text-slate-500">{{ $history->created_at->format('d/m/Y H:i') }}</p></li>
@empty<li>Đơn được tạo lúc {{ $order->created_at->format('d/m/Y H:i') }}. Chưa có lịch sử cập nhật.</li>@endforelse
</ol>
</section>
<section class="bg-white rounded-2xl border p-5 space-y-4">
@include('account.order-items')
<div class="border-t pt-4 space-y-2">
<p>Tạm tính: {{ number_format($order->subtotal, 0, ',', '.') }}đ</p>
<p>Phí giao: {{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }}đ</p>
<p>Giảm giá: -{{ number_format($order->discount, 0, ',', '.') }}đ</p>
<p class="font-bold text-xl text-red-600">Tổng: {{ number_format($order->total, 0, ',', '.') }}đ</p>
<p>Thanh toán: {{ $order->isPaid() ? 'Đã thanh toán' : 'Chưa thanh toán' }}</p>
@if($order->note)<p>Ghi chú: {{ $order->note }}</p>@endif
</div>
</section>
<div class="flex gap-4"><a class="text-red-600 font-bold" href="{{ route('orders.reorder.preview', $order) }}">Đặt lại</a>@if($order->isCompleted())<a class="text-red-600 font-bold" href="{{ route('orders.review', $order) }}">{{ $order->review ? 'Xem đánh giá' : 'Đánh giá đơn hàng' }}</a>@endif</div>
</div>
@endsection
