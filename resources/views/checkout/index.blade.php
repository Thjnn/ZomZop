@extends('layouts.app')
@section('title', 'Thanh toán — ZomZop')
@section('content')
@php
    $money = fn ($amount) => number_format($amount, 0, ',', '.') . 'đ';
    $selection = ['type' => $type, 'delivery_speed' => $speed, 'coupon' => $couponCode, 'address_id' => $selectedAddress?->id];
    $fees = \App\Support\Shipping::fees();
    $availableCount = $voucherOptions->whereNull('reason')->count();
    $total = $subtotal + $shippingFee - $discount;
@endphp
<style>
.zc{--red:#f43247;--ink:#202b3d;--muted:#7b8494;--line:#edf0f4;color:var(--ink);max-width:1120px;margin:10px auto 50px;padding:0 8px;font-size:14px}
.zc *{box-sizing:border-box}.zc a,.zc button,.zc input,.zc textarea{transition:background .16s,border-color .16s,box-shadow .16s}.zc button,.zc a{touch-action:manipulation}.zc button{cursor:pointer}.zc button:disabled{cursor:not-allowed;opacity:.55}
.zc :focus-visible{outline:3px solid #ffb6bd;outline-offset:3px}.zc-heading{margin-bottom:28px}.zc-back{color:var(--muted);font-size:13px;display:inline-block;margin-bottom:14px}.zc-heading h1{font-size:30px;font-weight:800;letter-spacing:-.7px;margin:0 0 8px}.zc-heading p{color:var(--muted);margin:0}.zc-heading p strong{color:var(--red);font-weight:600}
.zc-grid{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:26px;align-items:start}.zc-main{display:grid;gap:18px}.zc-card{background:#fff;border:1px solid var(--line);border-radius:22px;padding:24px;box-shadow:0 3px 15px #1e293b03}.zc-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}.zc-card h2{font-size:17px;font-weight:750;margin:0}.zc-link{color:var(--red);font-size:13px;font-weight:650;background:none;border:0;padding:0;text-decoration:none}.zc-muted{color:var(--muted);font-size:12px;line-height:1.65}.zc-modes{display:grid;grid-template-columns:1fr 1fr;gap:12px}.zc-mode{display:flex;align-items:center;gap:12px;padding:16px;border:1.5px solid #e8ebef;border-radius:15px;text-decoration:none;color:var(--ink)}.zc-mode.active{border-color:var(--red);background:#fff5f5}.zc-mode:hover{border-color:var(--red)}.zc-icon{display:grid;place-items:center;width:40px;height:40px;flex-shrink:0;border-radius:13px;background:#f5f6f8;font-size:22px}.active .zc-icon{background:#ffe4e7}.zc-mode strong{display:block;font-size:14px}.zc-mode small{display:block;color:var(--muted);font-size:11px;margin-top:4px}
.zc-address{display:flex;align-items:flex-start;gap:13px}.zc-address .zc-icon{color:var(--red);background:#fff0f2}.zc-person{font-weight:700;margin:2px 0 7px}.zc-person span{font-weight:400;color:var(--muted);margin-left:8px}.zc-address p{margin:0 0 7px;line-height:1.7}.zc-tag{display:inline-block;background:#f1f6f3;color:#45805a;border-radius:6px;padding:3px 7px;font-size:10px;margin-left:8px;font-weight:600}.zc-speed-label{font-size:12px;color:var(--muted);padding-top:20px;border-top:1px solid var(--line);margin:18px 0 10px}.zc-speeds{display:grid;grid-template-columns:1fr 1fr;gap:10px}.zc-speed{padding:12px 14px;border:1px solid #e8ebef;border-radius:12px;display:flex;justify-content:space-between;gap:8px;text-decoration:none;color:var(--ink);font-size:12px}.zc-speed.active{border-color:var(--red);background:#fff5f5}.zc-speed strong{font-weight:700}
.zc-payments{display:grid;gap:10px}.zc-payment{display:flex;align-items:center;gap:12px;border:1px solid #e8ebef;border-radius:13px;padding:14px;cursor:pointer}.zc-payment:has(input:checked){border-color:var(--red);background:#fff9f9}.zc-payment input{accent-color:var(--red);width:16px;height:16px}.zc-payment strong{font-size:13px}.zc-payment small{display:block;color:var(--muted);font-size:11px;margin-top:3px}.zc-pay-icon{width:35px;height:30px;border-radius:8px;display:grid;place-items:center;background:#f1f4f8;font-size:12px;font-weight:800;color:#4c6076}.zc-note{width:100%;resize:vertical;min-height:85px;border:1px solid #e8ebef;border-radius:12px;padding:13px;font:inherit;font-size:13px;background:#fafbfc}.zc-note:focus{border-color:var(--red);background:#fff}
.zc-summary{position:sticky;top:90px;padding:0;overflow:hidden}.zc-summary-head{padding:22px 22px 16px;display:flex;justify-content:space-between;align-items:center}.zc-summary-head span{font-size:12px;color:var(--muted)}.zc-products{padding:0 22px;max-height:300px;overflow-y:auto;scrollbar-width:thin}.zc-product{display:flex;gap:12px;padding:13px 0;border-bottom:1px solid #f2f4f6;align-items:center}.zc-product img{width:54px;height:54px;object-fit:cover;border-radius:12px;background:#fff3e7}.zc-product-info{flex:1;min-width:0}.zc-product-info strong{display:block;font-size:12px;line-height:1.5}.zc-product-info small{color:var(--muted);font-size:11px}.zc-product-price{font-weight:650;font-size:12px;white-space:nowrap}
.zc-voucher-wrap{padding:18px 22px;border-bottom:1px solid var(--line)}.zc-voucher-trigger{width:100%;display:flex;align-items:center;gap:12px;border:1px dashed #ffb5bf;background:#fff6f7;border-radius:13px;padding:14px;text-align:left;color:var(--red)}.zc-voucher-trigger svg{width:24px;height:24px;flex-shrink:0}.zc-voucher-trigger strong{display:block;font-size:13px}.zc-voucher-trigger small{display:block;color:#9f727b;font-size:10px;margin-top:4px}.zc-arrow{margin-left:auto;font-size:20px}.zc-remove{display:block;text-align:right;margin-top:9px}.zc-totals{padding:20px 22px}.zc-row{display:flex;justify-content:space-between;gap:12px;margin-bottom:12px;color:#788294;font-size:13px}.zc-row span:last-child{color:var(--ink)}.zc-row.zc-saving span{color:#27845b}.zc-grand{display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--line);padding-top:18px;margin-top:16px;font-weight:700}.zc-grand strong{font-size:24px;color:var(--red);letter-spacing:-.6px}.zc-submit{width:100%;background:var(--red);color:#fff;border:none;border-radius:13px;padding:15px;font-weight:750;font-size:14px;margin-top:20px;box-shadow:0 7px 16px #f4324720}.zc-submit:hover{background:#df2438}.zc-policy{text-align:center;font-size:10px;line-height:1.8;color:var(--muted);margin-top:12px}.zc-policy a{color:var(--muted);text-decoration:underline}.zc-alert{padding:13px 16px;border-radius:12px;margin-bottom:16px;line-height:1.7;background:#fff1f2;color:#b52c40;font-size:13px}.zc-alert.success{background:#edf8f0;color:#337249}.zc-alert.changes{background:#fff8e8;color:#896423}
.zc-dialog{position:fixed;inset:0;margin:auto;width:min(560px,calc(100vw - 28px));max-height:85vh;border:0;padding:0;border-radius:24px;background:#fff;color:var(--ink);box-shadow:0 24px 100px #17243b35;font-family:inherit}.zc-dialog::backdrop{background:#14203380;backdrop-filter:blur(3px)}.zc-dialog-head{padding:24px 24px 16px;display:flex;justify-content:space-between;align-items:start;gap:15px}.zc-dialog h2{margin:0;font-size:21px;font-weight:750}.zc-dialog-head p{color:var(--muted);font-size:12px;margin:6px 0 0}.zc-close{width:32px;height:32px;display:grid;place-items:center;border-radius:50%;background:#f4f5f7;border:0;font-size:19px;color:#677285}.zc-search{display:flex;gap:8px;padding:0 24px 18px}.zc-search input{min-width:0;flex:1;border:1px solid #e5e9ee;border-radius:11px;padding:12px;font:inherit;font-size:13px}.zc-search button{background:var(--red);color:#fff;border:0;border-radius:11px;padding:0 16px;font-size:12px;font-weight:700}.zc-vouchers{padding:4px 24px 24px;max-height:52vh;overflow-y:auto}.zc-voucher{border:1px solid #eee6e8;border-radius:15px;overflow:hidden;display:flex;margin-bottom:12px}.zc-voucher-side{width:96px;flex-shrink:0;background:#fff1f3;color:var(--red);display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:12px;border-right:1px dashed #ffc8cf;font-weight:800;font-size:16px;gap:7px}.zc-voucher-side small{font-weight:500;font-size:10px}.zc-voucher-body{padding:15px;flex:1;min-width:0}.zc-voucher-title{display:flex;justify-content:space-between;gap:8px;align-items:center;font-size:13px;font-weight:750}.zc-pick{background:var(--red);color:#fff;text-decoration:none;padding:5px 12px;border-radius:7px;font-size:11px;white-space:nowrap}.zc-voucher p{margin:7px 0;font-size:12px;line-height:1.5}.zc-voucher small{font-size:10px;color:var(--muted);line-height:1.6}.zc-voucher.unavailable .zc-voucher-side{background:#f5f6f7;color:#9198a5;border-color:#e1e4e9}.zc-reason{color:#a26d25!important;background:#fff8eb;padding:7px 9px;border-radius:7px;font-size:11px!important}.zc-voucher[hidden]{display:none}.zc-empty{text-align:center;padding:22px;color:var(--muted);font-size:13px;line-height:1.7}.zc-address-choice{display:block;padding:17px;margin-bottom:12px;border:1px solid #e5e9ee;border-radius:13px;color:var(--ink);text-decoration:none;font-size:13px}.zc-address-choice.active{background:#fff6f7;border-color:var(--red)}.zc-address-choice p{margin:7px 0;color:var(--muted);line-height:1.6}.zc-dialog-footer{padding:0 24px 24px}.zc-manage{display:block;text-align:center;padding:12px;border:1px dashed #ffc0c8;border-radius:12px;color:var(--red);text-decoration:none;font-size:13px;font-weight:600}
@media(max-width:900px){.zc-grid{grid-template-columns:1fr 340px;gap:16px}.zc-card{padding:18px}.zc-summary{padding:0}.zc-mode{padding:12px;gap:8px}.zc-mode small{font-size:10px}.zc-icon{width:32px;height:32px}.zc-person span{display:block;margin:4px 0}.zc-speeds{grid-template-columns:1fr}}
@media(max-width:700px){.zc{padding:0;margin-top:0}.zc-grid{grid-template-columns:1fr}.zc-summary{position:static}.zc-heading h1{font-size:26px}.zc-heading{margin-bottom:20px}.zc-modes{gap:8px}.zc-mode{padding:13px 10px}.zc-speeds{grid-template-columns:1fr 1fr}.zc-speed{font-size:11px;flex-direction:column;gap:3px}.zc-dialog-head{padding:20px 18px 14px}.zc-search{padding:0 18px 16px}.zc-vouchers{padding:0 18px 18px}.zc-voucher-side{width:76px;font-size:14px}.zc-voucher-body{padding:12px}.zc-dialog-footer{padding:0 18px 20px}}
</style>
<div class="zc" id="zomzop-checkout">
    <header class="zc-heading">
        <a href="{{ route('cart.index') }}" class="zc-back">← Quay lại giỏ hàng</a>
        <h1>Hoàn tất đơn hàng</h1>
        <p>Một bữa ngon đang chờ bạn · <strong>{{ $branchName }}</strong></p>
    </header>
    @if(session('success'))<div class="zc-alert success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="zc-alert" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @if($couponError)<div class="zc-alert" role="alert">{{ $couponError }} Bạn có thể chọn mã khác trong kho voucher.</div>@endif
    @if(session('cart_changes'))<div class="zc-alert changes">@foreach(session('cart_changes') as $change)<p>{{ $change }}</p>@endforeach</div>@endif
    <noscript><p class="zc-alert">Bật JavaScript để mở kho voucher và danh sách địa chỉ. Bạn vẫn có thể quản lý địa chỉ bằng liên kết bên dưới.</p></noscript>
    <div class="zc-grid">
        <div class="zc-main">
            <section class="zc-card">
                <div class="zc-card-head"><h2>Cách nhận hàng</h2></div>
                <div class="zc-modes">
                    <a href="{{ route('checkout.index', array_merge($selection, ['type' => 'delivery'])) }}" class="zc-mode zc-requote {{ $type === 'delivery' ? 'active' : '' }}" @if($type === 'delivery') aria-current="true" @endif>
                        <span class="zc-icon" aria-hidden="true"><svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h11v11H3zM14 10h4l3 4v3h-7M3 10h7"/><circle cx="7" cy="18" r="2" fill="white"/><circle cx="18" cy="18" r="2" fill="white"/></svg></span><span><strong>Giao tận nơi</strong><small>Đến địa chỉ của bạn</small></span>
                    </a>
                    <a href="{{ route('checkout.index', array_merge($selection, ['type' => 'takeaway'])) }}" class="zc-mode zc-requote {{ $type === 'takeaway' ? 'active' : '' }}" @if($type === 'takeaway') aria-current="true" @endif>
                        <span class="zc-icon" aria-hidden="true">⌂</span><span><strong>Tự đến lấy</strong><small>Không tính phí giao hàng</small></span>
                    </a>
                </div>
            </section>
            <section class="zc-card">
                <div class="zc-card-head"><h2>{{ $type === 'delivery' ? 'Giao đến địa chỉ' : 'Nhận tại cửa hàng' }}</h2>
                    @if($type === 'delivery')<button type="button" class="zc-link" data-open="zc-address-dialog">Thay đổi</button>@endif
                </div>
                @if($type === 'delivery' && $selectedAddress)
                <div class="zc-address"><span class="zc-icon" aria-hidden="true">⌖</span><div>
                    <p class="zc-person">{{ $selectedAddress->name }} <span>{{ $selectedAddress->phone }}</span></p>
                    <p>{{ $selectedAddress->address }}</p>
                    <span class="zc-muted">{{ $selectedAddress->label }}</span>@if($selectedAddress->is_default)<span class="zc-tag">Mặc định</span>@endif
                    @if($selectedAddress->note)<p class="zc-muted">Lưu ý: {{ $selectedAddress->note }}</p>@endif
                </div></div>
                <p style="margin-top:12px"><a class="zc-link zc-requote" href="{{ route('addresses.index', ['from' => 'checkout']) }}">Quản lý / thêm địa chỉ</a></p>
                <p class="zc-speed-label">HÌNH THỨC GIAO HÀNG</p>
                <div class="zc-speeds">
                    @foreach(\App\Support\Shipping::SPEEDS as $key => $label)
                    <a class="zc-speed zc-requote {{ $speed === $key ? 'active' : '' }}" href="{{ route('checkout.index', array_merge($selection, ['delivery_speed' => $key])) }}"><span>{{ $label }}</span><strong>{{ $money($fees[$key]) }}</strong></a>
                    @endforeach
                </div>
                @else
                <div class="zc-address"><span class="zc-icon" aria-hidden="true">⌂</span><div><p class="zc-person">{{ $branchName }}</p><p class="zc-muted">Đến chi nhánh nhận món khi đơn đã sẵn sàng. Mã nhận món sẽ được cấp sau khi đặt hàng.</p></div></div>
                @endif
            </section>
            <form id="zc-order-form" method="POST" action="{{ route('checkout.store') }}" class="zc-main">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="delivery_speed" value="{{ $speed }}">
                <input type="hidden" name="coupon_code" value="{{ $couponCode }}">
                @if($type === 'delivery')<input type="hidden" name="address_id" value="{{ $selectedAddress?->id }}">@endif
                <section class="zc-card">
                    <div class="zc-card-head"><h2>Phương thức thanh toán</h2></div>
                    <div class="zc-payments">
                        @forelse($paymentMethods as $method)
                        <label class="zc-payment"><input type="radio" name="payment_method" value="{{ $method }}" @checked(old('payment_method', $paymentMethods[0] ?? '') === $method) required>
                            <span class="zc-pay-icon" aria-hidden="true">{{ ['cash' => '₫', 'momo' => 'Mo', 'vnpay' => 'VN'][$method] }}</span>
                            <span><strong>{{ \App\Support\PaymentMethods::LABELS[$method] }}</strong><small>{{ $method === 'cash' ? 'Thanh toán khi nhận món' : 'Phương thức thanh toán trực tuyến' }}</small></span>
                        </label>
                        @empty<p class="zc-muted">Cửa hàng đang tạm ngưng thanh toán. Vui lòng quay lại sau.</p>@endforelse
                    </div>
                </section>
                <section class="zc-card">
                    <div class="zc-card-head"><h2>Ghi chú cho cửa hàng</h2><span class="zc-muted">Không bắt buộc</span></div>
                    <textarea class="zc-note" name="note" maxlength="500" rows="3" placeholder="Ví dụ: ít cay, không hành, để riêng nước sốt…">{{ old('note') }}</textarea>
                </section>
            </form>
        </div>
        <aside class="zc-card zc-summary" aria-label="Tóm tắt đơn hàng">
            <div class="zc-summary-head"><h2>Đơn của bạn</h2><span>{{ collect($cart['items'])->sum('quantity') }} món</span></div>
            <div class="zc-products">
                @foreach($cart['items'] as $item)
                <div class="zc-product"><img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"><div class="zc-product-info"><strong>{{ $item['name'] }}</strong><small>Số lượng: {{ $item['quantity'] }}</small></div><span class="zc-product-price">{{ $money($item['price'] * $item['quantity']) }}</span></div>
                @endforeach
            </div>
            <div class="zc-voucher-wrap">
                <button class="zc-voucher-trigger" type="button" data-open="zc-voucher-dialog" aria-haspopup="dialog">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 5h18v5a2 2 0 0 0 0 4v5H3v-5a2 2 0 0 0 0-4V5Z"/><path d="M9 5v14" stroke-dasharray="2 2"/></svg>
                    <span><strong>{{ $couponCode ?: 'Chọn hoặc nhập mã giảm giá' }}</strong><small>{{ $couponCode ? 'Đã tiết kiệm ' . $money($discount) . ' · Bấm để đổi mã' : $availableCount . ' ưu đãi có thể dùng cho đơn này' }}</small></span><span class="zc-arrow" aria-hidden="true">›</span>
                </button>
                @if($couponCode)<a class="zc-link zc-remove zc-requote" href="{{ route('checkout.index', array_merge($selection, ['coupon' => ''])) }}">Bỏ mã</a>@endif
            </div>
            <div class="zc-totals">
                <div class="zc-row"><span>Tạm tính</span><span>{{ $money($subtotal) }}</span></div>
                <div class="zc-row"><span>Phí giao hàng</span><span>{{ $shippingFee ? $money($shippingFee) : 'Miễn phí' }}</span></div>
                <div class="zc-row zc-saving"><span>Giảm giá</span><span>-{{ $money($discount) }}</span></div>
                <div class="zc-grand"><span>Tổng thanh toán</span><strong>{{ $money($total) }}</strong></div>
                <button id="zc-submit" class="zc-submit" form="zc-order-form" type="submit" @disabled(empty($paymentMethods))>Đặt hàng · {{ $money($total) }}</button>
                <p class="zc-policy">Bằng việc đặt hàng, bạn đồng ý với<br><a href="{{ route('privacy-policy') }}">chính sách bảo mật của ZomZop</a>.</p>
            </div>
        </aside>
    </div>
    <dialog id="zc-voucher-dialog" class="zc-dialog" aria-labelledby="zc-voucher-title">
        <div class="zc-dialog-head"><div><h2 id="zc-voucher-title">Ưu đãi dành cho bạn</h2><p>Chọn một mã phù hợp để tiết kiệm hơn.</p></div><button type="button" class="zc-close" data-close aria-label="Đóng kho voucher">×</button></div>
        <form class="zc-search" id="zc-coupon-form" action="{{ route('checkout.index') }}" method="GET">
            <input type="hidden" name="type" value="{{ $type }}"><input type="hidden" name="delivery_speed" value="{{ $speed }}"><input type="hidden" name="address_id" value="{{ $selectedAddress?->id }}">
            <input id="zc-coupon-search" name="coupon" maxlength="20" placeholder="Tìm ưu đãi hoặc nhập mã của bạn" aria-label="Tìm voucher hoặc nhập mã" autocomplete="off">
            <button type="submit">Áp dụng</button>
        </form>
        <div class="zc-vouchers">
            @foreach($voucherOptions as $option)
            @php $voucher = $option['coupon']; @endphp
            <article class="zc-voucher {{ $option['reason'] ? 'unavailable' : '' }}" data-voucher-search="{{ $voucher->code . ' ' . $voucher->summary . ' ' . $voucher->scope_label }}">
                <div class="zc-voucher-side">{{ $voucher->badge }}<small>{{ $voucher->scope_label }}</small></div>
                <div class="zc-voucher-body"><div class="zc-voucher-title"><span>{{ $voucher->code }}</span>
                    @unless($option['reason'])<a class="zc-pick zc-requote" href="{{ route('checkout.index', array_merge($selection, ['coupon' => $voucher->code])) }}">{{ $couponCode === $voucher->code ? 'Đang dùng' : 'Chọn' }}</a>@endunless
                </div><p>{{ $voucher->summary }}</p><small>Đơn từ {{ $money($voucher->min_order_value) }} · {{ $voucher->expired_at ? 'HSD ' . $voucher->expired_at->format('d/m/Y') : 'Không thời hạn' }}<br>{{ $voucher->max_uses_per_user }} lượt / khách</small>
                @if($option['reason'])<p class="zc-reason">{{ $option['reason'] }}</p>@else<p style="color:#27845b;font-size:11px">Tiết kiệm {{ $money($option['saving']) }} cho đơn này</p>@endif
                </div>
            </article>
            @endforeach
            <p id="zc-voucher-empty" class="zc-empty" @if($voucherOptions->isNotEmpty()) hidden @endif>Chưa có voucher phù hợp trong danh sách.<br>Nếu có mã riêng, nhập mã rồi bấm Áp dụng.</p>
        </div>
    </dialog>
    <dialog id="zc-address-dialog" class="zc-dialog" aria-labelledby="zc-address-title">
        <div class="zc-dialog-head"><div><h2 id="zc-address-title">Chọn địa chỉ nhận hàng</h2><p>Địa chỉ được lấy từ sổ địa chỉ của bạn.</p></div><button type="button" class="zc-close" data-close aria-label="Đóng danh sách địa chỉ">×</button></div>
        <div class="zc-vouchers">
            @foreach($addresses as $address)
            <a class="zc-address-choice zc-requote {{ $selectedAddress?->id === $address->id ? 'active' : '' }}" href="{{ route('checkout.index', array_merge($selection, ['address_id' => $address->id])) }}"><strong>{{ $address->name }} · {{ $address->phone }}</strong>@if($address->is_default)<span class="zc-tag">Mặc định</span>@endif<p>{{ $address->address }}</p><small>{{ $address->label }}{{ $selectedAddress?->id === $address->id ? ' · Đang chọn' : '' }}</small></a>
            @endforeach
        </div>
        <div class="zc-dialog-footer"><a class="zc-manage zc-requote" href="{{ route('addresses.index', ['from' => 'checkout']) }}">＋ Thêm / sửa địa chỉ trong sổ địa chỉ</a></div>
    </dialog>
</div>
<script>
(() => {
    const root = document.getElementById('zomzop-checkout');
    const form = document.getElementById('zc-order-form');
    const submit = document.getElementById('zc-submit');
    const key = 'zomzop-checkout-draft-{{ auth()->id() }}';
    function saveDraft() {
        try { sessionStorage.setItem(key, JSON.stringify({note: form.elements.note.value, payment: form.querySelector('[name="payment_method"]:checked')?.value})); } catch (_) {}
    }
    try {
        const draft = JSON.parse(sessionStorage.getItem(key) || '{}');
        if (!form.elements.note.value && draft.note) form.elements.note.value = draft.note;
        if (draft.payment) Array.from(form.querySelectorAll('[name="payment_method"]')).forEach(input => { if(input.value === draft.payment) input.checked = true; });
    } catch (_) {}
    form.addEventListener('input', saveDraft);
    root.querySelectorAll('.zc-requote').forEach(link => link.addEventListener('click', () => { saveDraft(); }));
    document.getElementById('zc-coupon-form').addEventListener('submit', saveDraft);
    form.addEventListener('submit', () => { saveDraft(); submit.disabled = true; submit.textContent = 'Đang gửi đơn hàng…'; });
    window.addEventListener('pageshow', event => { if(event.persisted) window.location.reload(); });
    root.querySelectorAll('[data-open]').forEach(button => button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.open);
        dialog.showModal();
        if(dialog.id === 'zc-voucher-dialog') document.getElementById('zc-coupon-search').focus();
    }));
    root.querySelectorAll('dialog').forEach(dialog => {
        dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', event => {
            if(event.target !== dialog) return;
            const box = dialog.getBoundingClientRect();
            if(event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
        });
    });
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/gi, 'd').toLowerCase().trim();
    document.getElementById('zc-coupon-search').addEventListener('input', event => {
        const query = normalize(event.target.value); let count = 0;
        root.querySelectorAll('[data-voucher-search]').forEach(card => {
            card.hidden = !normalize(card.dataset.voucherSearch).includes(query);
            if(!card.hidden) count++;
        });
        document.getElementById('zc-voucher-empty').hidden = count > 0;
    });
})();
</script>
@endsection
