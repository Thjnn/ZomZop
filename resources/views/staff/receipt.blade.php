@php
    $money = fn ($v) => number_format((int) $v, 0, ',', '.') . ' đ';
    $type  = \App\Models\Order::TYPE_LABELS[$order->type] ?? $order->type;
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn {{ $order->order_code }}</title>
    {{-- CSS viết tay, không qua Vite: hóa đơn phải in đúng kể cả khi chưa build lại asset --}}
    <style>
        @page { size: {{ $paper }}mm auto; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f5f9; color: #000; font: 12px/1.4 "Courier New", ui-monospace, monospace; }
        .toolbar { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; padding: 12px; font-family: system-ui, sans-serif; }
        .toolbar a, .toolbar button { border: 1px solid #e2e8f0; background: #fff; border-radius: 12px; padding: 10px 16px; font-size: 14px; font-weight: 600; color: #334155; text-decoration: none; cursor: pointer; }
        .toolbar .primary { background: #ef4444; border-color: #ef4444; color: #fff; }
        .toolbar .active { border-color: #ef4444; color: #ef4444; }
        .receipt { width: {{ $paper }}mm; margin: 0 auto 24px; background: #fff; padding: 4mm {{ $paper === 58 ? 2 : 4 }}mm; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .center { text-align: center; }
        .logo { width: {{ $paper === 58 ? 14 : 18 }}mm; height: auto; filter: grayscale(1); }
        .brand { font-size: {{ $paper === 58 ? 15 : 17 }}px; font-weight: 700; margin: 2px 0 0; }
        .muted { font-size: 11px; }
        .title { font-weight: 700; font-size: 14px; margin: 6px 0 2px; letter-spacing: .5px; }
        .sep { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; gap: 6px; }
        .row > :last-child { text-align: right; white-space: nowrap; }
        table { width: 100%; border-collapse: collapse; }
        th { font-weight: 700; text-align: left; border-bottom: 1px solid #000; padding: 2px 0; }
        td { vertical-align: top; padding: 2px 0; }
        .num { text-align: right; white-space: nowrap; }
        .item-name { font-weight: 700; }
        .note { font-style: italic; font-size: 11px; }
        .total { font-size: 15px; font-weight: 700; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .receipt { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    @unless ($embed)
        <div class="toolbar">
            <button type="button" class="primary" onclick="window.print()">🖨 In hóa đơn</button>
            <a href="{{ request()->fullUrlWithQuery(['paper' => 80]) }}" class="{{ $paper === 80 ? 'active' : '' }}">Khổ 80mm</a>
            <a href="{{ request()->fullUrlWithQuery(['paper' => 58]) }}" class="{{ $paper === 58 ? 'active' : '' }}">Khổ 58mm</a>
            <button type="button" onclick="window.close()">Đóng</button>
        </div>
    @endunless

    <div class="receipt" id="receipt">
        <div class="center">
            <img src="{{ asset('images/avatar-logo.png') }}" alt="" class="logo">
            <p class="brand">{{ $brand }}</p>
            <p class="muted" style="margin:0">{{ $order->branch?->name }}</p>
            @if ($order->branch?->address)<p class="muted" style="margin:0">{{ $order->branch->address }}</p>@endif
            <p class="muted" style="margin:0">ĐT: {{ $order->branch?->phone ?: $hotline }}</p>
            <p class="title">HÓA ĐƠN BÁN HÀNG</p>
        </div>
        <hr class="sep">

        <div class="row"><span>Mã đơn:</span><strong>{{ $order->order_code }}</strong></div>
        <div class="row"><span>Ngày giờ:</span><span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Thu ngân:</span><span>{{ $order->staff?->name ?? '—' }}</span></div>
        <div class="row"><span>Loại đơn:</span><span>{{ $type }}@if ($order->table_number) · Bàn {{ $order->table_number }}@endif</span></div>
        @if ($order->customer_name || $order->customer_phone)
            <div class="row"><span>Khách:</span><span>{{ trim($order->customer_name . ' ' . $order->customer_phone) }}</span></div>
        @endif
        @if ($order->delivery_address)
            <div>Giao tới: {{ $order->delivery_address }}</div>
        @endif
        <hr class="sep">

        <table>
            <thead><tr><th>Món</th><th class="num">SL</th>@if ($paper === 80)<th class="num">Đ.giá</th>@endif<th class="num">T.tiền</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td class="item-name" @if ($paper === 58) colspan="3" @else colspan="4" @endif>{{ $item->name_snapshot }}</td>
                    </tr>
                    <tr>
                        {{-- Khổ 58mm hẹp: đơn giá đi kèm số lượng thay vì cột riêng --}}
                        <td>@if ($paper === 58){{ $money($item->price_snapshot) }}@endif</td>
                        <td class="num">{{ $item->quantity }}</td>
                        @if ($paper === 80)<td class="num">{{ $money($item->price_snapshot) }}</td>@endif
                        <td class="num">{{ $money($item->subtotal) }}</td>
                    </tr>
                    @foreach ($item->toppings ?? [] as $t)
                        @php $tq = \App\Services\BranchMenu::toppingQty($t); @endphp
                        <tr><td colspan="4">&nbsp;+ {{ $tq }}× {{ $t['name'] }} ({{ $tq }} × {{ $money($t['price']) }} = {{ $money($tq * $t['price']) }})</td></tr>
                    @endforeach
                    @if ($item->note)
                        <tr><td class="note" colspan="4">↳ {{ $item->note }}</td></tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        @if ($order->note)
            <p class="note" style="margin:4px 0 0">Ghi chú đơn: {{ $order->note }}</p>
        @endif
        <hr class="sep">

        <div class="row"><span>Tạm tính</span><span>{{ $money($order->subtotal) }}</span></div>
        <div class="row"><span>Giảm giá</span><span>{{ $order->discount ? '-' . $money($order->discount) : $money(0) }}</span></div>
        @if ($order->shipping_fee)
            <div class="row"><span>Phí giao hàng</span><span>{{ $money($order->shipping_fee) }}</span></div>
        @endif
        <div class="row total"><span>TỔNG CỘNG</span><span>{{ $money($order->total) }}</span></div>
        <hr class="sep">
        <div class="row"><span>Thanh toán</span><span>{{ \App\Support\PaymentMethods::label($order->payment_method) }}</span></div>
        @if ($order->cash_received !== null)
            <div class="row"><span>Tiền khách đưa</span><span>{{ $money($order->cash_received) }}</span></div>
            <div class="row"><span>Tiền thừa</span><span>{{ $money($order->change_amount) }}</span></div>
        @endif
        @if ($order->isCancelled())
            <p class="center title">*** ĐƠN ĐÃ HỦY ***</p>
        @endif
        <hr class="sep">
        <p class="center" style="margin:4px 0 0;font-weight:700">Cảm ơn quý khách, hẹn gặp lại!</p>
        <p class="center muted" style="margin:2px 0 0">Hotline: {{ $hotline }}</p>
    </div>

    <script>
        // Mở là in luôn; đợi ảnh logo tải xong để không in ra khung trống
        window.addEventListener('load', () => setTimeout(() => window.print(), 100));
    </script>
</body>
</html>
