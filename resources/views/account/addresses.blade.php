@extends('layouts.app')
@section('title', 'Sổ địa chỉ - ZomZop')
@section('content')
@php $fromCheckout = request('from') === 'checkout' || old('from') === 'checkout'; @endphp
<div class="ac-page" data-address-book>
<a class="ac-back" href="{{ url('/menu') }}">← Tài khoản</a>
<header class="ac-heading"><div><p class="ac-eyebrow">BỮA NGON ĐẾN ĐÚNG NƠI</p><h1>Sổ địa chỉ</h1><p>Lưu nơi nhận quen thuộc để đặt món thuận tiện hơn.</p></div><button class="ac-button ac-primary" type="button" data-address-add>+ Thêm địa chỉ</button></header>
@if(session('success'))<div class="ac-alert ac-success" role="status">{{ session('success') }}</div>@endif
@if($fromCheckout)<div class="ac-banner ac-checkout-banner"><div><strong>Đang chọn địa chỉ cho đơn hàng</strong><p>Giỏ hàng vẫn được giữ. Chọn một địa chỉ đã lưu hoặc thêm địa chỉ mới.</p></div><a class="ac-button ac-outline" href="{{ route('checkout.index', array_merge(session('checkout.preferences', []), ['type'=>$addresses->isEmpty() ? 'takeaway' : 'delivery'])) }}">{{ $addresses->isEmpty() ? 'Chuyển sang nhận tại quán' : 'Quay lại thanh toán' }}</a></div>@endif
<div class="ac-panel ac-address-overview"><div><span class="ac-map-icon" aria-hidden="true">⌖</span><div><strong>{{ $addresses->count() }} địa chỉ đã lưu</strong><p>Địa chỉ mặc định được ưu tiên khi bạn chọn nơi nhận hàng.</p></div></div><div class="ac-search-block"><label class="ac-sr-only" for="ac-address-search">Tìm địa chỉ</label><input id="ac-address-search" type="search" placeholder="Tìm tên, số điện thoại hoặc địa chỉ…" data-address-search></div></div>
<div class="ac-address-grid">
@foreach($addresses as $a)
<article class="ac-address-card {{ $a->is_default ? 'ac-address-default' : '' }}" data-address-card data-search="{{ $a->name . ' ' . $a->phone . ' ' . $a->address . ' ' . $a->label }}">
<header><span class="ac-address-label">{{ $a->label }}</span>@if($a->is_default)<span class="ac-badge ac-green">Mặc định</span>@endif</header>
<h2>{{ $a->name }}</h2><p class="ac-phone">{{ $a->phone }}</p><p class="ac-address-text">{{ $a->address }}</p>@if($a->note)<p class="ac-address-note">Ghi chú: {{ $a->note }}</p>@endif
@if($fromCheckout)<a class="ac-button ac-primary ac-address-choose" href="{{ route('checkout.index', array_merge(session('checkout.preferences', []), ['type'=>'delivery','address_id'=>$a->id])) }}">Giao đến địa chỉ này →</a>@endif
<footer><div class="ac-address-actions"><button type="button" class="ac-text-button" data-address-edit='@json($a)'>Chỉnh sửa</button><form method="POST" action="{{ route('addresses.destroy',['address'=>$a,'from'=>$fromCheckout ? 'checkout' : null]) }}" onsubmit="return confirm('Xóa địa chỉ này khỏi sổ địa chỉ?')">@csrf @method('DELETE')<button class="ac-text-button ac-danger-text">Xóa</button></form></div>@unless($a->is_default)<form method="POST" action="{{ route('addresses.default',['address'=>$a,'from'=>$fromCheckout ? 'checkout' : null]) }}">@csrf<button class="ac-text-button">Đặt mặc định</button></form>@endunless</footer>
</article>
@endforeach
<button type="button" class="ac-add-card" data-address-add><span aria-hidden="true">+</span><strong>Thêm nơi nhận mới</strong><small>Nhà, công ty hoặc một địa chỉ khác</small></button>
</div>
<div class="ac-empty" data-address-empty hidden><h2>Không tìm thấy địa chỉ phù hợp</h2><p>Thử từ khóa khác hoặc thêm địa chỉ mới.</p><button class="ac-button ac-primary" data-address-add type="button">Thêm địa chỉ</button></div>
<p class="ac-footnote">Kiểm tra số điện thoại và địa chỉ đầy đủ trước khi lưu để việc nhận món thuận tiện hơn.</p>
<noscript><p class="ac-alert ac-error">Vui lòng bật JavaScript để mở biểu mẫu thêm hoặc sửa địa chỉ.</p></noscript>
<dialog id="ac-address-dialog" class="ac-dialog" aria-labelledby="ac-address-title" data-address-dialog>
<form method="POST" action="{{ route('addresses.store') }}" data-address-form data-create="{{ route('addresses.store') }}" data-base="{{ url('/addresses') }}" data-from-checkout="{{ $fromCheckout ? '1' : '0' }}" data-open-initial="{{ $errors->any() || ($fromCheckout && $addresses->isEmpty()) ? '1' : '0' }}">
@csrf
@if($fromCheckout)<input type="hidden" name="from" value="checkout">@endif
<input type="hidden" name="_method" value="PUT" data-address-method disabled>
<input type="hidden" name="editing_id" value="{{ old('editing_id') }}">
<header class="ac-dialog-heading"><div><p class="ac-eyebrow">THÔNG TIN NHẬN HÀNG</p><h2 id="ac-address-title">Thêm địa chỉ</h2></div><button type="button" class="ac-close" data-address-close aria-label="Đóng biểu mẫu">×</button></header>
@if($errors->any())<div class="ac-alert ac-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<fieldset class="ac-address-types"><legend>Lưu với tên</legend>@foreach(['Nhà','Công ty','Khác'] as $label)<label><input type="radio" name="label" value="{{ $label }}" required @checked(old('label','Nhà') === $label)><span>{{ $label }}</span></label>@endforeach</fieldset>
<div class="ac-form-grid"><div class="ac-field"><label for="ac-recipient">Họ tên người nhận *</label><input id="ac-recipient" name="name" required maxlength="100" autocomplete="name" value="{{ old('name') }}"></div><div class="ac-field"><label for="ac-phone">Số điện thoại *</label><input id="ac-phone" name="phone" type="tel" required autocomplete="tel" placeholder="0901234567" value="{{ old('phone') }}"></div><div class="ac-field ac-full"><label for="ac-address">Địa chỉ nhận hàng *</label><textarea id="ac-address" name="address" required maxlength="255" rows="3" autocomplete="street-address" placeholder="Số nhà, đường, phường/xã, tỉnh/thành…">{{ old('address') }}</textarea></div><div class="ac-field ac-full"><label for="ac-note">Ghi chú giao hàng</label><input id="ac-note" name="note" maxlength="255" placeholder="Ví dụ: gọi trước khi giao, cổng bên trái…" value="{{ old('note') }}"></div></div>
<label class="ac-default-check"><input type="checkbox" name="is_default" value="1" @checked(old('is_default'))><span>Đặt làm địa chỉ mặc định<small>Địa chỉ đầu tiên sẽ tự động được đặt làm mặc định.</small></span></label>
<footer class="ac-dialog-footer"><button type="button" class="ac-button ac-outline" data-address-close>Hủy chỉnh sửa</button><button type="submit" class="ac-button ac-primary">Lưu địa chỉ</button></footer>
</form>
</dialog>
</div>
@endsection
