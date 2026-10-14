@extends('layouts.app')
@section('title', 'Đặt lại — ZomZop')
@section('content')
<div class="max-w-2xl mx-auto space-y-5">
<a class="text-red-600" href="{{ route('orders.show', $order) }}">← Chi tiết đơn</a>
<h1 class="text-2xl font-bold">Đặt lại đơn {{ $order->order_code }}</h1>
@include('account.order-messages')
<p>Chi nhánh: {{ $order->branch?->name }}. Giá và tình trạng món được kiểm tra lại khi bạn xác nhận.</p>
@foreach($result['changes'] as $change)<p class="text-amber-700">{{ $change }}</p>@endforeach
<div class="bg-white rounded-2xl border p-5 space-y-4">
@forelse($result['cart']['items'] as $item)
<div class="flex items-center gap-3"><img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 rounded-xl object-cover"><div class="flex-1">{{ $item['name'] }} × {{ $item['quantity'] }}</div><strong>{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ</strong></div>
@empty<p>Tất cả món trong đơn đã ngừng bán.</p>@endforelse
</div>
@if(count($result['cart']['items']))
<form method="POST" action="{{ route('orders.reorder', $order) }}" class="space-y-4">@csrf
<p>Thao tác này thay giỏ hàng hiện tại bằng các món trên. Mã giảm giá cũ không tự áp dụng. Chưa tạo đơn hoặc thu tiền ở bước này.</p>
<label class="block"><input type="checkbox" name="replace_cart" value="1" required> Tôi đồng ý thay giỏ hàng hiện tại.</label>
<button class="px-6 py-3 rounded-full bg-red-500 text-white font-bold">Tiếp tục đến thanh toán</button>
</form>@endif
</div>
@endsection
