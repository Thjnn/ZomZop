@extends('layouts.app')
@section('title', 'Ưu đãi dành cho bạn - ZomZop')
@section('content')
@php
    $usageCounts = auth()->check() ? \App\Models\CouponUsage::where('user_id',auth()->id())->whereIn('coupon_id',$coupons->pluck('id'))->selectRaw('coupon_id, COUNT(*) as total')->groupBy('coupon_id')->pluck('total','coupon_id') : collect();
@endphp
<div class="ac-page" data-coupon-browser>
<a class="ac-back" href="{{ url('/menu') }}">← Tài khoản</a>
<header class="ac-hero"><div><p class="ac-eyebrow">THÊM ƯU ĐÃI, THÊM NIỀM VUI</p><h1>Bữa ngon, giá dễ thương.</h1><p>Chọn mã phù hợp cho món yêu thích và cách nhận hàng của bạn.</p></div><span class="ac-hero-ticket" aria-hidden="true">ZOMZOP<br><b>ưu đãi mỗi bữa</b></span></header>
<div class="ac-panel ac-coupon-controls"><div class="ac-search-block"><label for="ac-coupon-search">Tìm ưu đãi</label><input id="ac-coupon-search" type="search" placeholder="Nhập mã hoặc nội dung ưu đãi…" data-coupon-search></div><div class="ac-search-block"><label for="ac-coupon-scope">Áp dụng cho</label><select id="ac-coupon-scope" data-coupon-scope><option value="all">Tất cả ưu đãi</option value="food">Tiền món</option><option value="ship">Phí giao tiêu chuẩn</option><option value="express">Phí giao hỏa tốc</option></select></div><div class="ac-search-block"><label for="ac-coupon-type">Hình thức giảm</label><select id="ac-coupon-type" data-coupon-type><option value="all">Tất cả hình thức</option><option value="percent">Giảm theo %</option><option value="fixed">Giảm số tiền</option></select></div></div>
<div class="ac-results"><p><strong data-coupon-count>{{ $coupons->count() }}</strong> ưu đãi đang được hiển thị</p><p>Mã được kiểm tra lại tại bước thanh toán.</p></div>
<div class="ac-voucher-grid">
@foreach($coupons as $c)
@php $used = (int)($usageCounts[$c->id] ?? 0); $remaining = max(0,(int)$c->max_uses_per_user-$used); $scope = $c->scope ?? 'food'; @endphp
<article class="ac-voucher {{ auth()->check() && !$remaining ? 'ac-voucher-used' : '' }}" data-coupon-card data-scope="{{ $scope }}" data-type="{{ $c->type }}" data-search="{{ $c->code . ' ' . $c->summary . ' ' . $c->scope_label }}">
<div class="ac-voucher-stamp"><span>{{ $scope === 'food' ? 'BỮA NGON' : 'GIAO HÀNG' }}</span><strong>{{ $c->badge }}</strong><small>{{ $c->scope_label }}</small></div>
<div class="ac-voucher-main"><div class="ac-voucher-top"><code>{{ $c->code }}</code><span class="ac-badge">{{ $c->type === 'percent' ? 'Giảm %' : 'Giảm tiền' }}</span></div><h2>{{ $c->summary }}</h2><p>Đơn từ <strong>{{ number_format($c->min_order_value,0,',','.') }}đ</strong>@if($c->type === 'percent' && $c->max_discount) · Tối đa {{ number_format($c->max_discount,0,',','.') }}đ@endif</p><p class="ac-voucher-expiry">{{ $c->expired_at ? 'Đến ' . $c->expired_at->format('d/m/Y H:i') : 'Chưa giới hạn ngày kết thúc' }}</p>
<details class="ac-coupon-details"><summary>Điều kiện sử dụng</summary><ul><li>Phạm vi: {{ $c->scope_label }}.</li><li>Tạm tính món tối thiểu {{ number_format($c->min_order_value,0,',','.') }}đ.</li><li>Tối đa {{ $c->max_uses_per_user }} lượt mỗi khách.@auth Còn {{ $remaining }} lượt theo dữ liệu hiện tại.@endauth</li>@if($scope === 'ship')<li>Chỉ dùng khi giao hàng tiêu chuẩn.</li>@elseif($scope === 'express')<li>Chỉ dùng khi giao hàng hỏa tốc.</li>@endif<li>Mỗi đơn áp dụng một mã; ưu đãi được kiểm tra lại khi xác nhận đặt hàng.</li>@if($c->started_at)<li>Bắt đầu từ {{ $c->started_at->format('d/m/Y H:i') }}.</li>@endif</ul></details>
<div class="ac-voucher-bottom"><span>{{ auth()->check() ? ($remaining ? 'Còn ' . $remaining . ' lượt của bạn' : 'Bạn đã dùng hết lượt') : 'Đăng nhập để kiểm tra lượt của bạn' }}</span><button class="ac-button ac-outline" type="button" data-copy-code="{{ $c->code }}" @disabled(auth()->check() && !$remaining)>Sao chép mã</button></div></div>
</article>
@endforeach
</div>
<div class="ac-empty" data-coupon-empty @if($coupons->isNotEmpty()) hidden @endif><span aria-hidden="true">◇</span><h2>Chưa tìm thấy ưu đãi phù hợp</h2><p>Thử mã khác hoặc bỏ bớt điều kiện lọc.</p><button class="ac-button ac-outline" type="button" data-coupon-reset>Xóa bộ lọc</button></div>
<div class="ac-banner"><div><strong>Đã chọn được mã yêu thích?</strong><p>Sao chép mã rồi nhập hoặc chọn trong kho mã ở bước thanh toán.</p></div><a class="ac-button ac-primary" href="{{ route('home') }}">Chọn món ngay →</a></div>
<p class="ac-footnote" data-copy-feedback role="status" aria-live="polite"></p>
</div>
@endsection
