@extends('layouts.staff')

@section('title', 'Đơn đã bán')

@section('content')
@php $money = fn ($v) => number_format((int) $v, 0, ',', '.') . ' đ'; @endphp
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 class="text-xl font-bold">Đơn đã bán tại quầy</h1>
        <p class="text-sm text-slate-500">{{ auth()->user()->branch?->name }} · {{ $date->format('d/m/Y') }} · {{ $orders->total() }} đơn</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}"
               class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:border-red-400">
        <button class="h-11 px-4 rounded-xl bg-red-500 text-white text-sm font-semibold cursor-pointer hover:bg-red-600">Xem</button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3">Mã đơn</th>
                <th class="px-4 py-3">Giờ</th>
                <th class="px-4 py-3">Loại đơn</th>
                <th class="px-4 py-3 text-right">Số món</th>
                <th class="px-4 py-3 text-right">Tổng</th>
                <th class="px-4 py-3">Thanh toán</th>
                <th class="px-4 py-3">Trạng thái</th>
                <th class="px-4 py-3">Thu ngân</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse ($orders as $order)
                <tr>
                    <td class="px-4 py-3 font-mono font-semibold">{{ $order->order_code }}</td>
                    <td class="px-4 py-3">{{ $order->created_at->format('H:i') }}</td>
                    <td class="px-4 py-3">{{ \App\Models\Order::TYPE_LABELS[$order->type] ?? $order->type }}@if ($order->table_number) · Bàn {{ $order->table_number }}@endif</td>
                    <td class="px-4 py-3 text-right">{{ (int) $order->items_sum_quantity }}</td>
                    <td class="px-4 py-3 text-right font-bold">{{ $money($order->total) }}</td>
                    <td class="px-4 py-3">{{ \App\Support\PaymentMethods::label($order->payment_method) }}</td>
                    <td class="px-4 py-3">@include('manager.partials.status-badge', ['status' => $order->status])</td>
                    <td class="px-4 py-3">{{ $order->staff?->name }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('staff.pos.orders.receipt', $order) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center h-10 px-4 rounded-xl border border-slate-200 font-semibold text-slate-700 whitespace-nowrap hover:border-red-400 hover:text-red-500">🖨 In lại hóa đơn</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-slate-400">Chưa có đơn nào trong ngày này.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
