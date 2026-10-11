@extends('layouts.app')

@section('title', 'Thanh Toán — ZomZop')

@section('content')
@if (session('cart_changes'))
<div class="max-w-5xl mx-auto mb-6 px-4 py-3 rounded-xl bg-amber-50 text-amber-700 text-sm">
    <p class="font-semibold mb-1">Giỏ hàng đã được cập nhật theo chi nhánh:</p>
    @foreach (session('cart_changes') as $change) <p>• {{ $change }}</p> @endforeach
</div>
@endif

<div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

    {{-- ========== FORM ========== --}}
    <form action="{{ route('checkout.store') }}" method="POST" class="lg:col-span-3 space-y-6">
        @csrf

        <div>
            <h1 class="text-2xl font-bold text-slate-800">Thanh Toán</h1>
            <p class="text-sm text-slate-500 mt-1">
                Chi nhánh: <strong class="text-red-500">{{ $branchName }}</strong>
                &middot; <a href="{{ route('cart.index') }}" class="text-red-500 hover:underline text-xs">← Sửa giỏ hàng</a>
            </p>
        </div>

        {{-- Loại đơn --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs space-y-4">
            <h3 class="font-bold text-slate-700 text-sm">Loại đơn</h3>
            <div class="flex gap-3">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="type" value="takeaway" {{ old('type', 'takeaway') === 'takeaway' ? 'checked' : '' }} class="peer hidden">
                    <div class="border-2 border-slate-200 peer-checked:border-red-500 rounded-xl p-4 text-center transition hover:border-red-300">
                        <span class="text-2xl">🏃</span>
                        <p class="text-sm font-semibold text-slate-700 mt-1">Mang đi</p>
                        <p class="text-xs text-slate-400">Tự đến lấy tại cửa hàng</p>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="type" value="delivery" {{ old('type') === 'delivery' ? 'checked' : '' }} class="peer hidden">
                    <div class="border-2 border-slate-200 peer-checked:border-red-500 rounded-xl p-4 text-center transition hover:border-red-300">
                        <span class="text-2xl">🛵</span>
                        <p class="text-sm font-semibold text-slate-700 mt-1">Giao hàng</p>
                        <p class="text-xs text-slate-400">Giao tận nơi</p>
                    </div>
                </label>
            </div>

            <div id="delivery-address-group" class="{{ old('type') === 'delivery' ? '' : 'hidden' }}">
                <label class="text-xs font-semibold text-slate-600 mb-1 block">Địa chỉ giao hàng *</label>
                <input type="text" name="delivery_address" id="delivery-address-input" value="{{ old('delivery_address') }}" placeholder="Nhập địa chỉ nhận hàng..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-red-400 focus:outline-hidden transition">
                @if ($addresses->isNotEmpty())
                    <div class="mt-3">
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Hoặc chọn địa chỉ đã lưu</label>
                        <select id="saved-address" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-red-400 focus:outline-hidden transition">
                            <option value="">-- Chọn địa chỉ --</option>
                            @foreach ($addresses as $a)
                                <option value="{{ $a->address }}">{{ $a->label }}{{ $a->is_default ? ' (mặc định)' : '' }} · {{ $a->name }} · {{ $a->address }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="mt-4">
                    <label class="text-xs font-semibold text-slate-600 mb-2 block">Tốc độ giao hàng</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_speed" value="standard" {{ old('delivery_speed', 'standard') === 'standard' ? 'checked' : '' }} class="peer hidden">
                            <div class="border-2 border-slate-200 peer-checked:border-red-500 rounded-xl p-3 text-center transition hover:border-red-300">
                                <p class="text-sm font-semibold text-slate-700">🛵 Tiêu chuẩn</p>
                                <p class="text-xs text-slate-400">Phí {{ number_format($fees['standard'], 0, ',', '.') }} đ</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_speed" value="express" {{ old('delivery_speed') === 'express' ? 'checked' : '' }} class="peer hidden">
                            <div class="border-2 border-slate-200 peer-checked:border-red-500 rounded-xl p-3 text-center transition hover:border-red-300">
                                <p class="text-sm font-semibold text-slate-700">⚡ Hỏa tốc</p>
                                <p class="text-xs text-slate-400">Phí {{ number_format($fees['express'], 0, ',', '.') }} đ</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Phương thức thanh toán --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs space-y-4">
            <h3 class="font-bold text-slate-700 text-sm">Phương thức thanh toán</h3>
            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 has-checked:border-red-500 transition cursor-pointer">
                    <input type="radio" name="payment_method" value="cash" checked class="accent-red-500">
                    <span class="text-lg">💵</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Tiền mặt</p>
                        <p class="text-xs text-slate-400">Thanh toán khi nhận hàng</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 has-checked:border-red-500 transition cursor-pointer">
                    <input type="radio" name="payment_method" value="momo" class="accent-red-500">
                    <span class="text-lg">💳</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">MoMo</p>
                        <p class="text-xs text-slate-400">Ví điện tử MoMo</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 has-checked:border-red-500 transition cursor-pointer">
                    <input type="radio" name="payment_method" value="vnpay" class="accent-red-500">
                    <span class="text-lg">🏦</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">VNPAY</p>
                        <p class="text-xs text-slate-400">Thanh toán qua VNPAY</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Mã giảm giá --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-700 text-sm">Mã giảm giá</h3>
                <a href="{{ route('coupons') }}" target="_blank" class="text-xs text-red-500 hover:underline">Xem tất cả mã</a>
            </div>
            <div class="flex gap-2">
                <input type="text" name="coupon_code" id="coupon-input" value="{{ old('coupon_code') }}" placeholder="Nhập mã giảm giá" autocomplete="off"
                    class="flex-1 uppercase bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-red-400 focus:outline-hidden transition">
                <button type="button" id="coupon-apply"
                    class="px-5 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-700 transition cursor-pointer">Áp dụng</button>
            </div>
            <p id="coupon-msg" class="text-xs {{ $errors->has('coupon_code') ? 'text-red-500' : 'hidden' }}">{{ $errors->first('coupon_code') }}</p>

            @if ($coupons->isNotEmpty())
                <div class="flex flex-wrap gap-2 pt-1">
                    @foreach ($coupons as $c)
                        <button type="button" data-code="{{ $c->code }}" title="{{ $c->description }}"
                            class="coupon-chip px-3 py-1 rounded-full border border-dashed border-red-300 text-red-500 text-xs hover:bg-red-50 cursor-pointer">
                            {{ $c->code }} · {{ $c->badge }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Ghi chú --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs space-y-3">
            <h3 class="font-bold text-slate-700 text-sm">Ghi chú</h3>
            <textarea name="note" rows="2" placeholder="Ghi chú cho cửa hàng..."
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-red-400 focus:outline-hidden transition resize-none"></textarea>
        </div>

        <button type="submit"
            class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3.5 rounded-full transition shadow-lg shadow-red-100 text-sm cursor-pointer">
            Đặt Hàng — <span id="btn-total">{{ number_format($subtotal, 0, ',', '.') }} đ</span>
        </button>
    </form>

    {{-- ========== ORDER SUMMARY ========== --}}
    <div class="lg:col-span-2 lg:sticky lg:top-24 space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-700 text-sm">Đơn hàng ({{ collect($cart['items'])->sum('quantity') }} món)</h3>
            </div>
            <div class="divide-y divide-slate-50 max-h-80 overflow-y-auto">
                @foreach ($cart['items'] as $item)
                <div class="flex items-center gap-3 p-3">
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                        class="w-12 h-12 rounded-xl object-cover flex-shrink-0 bg-[#f6e6d6]">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $item['name'] }}</p>
                        <p class="text-xs text-slate-400">x{{ $item['quantity'] }}</p>
                    </div>
                    <p class="text-sm font-bold text-slate-800">{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} đ</p>
                </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-slate-100 space-y-1.5 text-sm">
                <div class="flex justify-between text-slate-500">
                    <span>Tạm tính</span>
                    <span>{{ number_format($subtotal, 0, ',', '.') }} đ</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Phí giao hàng</span>
                    <span id="sum-ship">0 đ</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Giảm giá</span>
                    <span id="sum-discount" class="text-green-500">0 đ</span>
                </div>
                <div class="flex justify-between font-bold text-slate-800 text-base pt-1.5 border-t border-slate-100">
                    <span>Tổng cộng</span>
                    <span id="sum-total" class="text-red-500">{{ number_format($subtotal, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>

        <p class="text-[11px] text-slate-400 text-center leading-relaxed">
            Bằng cách đặt hàng, bạn đồng ý với
            <a href="#" class="text-red-500 hover:underline">Điều khoản sử dụng</a>
            và
            <a href="#" class="text-red-500 hover:underline">Chính sách bảo mật</a>
            của ZomZop.
        </p>
    </div>

</div>

<script>
    const SUBTOTAL = {{ (int) $subtotal }};
    const FEES = @json($fees);
    const COUPON_URL = @json(route('checkout.coupon'));
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    let discount = 0;        // số tiền giảm của mã đang áp dụng
    let appliedCode = '';    // mã đã được máy chủ xác nhận

    const byId = id => document.getElementById(id);
    const fmt = n => Number(n).toLocaleString('vi-VN') + ' đ';
    const orderType = () => document.querySelector('input[name="type"]:checked').value;
    const speed = () => (document.querySelector('input[name="delivery_speed"]:checked') || {}).value || 'standard';
    const shipFee = () => orderType() === 'delivery' ? (FEES[speed()] || 0) : 0;

    function render() {
        const fee = shipFee();
        const total = Math.max(0, SUBTOTAL + fee - discount);
        byId('sum-ship').textContent = fee > 0 ? fmt(fee) : 'Miễn phí';
        byId('sum-discount').textContent = discount > 0 ? '-' + fmt(discount) : '0 đ';
        byId('sum-total').textContent = fmt(total);
        byId('btn-total').textContent = fmt(total);
    }

    function showMsg(text, ok) {
        const el = byId('coupon-msg');
        el.textContent = text;
        el.classList.toggle('hidden', !text);
        el.classList.toggle('text-green-600', ok);
        el.classList.toggle('text-red-500', !ok);
    }

    async function applyCoupon(silent) {
        const code = byId('coupon-input').value.trim();
        if (!code) { discount = 0; appliedCode = ''; showMsg('', true); render(); return; }
        try {
            const res = await fetch(COUPON_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ code, type: orderType(), delivery_speed: speed() }),
            });
            const data = await res.json();
            discount = data.ok ? data.discount : 0;
            appliedCode = data.ok ? code : '';
            showMsg(data.message || 'Không áp dụng được mã này.', data.ok);
        } catch (e) {
            discount = 0; appliedCode = '';
            showMsg('Không kiểm tra được mã, vui lòng thử lại.', false);
        }
        render();
    }

    // Đổi loại đơn / tốc độ giao: cập nhật phí; nếu đang có mã thì kiểm tra lại (mã ship chỉ hợp lệ với một số loại giao)
    function onShippingChange() {
        byId('delivery-address-group').classList.toggle('hidden', orderType() !== 'delivery');
        render();
        if (byId('coupon-input').value.trim()) applyCoupon(true);
    }
    document.querySelectorAll('input[name="type"], input[name="delivery_speed"]').forEach(el => el.addEventListener('change', onShippingChange));

    byId('coupon-apply').addEventListener('click', () => applyCoupon(false));
    byId('coupon-input').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); applyCoupon(false); } });
    document.querySelectorAll('.coupon-chip').forEach(b => b.addEventListener('click', () => { byId('coupon-input').value = b.dataset.code; applyCoupon(false); }));

    const saved = byId('saved-address');
    if (saved) saved.addEventListener('change', () => { if (saved.value) byId('delivery-address-input').value = saved.value; });

    // Trạng thái ban đầu (kể cả khi trang được mở lại sau lỗi)
    byId('delivery-address-group').classList.toggle('hidden', orderType() !== 'delivery');
    render();
    if (byId('coupon-input').value.trim()) applyCoupon(true);
</script>

@endsection
