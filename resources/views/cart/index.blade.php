@extends('layouts.app')

@section('title', 'Giỏ Hàng — ZomZop')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Giỏ Hàng 🛒</h1>
            <p class="text-sm text-slate-500 mt-1">
                Chi nhánh: <strong class="text-red-500">{{ $branchName }}</strong>
                &middot; <a href="{{ route('branches.select') }}" class="text-red-500 hover:underline text-xs">Đổi</a>
            </p>
        </div>
        <a href="{{ route('home') }}" class="text-xs font-semibold text-red-500 hover:underline">← Tiếp tục mua sắm</a>
    </div>

    @if (empty($items))

    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="text-7xl mb-4">🛒</div>
        <h2 class="text-xl font-bold text-slate-700 mb-2">Giỏ hàng trống</h2>
        <p class="text-slate-400 text-sm mb-6">Hãy thêm món ăn vào giỏ để đặt hàng</p>
        <a href="{{ route('home') }}"
            class="bg-red-500 hover:bg-red-600 text-white font-semibold px-8 py-3 rounded-full transition">
            Khám Phá Ngay
        </a>
    </div>

    @else

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="divide-y divide-slate-100">
            @php $total = 0; @endphp
            @foreach ($items as $index => $item)
            @php
                $itemTotal = $item['price'] * $item['quantity'];
                $total += $itemTotal;
            @endphp
            <div class="flex items-center gap-4 p-4 cart-item" data-item-id="{{ $item['id'] }}">
                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                    class="w-20 h-20 rounded-2xl object-cover flex-shrink-0 bg-[#f6e6d6]">

                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-slate-800 text-sm">{{ $item['name'] }}</h4>
                    @if ($item['note'])
                    <p class="text-xs text-slate-400 mt-0.5">📝 {{ $item['note'] }}</p>
                    @endif
                    <p class="text-sm font-bold text-red-500 mt-1">{{ number_format($item['price'], 0, ',', '.') }} đ</p>
                </div>

                <div class="flex items-center gap-2">
                    <button class="btn-cart-minus w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-500 transition cursor-pointer font-bold text-sm">−</button>
                    <span class="cart-qty text-sm font-bold text-slate-800 w-5 text-center">{{ $item['quantity'] }}</span>
                    <button class="btn-cart-plus w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-green-100 hover:text-green-500 transition cursor-pointer font-bold text-sm">+</button>
                </div>

                <p class="w-24 text-right font-bold text-slate-800 cart-item-total">{{ number_format($itemTotal, 0, ',', '.') }} đ</p>

                <button class="btn-cart-remove w-8 h-8 flex items-center justify-center rounded-full text-slate-300 hover:bg-red-50 hover:text-red-500 transition cursor-pointer" aria-label="Xóa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endforeach
        </div>

        <div class="border-t border-slate-100 p-4 flex items-center justify-between bg-slate-50/50">
            <div class="text-sm text-slate-500">
                Tổng cộng <span class="cart-count-badge">{{ collect($items)->sum('quantity') }}</span> món
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400">Tạm tính:</span>
                <span id="cart-subtotal" class="text-xl font-bold text-slate-800 ml-2">{{ number_format($total, 0, ',', '.') }} đ</span>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('checkout.index') }}"
            class="bg-red-500 hover:bg-red-600 text-white font-semibold px-10 py-3.5 rounded-full transition shadow-lg shadow-red-100 text-sm">
            Tiến Hành Đặt Hàng →
        </a>
    </div>

    @endif
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    function syncCart() {
        const items = [];
        document.querySelectorAll('.cart-item').forEach(row => {
            items.push({
                id: row.dataset.itemId,
                qty: parseInt(row.querySelector('.cart-qty').textContent),
            });
        });
        let totalQty = 0, totalPrice = 0;
        items.forEach(i => { totalQty += i.qty; });
        document.querySelectorAll('.cart-item .cart-item-total').forEach(el => {
            totalPrice += parseInt(el.textContent.replace(/[^0-9]/g, ''));
        });
        const badge = document.getElementById('cart-badge');
        if (badge) badge.textContent = totalQty;
        document.getElementById('cart-subtotal').textContent =
            new Intl.NumberFormat('vi-VN').format(totalPrice) + ' đ';
        const cb = document.querySelector('.cart-count-badge');
        if (cb) cb.textContent = totalQty;
    }

    function callApi(route, body, onSuccess) {
        fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify(body),
        })
        .then(r => r.json())
        .then(data => { if (data.success && onSuccess) onSuccess(data); });
    }

    document.querySelectorAll('.cart-item').forEach(row => {
        const itemId = row.dataset.itemId;
        const qtyEl = row.querySelector('.cart-qty');
        const priceEl = row.querySelector('.cart-item-total');
        const unitPrice = parseInt(priceEl.textContent.replace(/[^0-9]/g, '')) / parseInt(qtyEl.textContent);

        function updateQty(newQty) {
            qtyEl.textContent = newQty;
            priceEl.textContent = new Intl.NumberFormat('vi-VN').format(unitPrice * newQty) + ' đ';
            syncCart();
            callApi('{{ route("cart.update") }}', { menu_item_id: itemId, quantity: newQty });
        }

        row.querySelector('.btn-cart-minus').addEventListener('click', () => {
            let q = parseInt(qtyEl.textContent);
            if (q <= 1) { removeItem(); return; }
            updateQty(q - 1);
        });

        row.querySelector('.btn-cart-plus').addEventListener('click', () => {
            let q = parseInt(qtyEl.textContent);
            if (q >= 99) return;
            updateQty(q + 1);
        });

        function removeItem() {
            callApi('{{ route("cart.remove") }}', { menu_item_id: itemId }, (data) => {
                const badge = document.getElementById('cart-badge');
                if (badge) badge.textContent = data.count;
                row.remove();
                if (data.isEmpty) { location.reload(); return; }
                syncCart();
            });
        }

        row.querySelector('.btn-cart-remove').addEventListener('click', removeItem);
    });
});
</script>
@endpush
