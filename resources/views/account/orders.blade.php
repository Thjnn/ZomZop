@extends('layouts.app')
@section('title', 'Đơn hàng của tôi - ZomZop')
@section('content')
<div class="zo-orders">
    <nav class="zo-breadcrumb" aria-label="Đường dẫn"><a href="{{ url('/menu') }}">← Tài khoản</a><span>/</span><span>Đơn hàng của tôi</span></nav>
    <header class="zo-heading"><div><p class="zo-eyebrow">NHỮNG BỮA NGON CỦA BẠN</p><h1>Đơn hàng của tôi</h1><p>Theo dõi đơn đang chuẩn bị, tìm lại món bạn yêu thích.</p></div><a class="zo-button zo-primary" href="{{ route('home') }}">Đặt món mới →</a></header>
    @include('account.order-messages')
    <section class="zo-toolbar" aria-label="Tìm và lọc đơn hàng">
        <div class="zo-search-row"><div><strong>Lịch sử đặt món</strong><span>{{ $totalOrders }} đơn hàng trong tài khoản</span></div><form action="{{ route('orders') }}" method="GET" class="zo-search"><label class="zo-sr-only" for="zo-query">Tìm theo mã đơn hoặc tên món</label><input id="zo-query" name="q" type="search" maxlength="100" value="{{ $search }}" placeholder="Tìm mã đơn hoặc tên món…">@if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif<button type="submit" class="zo-button zo-dark">Tìm kiếm</button></form></div>
        <nav class="zo-tabs" aria-label="Trạng thái đơn hàng"><a href="{{ route('orders', ['q' => $search]) }}" @if(!request('status')) aria-current="page" @endif>Tất cả <span>{{ $totalOrders }}</span></a>@foreach($labels as $key => $label)<a href="{{ route('orders', ['status' => $key, 'q' => $search]) }}" @if(request('status') === $key) aria-current="page" @endif>{{ $label }} <span>{{ $statusCounts[$key] ?? 0 }}</span></a>@endforeach</nav>
    </section>
    <div class="zo-results"><p>@if($orders->total())Hiển thị {{ $orders->firstItem() }}–{{ $orders->lastItem() }} / {{ $orders->total() }} đơn phù hợp @else Không có đơn phù hợp @endif</p>@if($search !== '' || request('status'))<a href="{{ route('orders') }}">Xóa bộ lọc</a>@else<span>Mới nhất trước</span>@endif</div>
    <div class="zo-list">
    @forelse($orders as $order)
        @php
            $active = !in_array($order->status, ['completed', 'cancelled'], true);
            $hints = ['pending'=>'Đơn đang chờ chi nhánh xác nhận.', 'confirmed'=>'Chi nhánh đã tiếp nhận đơn hàng của bạn.', 'cooking'=>'Bếp đang chuẩn bị những món ngon cho bạn.', 'ready'=> $order->isDelivery() ? 'Món đã sẵn sàng để giao đến bạn.' : 'Món đã sẵn sàng để bạn đến nhận.', 'completed'=>'Cảm ơn bạn đã dùng bữa cùng ZomZop!', 'cancelled'=>'Đơn hàng đã hủy. Bạn có thể chọn món và đặt lại.'];
        @endphp
        <article class="zo-order {{ $active ? 'zo-order-active' : '' }}" aria-label="Đơn hàng {{ $order->order_code }}">
            <header class="zo-order-head"><div><div class="zo-code"><a href="{{ route('orders.show', $order) }}">#{{ $order->order_code }}</a>@if(str_starts_with($order->order_code, 'DEMO-') || str_starts_with($order->order_code, 'SIM-'))<small>Dữ liệu mẫu</small>@endif</div><p>{{ $order->branch?->name ?? 'Chi nhánh không còn khả dụng' }} · {{ $order->created_at->format('d/m/Y · H:i') }}</p></div><span class="zo-status zo-status-{{ $order->status }}"><i aria-hidden="true"></i>{{ $labels[$order->status] ?? $order->status }}</span></header>
            <div class="zo-order-body"><div class="zo-food-list">
                @foreach($order->items->take(2) as $line) @include('account.order-list-item') @endforeach
                @if($order->items->count() > 2)<details class="zo-more"><summary>Xem thêm {{ $order->items->count() - 2 }} loại món</summary><div>@foreach($order->items->slice(2) as $line) @include('account.order-list-item') @endforeach</div></details>@endif
                @if($order->items->isEmpty())<p class="zo-muted">Không còn chi tiết món trong đơn này.</p>@endif
            </div><aside class="zo-order-total" aria-label="Tổng thanh toán"><span>Tổng thanh toán</span><strong>{{ number_format($order->total, 0, ',', '.') }}<small>đ</small></strong><p>{{ $order->items->sum('quantity') }} món · {{ $order->isDelivery() ? 'Giao tận nơi' : 'Nhận tại quán' }}</p><span class="zo-payment">{{ $order->isPaid() ? 'Đã thanh toán' : 'Chưa thanh toán' }}</span></aside></div>
            <footer class="zo-order-foot"><p>{{ $hints[$order->status] ?? 'Mở chi tiết để xem thông tin đơn hàng.' }}</p><div class="zo-actions">@if(\App\Support\CustomerOrderCancellation::allowed($order))<a class="zo-button zo-outline" href="{{ route('orders.cancel.create', $order) }}">Hủy đơn</a>@endif<a class="zo-button {{ $active ? 'zo-primary' : 'zo-outline' }}" href="{{ route('orders.show', $order) }}">{{ $active ? 'Theo dõi đơn' : 'Xem chi tiết' }}</a><a class="zo-button {{ $active ? 'zo-outline' : 'zo-primary' }}" href="{{ route('orders.reorder.preview', $order) }}">Đặt lại</a>@if($order->isCompleted())<a class="zo-button zo-outline" href="{{ route('orders.review', $order) }}">{{ $order->review ? 'Xem đánh giá' : 'Đánh giá' }}</a>@endif</div></footer>
        </article>
    @empty
        <section class="zo-empty"><div aria-hidden="true">♡</div><h2>{{ $search !== '' || request('status') ? 'Chưa tìm thấy đơn phù hợp' : 'Bữa ngon đầu tiên đang chờ bạn' }}</h2><p>{{ $search !== '' || request('status') ? 'Thử mã đơn, tên món khác hoặc xóa bộ lọc để xem tất cả.' : 'Sau khi đặt món, bạn có thể theo dõi và xem lại đơn hàng tại đây.' }}</p><a class="zo-button zo-primary" href="{{ $search !== '' || request('status') ? route('orders') : route('home') }}">{{ $search !== '' || request('status') ? 'Xem tất cả đơn' : 'Khám phá thực đơn' }}</a></section>
    @endforelse
    </div>
    @if($orders->hasPages())<nav class="zo-pagination" aria-label="Phân trang đơn hàng">@if($orders->onFirstPage())<span aria-disabled="true">← Trang trước</span>@else<a href="{{ $orders->previousPageUrl() }}" rel="prev">← Trang trước</a>@endif<strong>Trang {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</strong>@if($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}" rel="next">Trang sau →</a>@else<span aria-disabled="true">Trang sau →</span>@endif</nav>@endif
    <div class="zo-support">Cần hỗ trợ về đơn hàng? <a href="{{ route('support') }}">Liên hệ ZomZop →</a></div>
</div>
@endsection
