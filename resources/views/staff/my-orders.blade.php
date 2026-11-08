@extends('layouts.staff')

@section('title', 'Của tôi · Đơn hôm nay')

@section('content')
@php
    $money = fn ($v) => number_format((int) $v, 0, ',', '.') . ' đ';
    // Đơn POS tạo ra đã "Đã xác nhận" nên không bao giờ ở "Chờ xác nhận"
    $filters = collect($labels)->except('pending');
@endphp

@include('staff.partials.me-tabs')

<div class="mb-5">
    <h1 class="text-xl font-bold">Đơn tôi bán hôm nay</h1>
    <p class="text-sm text-slate-500">{{ today()->format('d/m/Y') }} · {{ auth()->user()->branch?->name }}</p>
</div>

{{-- Thống kê nhanh --}}
<section class="grid grid-cols-2 gap-4 mb-5">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-sm text-slate-500">Số đơn</p>
        <p class="text-3xl font-bold mt-1">{{ $stats['orders'] }}</p>
        @if ($stats['cancelled'])
            <p class="text-xs text-slate-400 mt-1">Không tính {{ $stats['cancelled'] }} đơn đã hủy</p>
        @endif
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-sm text-slate-500">Doanh thu</p>
        <p class="text-3xl font-bold mt-1 text-red-500">{{ $money($stats['revenue']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Tổng tiền các đơn chưa hủy</p>
    </div>
</section>

{{-- Lọc theo trạng thái --}}
<nav class="flex flex-wrap gap-2 mb-4" aria-label="Lọc theo trạng thái">
    <a href="{{ route('staff.me.orders') }}"
       class="h-10 inline-flex items-center gap-1.5 px-4 rounded-full text-sm font-semibold {{ $status === null ? 'bg-red-500 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-red-300' }}">
        Tất cả <span class="opacity-75">{{ $counts->sum() }}</span>
    </a>
    @foreach ($filters as $key => $label)
        <a href="{{ route('staff.me.orders', ['status' => $key]) }}"
           class="h-10 inline-flex items-center gap-1.5 px-4 rounded-full text-sm font-semibold {{ $status === $key ? 'bg-red-500 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-red-300' }}">
            {{ $label }} <span class="opacity-75">{{ $counts[$key] ?? 0 }}</span>
        </a>
    @endforeach
</nav>

{{-- Danh sách đơn: bấm vào dòng để mở chi tiết món --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="hidden md:grid grid-cols-[1.4fr_.6fr_1fr_1fr_1fr_1fr_1.5rem] gap-3 px-4 py-3 text-xs font-semibold text-slate-400 border-b border-slate-100">
        <span>Mã đơn</span><span>Giờ</span><span>Loại đơn</span><span class="text-right">Tổng tiền</span><span>Thanh toán</span><span>Trạng thái</span><span></span>
    </div>

    @forelse ($orders as $order)
        <details class="group border-b border-slate-50 last:border-0">
            <summary class="grid grid-cols-2 md:grid-cols-[1.4fr_.6fr_1fr_1fr_1fr_1fr_1.5rem] gap-x-3 gap-y-1 items-center px-4 py-3.5 cursor-pointer list-none hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                <span class="font-mono font-semibold">{{ $order->order_code }}</span>
                <span class="text-sm text-slate-500 md:text-slate-800 text-right md:text-left">{{ $order->created_at->format('H:i') }}</span>
                <span class="text-sm">{{ \App\Models\Order::TYPE_LABELS[$order->type] ?? $order->type }}@if ($order->table_number) · Bàn {{ $order->table_number }}@endif</span>
                <span class="font-bold text-right">{{ $money($order->total) }}</span>
                <span class="text-sm">{{ \App\Support\PaymentMethods::label($order->payment_method) }}</span>
                <span class="text-right md:text-left">@include('manager.partials.status-badge', ['status' => $order->status])</span>
                <span class="hidden md:block text-slate-400 transition group-open:rotate-180" aria-hidden="true">▾</span>
            </summary>

            <div class="px-4 pb-4 bg-slate-50/60">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-slate-400">
                        <tr><th class="py-2 font-medium">Món</th><th class="py-2 font-medium text-right">SL</th><th class="py-2 font-medium text-right">Đơn giá</th><th class="py-2 font-medium text-right">Thành tiền</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-2">
                                    {{ $item->name_snapshot }}
                                    @if ($item->topping_names)<span class="block text-xs text-red-600">{{ $item->topping_names }}</span>@endif
                                    @if ($item->note)<span class="block text-xs text-slate-500">Ghi chú: {{ $item->note }}</span>@endif
                                </td>
                                <td class="py-2 text-right">{{ $item->quantity }}</td>
                                <td class="py-2 text-right">{{ $money($item->price_snapshot) }}</td>
                                <td class="py-2 text-right font-semibold">{{ $money($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex flex-wrap items-end justify-between gap-3 mt-3 pt-3 border-t border-slate-200">
                    <div class="text-sm text-slate-600 space-y-0.5">
                        @if ($order->discount)<p>Giảm giá: −{{ $money($order->discount) }}</p>@endif
                        @if ($order->cash_received !== null)
                            <p>Khách đưa {{ $money($order->cash_received) }} · Tiền thừa {{ $money($order->change_amount) }}</p>
                        @endif
                        @if ($order->customer_name || $order->customer_phone)
                            <p>Khách: {{ trim($order->customer_name . ' ' . $order->customer_phone) }}</p>
                        @endif
                        @if ($order->delivery_address)<p>Giao tới: {{ $order->delivery_address }}</p>@endif
                        @if ($order->note)<p>Ghi chú đơn: {{ $order->note }}</p>@endif
                    </div>
                    <a href="{{ route('staff.pos.orders.receipt', $order) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center h-11 px-5 rounded-xl bg-red-500 text-white font-semibold shadow-sm hover:bg-red-600">🖨 In lại hóa đơn</a>
                </div>
            </div>
        </details>
    @empty
        <p class="px-4 py-12 text-center text-slate-400">
            {{ $status ? 'Không có đơn "' . $labels[$status] . '" nào hôm nay.' : 'Hôm nay bạn chưa bán đơn nào.' }}
        </p>
    @endforelse
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
