@extends('layouts.admin')

@section('title', 'Đơn ' . $order->order_code)

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-slate-500 hover:underline">← Đơn hàng</a>
    <div class="flex items-center gap-3 my-3">
        <h1 class="text-xl font-bold">Đơn {{ $order->order_code }}</h1>
        @include('manager.partials.status-badge', ['status' => $order->status])
    </div>

    <div class="grid lg:grid-cols-3 gap-6 text-sm">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-5">
            <table class="w-full">
                <thead class="text-left text-xs text-slate-400 border-b border-slate-100"><tr><th class="py-2">Món</th><th>SL</th><th class="text-right">Thành tiền</th></tr></thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-slate-50"><td class="py-2">{{ $item->name_snapshot }} @if ($item->note)<span class="text-xs text-slate-400">({{ $item->note }})</span>@endif</td><td>{{ $item->quantity }}</td><td class="text-right">{{ $money($item->subtotal) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3 space-y-1 text-right">
                <p>Tạm tính: {{ $money($order->subtotal) }}</p>
                <p>Giảm giá: -{{ $money($order->discount) }} @if ($order->coupon) <span class="font-mono text-xs">({{ $order->coupon->code }})</span> @endif</p>
                <p class="font-bold text-base">Tổng: {{ $money($order->total) }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-100 p-5 space-y-1">
                <p><span class="text-slate-400">Chi nhánh:</span> {{ $order->branch?->name }}</p>
                <p><span class="text-slate-400">Khách:</span> {{ $order->user?->name }} · {{ $order->user?->phone ?? $order->user?->email }}</p>
                <p><span class="text-slate-400">Hình thức:</span> {{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}</p>
                @if ($order->delivery_address) <p><span class="text-slate-400">Địa chỉ:</span> {{ $order->delivery_address }}</p> @endif
                <p><span class="text-slate-400">Thanh toán:</span> {{ strtoupper($order->payment_method) }} · {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}</p>
                @if ($order->note) <p><span class="text-slate-400">Ghi chú:</span> {{ $order->note }}</p> @endif
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 p-5">
                <h2 class="font-semibold mb-2">Lịch sử trạng thái</h2>
                <p class="text-xs text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }} — Khách đặt đơn</p>
                @foreach ($order->histories as $h)
                    <p class="text-xs mt-1"><span class="text-slate-400">{{ $h->created_at->format('d/m/Y H:i') }}</span> — {{ $labels[$h->to_status] ?? $h->to_status }} <span class="text-slate-400">bởi {{ $h->changedBy?->name ?? '—' }}</span>@if ($h->note): {{ $h->note }}@endif</p>
                @endforeach
            </div>
        </div>
    </div>
@endsection
