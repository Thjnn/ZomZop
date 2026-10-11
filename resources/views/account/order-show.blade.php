@extends('layouts.app')

@section('title', 'Đơn ' . $order->order_code . ' - ZomZop')

@section('content')
@php
    $labels = \App\Services\OrderStatusService::LABELS;
    $badge = [
        'pending'   => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'cooking'   => 'bg-orange-100 text-orange-700',
        'ready'     => 'bg-purple-100 text-purple-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-slate-200 text-slate-600',
    ];
    $money = fn ($n) => number_format((int) $n, 0, ',', '.') . ' đ';
    $isActive = !in_array($order->status, ['completed', 'cancelled'], true);

    // Mốc thời gian của từng bước: lấy từ lịch sử đổi trạng thái
    $steps   = ['pending', 'confirmed', 'cooking', 'ready', 'completed'];
    $reached = [];
    foreach ($order->histories as $h) { $reached[$h->to_status] = $h->created_at; }
    $reached['pending'] = $order->created_at;
    $cancelledAt = $reached['cancelled'] ?? null;
    $currentIdx  = array_search($order->status, $steps, true);
    $speedText   = $order->delivery_speed === 'express' ? 'Giao hỏa tốc' : 'Giao tiêu chuẩn';
@endphp

<div class="max-w-4xl mx-auto py-8 px-4 space-y-5">
    <div>
        <a href="{{ route('orders') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-red-500 transition mb-2">← Quay lại đơn hàng</a>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-slate-800">Đơn #{{ $order->order_code }}</h1>
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badge[$order->status] ?? 'bg-slate-100' }}" id="status-badge">
                {{ $labels[$order->status] ?? $order->status }}
            </span>
        </div>
        <p class="text-sm text-slate-400 mt-1">Đặt lúc {{ $order->created_at->format('d/m/Y H:i') }} · Chi nhánh {{ $order->branch->name ?? '' }}</p>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Theo dõi tiến trình --}}
    <section id="tracking" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Theo dõi đơn hàng</h2>
            @if ($isActive)
                <span class="text-xs text-slate-400" id="live-note">● Tự động cập nhật</span>
            @endif
        </div>

        @if ($order->status === 'cancelled')
            <div class="rounded-xl bg-slate-100 text-slate-600 px-4 py-3 text-sm">
                Đơn hàng đã bị hủy{{ $cancelledAt ? ' lúc ' . $cancelledAt->format('d/m/Y H:i') : '' }}.
                @php $why = $order->histories->firstWhere('to_status', 'cancelled'); @endphp
                @if ($why && $why->note) <span class="block text-xs mt-1">Ghi chú: {{ $why->note }}</span> @endif
            </div>
        @else
            <ol class="grid grid-cols-5 gap-1 text-center">
                @foreach ($steps as $i => $step)
                    @php $done = $currentIdx !== false && $i <= $currentIdx; @endphp
                    <li class="relative">
                        <div class="mx-auto w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
                                    {{ $done ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-400' }}">
                            {{ $done ? '✓' : $i + 1 }}
                        </div>
                        <p class="text-xs mt-2 font-medium {{ $done ? 'text-slate-800' : 'text-slate-400' }}">{{ $labels[$step] }}</p>
                        <p class="text-[11px] text-slate-400">{{ isset($reached[$step]) ? $reached[$step]->format('H:i d/m') : '' }}</p>
                    </li>
                @endforeach
            </ol>
            @if ($isActive)
                <p class="text-sm text-slate-500 mt-5 text-center">
                    @if ($order->isTakeaway())
                        Khi đơn ở trạng thái <b>Sẵn sàng</b>, hãy đến chi nhánh và đưa mã nhận món <b class="text-red-500">{{ $order->pickup_code }}</b>.
                    @else
                        Đơn sẽ được giao đến: <b>{{ $order->delivery_address }}</b>
                    @endif
                </p>
            @endif
        @endif
    </section>

    <div class="grid md:grid-cols-5 gap-5">
        {{-- Danh sách món --}}
        <section class="md:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">Món đã đặt ({{ $order->items->sum('quantity') }})</h2>
            <div class="divide-y divide-slate-50">
                @foreach ($order->items as $line)
                    <div class="flex items-center gap-3 py-3">
                        @if ($line->menuItem)
                            <img src="{{ $line->menuItem->image_url }}" alt="{{ $line->name_snapshot }}" class="w-16 h-16 rounded-xl object-cover bg-red-50 shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-red-50 flex items-center justify-center text-2xl shrink-0">🍔</div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800">{{ $line->name_snapshot }}</p>
                            <p class="text-xs text-slate-400">{{ $money($line->price_snapshot) }} × {{ $line->quantity }}</p>
                            @if ($line->note) <p class="text-xs text-amber-600">Ghi chú: {{ $line->note }}</p> @endif
                        </div>
                        <span class="text-sm font-semibold text-slate-700">{{ $money($line->subtotal) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100 text-sm space-y-1.5">
                <div class="flex justify-between text-slate-500"><span>Tạm tính</span><span>{{ $money($order->subtotal) }}</span></div>
                @if ($order->shipping_fee > 0)
                    <div class="flex justify-between text-slate-500"><span>Phí giao hàng ({{ $speedText }})</span><span>{{ $money($order->shipping_fee) }}</span></div>
                @endif
                @if ($order->discount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Giảm giá{{ $order->coupon ? ' (' . $order->coupon->code . ')' : '' }}</span>
                        <span>-{{ $money($order->discount) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold text-base text-slate-800 pt-1.5 border-t border-slate-100">
                    <span>Tổng cộng</span><span class="text-red-500">{{ $money($order->total) }}</span>
                </div>
            </div>
        </section>

        {{-- Thông tin đơn --}}
        <section class="md:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-sm space-y-3 h-fit">
            <h2 class="font-semibold text-slate-800">Thông tin đơn hàng</h2>
            <div class="flex justify-between"><span class="text-slate-500">Hình thức</span><span class="font-medium">{{ $order->isDelivery() ? $speedText : 'Mang đi' }}</span></div>
            @if ($order->isDelivery())
                <div><p class="text-slate-500">Địa chỉ giao</p><p class="font-medium">{{ $order->delivery_address }}</p></div>
            @else
                <div class="flex justify-between"><span class="text-slate-500">Mã nhận món</span><span class="font-bold text-red-500">{{ $order->pickup_code }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-slate-500">Thanh toán</span>
                <span class="font-medium">{{ match ($order->payment_method) { 'cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPAY', default => $order->payment_method } }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Tình trạng</span>
                <span class="font-medium {{ $order->isPaid() ? 'text-green-600' : 'text-amber-600' }}">{{ $order->isPaid() ? 'Đã thanh toán' : 'Chưa thanh toán' }}</span></div>
            @if ($order->note)
                <div><p class="text-slate-500">Ghi chú</p><p class="font-medium">{{ $order->note }}</p></div>
            @endif
        </section>
    </div>

    {{-- Đánh giá --}}
    @if ($order->review)
        <section id="review" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-2">Đánh giá của bạn</h2>
            <p class="text-yellow-500 text-lg">{{ str_repeat('★', $order->review->rating) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $order->review->rating) }}</span>
                <span class="text-sm text-slate-500 ml-1">Đơn hàng</span></p>
            @if ($order->review->delivery_rating)
                <p class="text-yellow-500 text-lg">{{ str_repeat('★', $order->review->delivery_rating) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $order->review->delivery_rating) }}</span>
                    <span class="text-sm text-slate-500 ml-1">Giao hàng</span></p>
            @endif
            @if ($order->review->comment) <p class="text-sm text-slate-600 mt-2">“{{ $order->review->comment }}”</p> @endif
            @if ($order->review->reply)
                <div class="mt-3 rounded-xl bg-red-50 p-3 text-sm">
                    <p class="font-semibold text-red-600 mb-0.5">ZomZop phản hồi</p>
                    <p class="text-slate-600">{{ $order->review->reply }}</p>
                </div>
            @endif
        </section>
    @endif

    {{-- Nút thao tác --}}
    <div class="flex flex-wrap gap-2 text-sm">
        @if (in_array($order->status, ['pending', 'confirmed'], true))
            <form method="POST" action="{{ route('orders.cancel', $order->order_code) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?')">
                @csrf
                <button class="px-5 py-2.5 rounded-xl border border-red-200 text-red-500 hover:bg-red-50">Hủy đơn</button>
            </form>
        @endif
        @if (!$isActive)
            <form method="POST" action="{{ route('orders.reorder', $order->order_code) }}" onsubmit="return confirm('Giỏ hàng hiện tại sẽ được thay bằng các món của đơn này. Tiếp tục?')">
                @csrf
                <button class="px-5 py-2.5 rounded-xl bg-red-500 text-white hover:bg-red-600">Đặt lại đơn này</button>
            </form>
        @endif
        @if ($order->isCompleted() && !$order->review)
            <a href="{{ route('orders.review', $order->order_code) }}" class="px-5 py-2.5 rounded-xl border border-yellow-300 text-yellow-600 hover:bg-yellow-50">★ Đánh giá đơn hàng</a>
        @endif
    </div>
</div>

@if ($isActive)
<script>
    // Tự làm mới khi cửa hàng đổi trạng thái đơn (kiểm tra mỗi 15 giây)
    (function () {
        const current = @json($order->status);
        const url = @json(route('orders.status', $order->order_code));
        setInterval(async () => {
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status !== current) window.location.reload();
            } catch (e) { /* mất mạng thì bỏ qua, lần sau thử lại */ }
        }, 15000);
    })();
</script>
@endif
@endsection
