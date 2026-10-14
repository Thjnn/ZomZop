@extends('layouts.app')
@section('title', 'Đánh giá — ZomZop')
@section('content')
<div class="max-w-2xl mx-auto space-y-5">
<a class="text-red-600" href="{{ route('orders.show', $order) }}">← Chi tiết đơn</a>
<h1 class="text-2xl font-bold">Đánh giá đơn {{ $order->order_code }}</h1>
@include('account.order-messages')
@if($order->review)
<div class="bg-white rounded-2xl border p-6 space-y-3"><p class="text-xl text-amber-600">{{ $order->review->rating }}/5 sao</p><p>{{ $order->review->comment }}</p>
@if($order->review->delivery_rating)<p>Giao hàng: {{ $order->review->delivery_rating }}/5 sao</p>@endif
@if($order->review->reply)<div class="bg-slate-50 p-4 rounded-xl"><strong>Cửa hàng phản hồi</strong><p>{{ $order->review->reply }}</p></div>@endif</div>
@else
<form method="POST" action="{{ route('orders.review.store', $order) }}" class="bg-white rounded-2xl border p-6 space-y-5">@csrf
<fieldset><legend class="font-bold mb-3">Bạn thấy món ăn thế nào?</legend><div class="flex flex-wrap gap-4">@foreach(range(1,5) as $star)<label><input type="radio" name="rating" value="{{ $star }}" @checked((int) old('rating') === $star) required> {{ $star }} ★</label>@endforeach</div></fieldset>
@if($order->isDelivery())<label class="block">Đánh giá giao hàng (không bắt buộc)<select name="delivery_rating" class="block border rounded-xl p-2 mt-2"><option value="">Chọn số sao</option>@foreach(range(1,5) as $star)<option value="{{ $star }}" @selected((int) old('delivery_rating') === $star)>{{ $star }} sao</option>@endforeach</select></label>@endif
<label class="block">Nhận xét<textarea name="comment" maxlength="2000" rows="4" class="block w-full border rounded-xl p-3 mt-2" placeholder="Chia sẻ trải nghiệm của bạn...">{{ old('comment') }}</textarea></label>
<p class="text-sm text-slate-500">Mỗi đơn được gửi đánh giá một lần.</p><button class="px-6 py-3 bg-red-500 text-white rounded-full font-bold">Gửi đánh giá</button>
</form>@endif
</div>
@endsection
