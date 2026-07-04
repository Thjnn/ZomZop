@extends('layouts.app')

@section('title', 'Thanh Toán — ZomZop')

@section('content')

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
                    <input type="radio" name="type" value="takeaway" checked class="peer hidden">
                    <div class="border-2 border-slate-200 peer-checked:border-red-500 rounded-xl p-4 text-center transition hover:border-red-300">
                        <span class="text-2xl">🏃</span>
                        <p class="text-sm font-semibold text-slate-700 mt-1">Mang đi</p>
                        <p class="text-xs text-slate-400">Tự đến lấy tại cửa hàng</p>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="type" value="delivery" class="peer hidden">
                    <div class="border-2 border-slate-200 peer-checked:border-red-500 rounded-xl p-4 text-center transition hover:border-red-300">
                        <span class="text-2xl">🛵</span>
                        <p class="text-sm font-semibold text-slate-700 mt-1">Giao hàng</p>
                        <p class="text-xs text-slate-400">Giao tận nơi</p>
                    </div>
                </label>
            </div>

            <div id="delivery-address-group" class="hidden">
                <label class="text-xs font-semibold text-slate-600 mb-1 block">Địa chỉ giao hàng *</label>
                <input type="text" name="delivery_address" placeholder="Nhập địa chỉ nhận hàng..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-red-400 focus:outline-hidden transition">
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

        {{-- Ghi chú --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs space-y-3">
            <h3 class="font-bold text-slate-700 text-sm">Ghi chú</h3>
            <textarea name="note" rows="2" placeholder="Ghi chú cho cửa hàng..."
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-red-400 focus:outline-hidden transition resize-none"></textarea>
        </div>

        <button type="submit"
            class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3.5 rounded-full transition shadow-lg shadow-red-100 text-sm cursor-pointer">
            Đặt Hàng — {{ number_format($subtotal, 0, ',', '.') }} đ
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
                    <span>Giảm giá</span>
                    <span class="text-green-500">0 đ</span>
                </div>
                <div class="flex justify-between font-bold text-slate-800 text-base pt-1.5 border-t border-slate-100">
                    <span>Tổng cộng</span>
                    <span class="text-red-500">{{ number_format($subtotal, 0, ',', '.') }} đ</span>
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
    document.querySelectorAll('input[name="type"]').forEach(el => {
        el.addEventListener('change', () => {
            const addrGroup = document.getElementById('delivery-address-group');
            addrGroup.classList.toggle('hidden', el.value !== 'delivery');
        });
    });
</script>

@endsection
