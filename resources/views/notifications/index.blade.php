@extends('layouts.app')
@section('title', 'Thông báo của bạn - ZomZop')
@section('content')
<div class="ac-page">
<a class="ac-back" href="{{ url('/menu') }}">← Tài khoản</a>
<header class="ac-heading"><div><p class="ac-eyebrow">LUÔN KẾT NỐI VỚI ZOMZOP</p><h1>Hộp thư của bạn</h1><p>Cập nhật đơn hàng và ưu đãi, gọn trong một nơi.</p></div><div class="ac-mini-stat"><strong>{{ $unread }}</strong><span>thông báo chưa đọc</span></div></header>
@if(session('success'))<div class="ac-alert ac-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-alert ac-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<section class="ac-panel">
<div class="ac-toolbar"><nav class="ac-tabs" aria-label="Trạng thái đọc">@foreach(['all'=>'Tất cả', 'unread'=>'Chưa đọc'] as $value=>$text)<a href="{{ route('notifications', ['view'=>$value,'kind'=>request('kind','all')]) }}" @if(request('view','all') === $value) aria-current="page" @endif>{{ $text }} <span>{{ $value === 'all' ? $total : $unread }}</span></a>@endforeach</nav><div class="ac-toolbar-actions"><form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="ac-text-button" @disabled($unread === 0)>Đánh dấu tất cả đã đọc</button></form><form method="POST" action="{{ route('notifications.clear-read') }}" onsubmit="return confirm('Xóa tất cả thông báo đã đọc khỏi hộp thư? Đơn hàng và mã giảm giá vẫn được giữ.')">@csrf @method('DELETE')<button class="ac-text-button ac-danger-text" @disabled($total === $unread)>Dọn thông báo đã đọc</button></form></div></div>
<div class="ac-filter-row"><nav class="ac-chips" aria-label="Loại thông báo">@foreach(['all'=>'Mọi cập nhật','order'=>'Đơn hàng','coupon'=>'Ưu đãi'] as $value=>$text)<a href="{{ route('notifications',['view'=>request('view','all'),'kind'=>$value]) }}" @if(request('kind','all') === $value) aria-current="page" @endif>{{ $text }}</a>@endforeach</nav><span class="ac-muted">{{ $items->total() }} thông báo phù hợp</span></div>
@forelse($items as $n)
<article class="ac-notice {{ $n->read_at ? '' : 'ac-unread' }}">
<span class="ac-notice-icon {{ $n->source === 'coupon' ? 'ac-icon-coupon' : '' }}" aria-hidden="true">@if($n->source === 'coupon')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 6h18v4a2 2 0 0 0 0 4v4H3v-4a2 2 0 0 0 0-4Z"/><path d="M15 6v3m0 3v2m0 2v2"/></svg>@else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>@endif</span>
<div class="ac-notice-content"><div class="ac-notice-meta"><span>{{ $n->source === 'order' ? 'Đơn hàng' : 'Ưu đãi' }}</span>@if(!$n->read_at)<b>Mới</b>@endif<time datetime="{{ $n->at->toIso8601String() }}">{{ $n->at->format('d/m/Y · H:i') }}</time></div><form method="POST" action="{{ route('notifications.open', ['source'=>$n->source,'sourceId'=>$n->source_id]) }}">@csrf<button class="ac-notice-open" type="submit"><strong>{{ $n->title }}</strong>@if($n->body)<span>{{ $n->body }}</span>@endif<small>{{ $n->source === 'order' ? 'Xem đơn hàng' : 'Khám phá ưu đãi' }} →</small></button></form></div>
<form class="ac-notice-delete" method="POST" action="{{ route('notifications.destroy',['source'=>$n->source,'sourceId'=>$n->source_id]) }}" onsubmit="return confirm('Xóa thông báo này khỏi hộp thư?')">@csrf @method('DELETE')<button class="ac-text-button" aria-label="Xóa thông báo: {{ $n->title }}">Xóa</button></form>
</article>
@empty<div class="ac-empty"><span aria-hidden="true">✓</span><h2>{{ $total ? 'Không có thông báo trong mục này' : 'Hộp thư của bạn đang gọn gàng' }}</h2><p>Khi có cập nhật đơn hàng hoặc ưu đãi được gửi, bạn sẽ thấy tại đây.</p><a class="ac-button ac-primary" href="{{ $total ? route('notifications') : route('home') }}">{{ $total ? 'Xem mọi thông báo' : 'Khám phá thực đơn' }}</a></div>@endforelse
</section>
@include('account.tool-pagination',['paginator'=>$items])
<p class="ac-footnote">Dọn hộp thư chỉ xóa thông báo hiển thị với bạn, không xóa lịch sử đơn hàng hoặc lượt sử dụng ưu đãi.</p>
</div>
@endsection
