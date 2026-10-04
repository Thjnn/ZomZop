@extends('layouts.manager')

@section('title', 'Đơn ' . $order->order_code)

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        // Chữ trên nút cho từng bước tiếp theo (trừ huỷ, huỷ có form riêng)
        $actionText = [
            'confirmed' => 'Xác nhận đơn',
            'cooking'   => 'Bắt đầu nấu',
            'ready'     => 'Đã nấu xong',
            'completed' => 'Hoàn thành / đã giao',
        ];
    @endphp

    <a href="{{ route('manager.orders.index') }}" class="text-sm text-slate-500 hover:text-red-500">← Danh sách đơn</a>

    <div class="flex flex-wrap items-center gap-3 mt-2 mb-6">
        <h1 class="text-xl font-bold">{{ $order->order_code }}</h1>
        @include('manager.partials.status-badge', ['status' => $order->status])
        <span class="text-sm text-slate-500">Mã lấy hàng: <b>{{ $order->pickup_code }}</b></span>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Món trong đơn --}}
            <section class="bg-white rounded-2xl p-5 border border-slate-100">
                <h2 class="font-semibold mb-3">Món ({{ $order->items->sum('quantity') }})</h2>
                @foreach ($order->items as $item)
                    <div class="flex justify-between py-2 text-sm border-b border-slate-50">
                        <div>
                            <p>{{ $item->quantity }} × {{ $item->name_snapshot }}</p>
                            @if ($item->note) <p class="text-xs text-amber-600">Ghi chú: {{ $item->note }}</p> @endif
                        </div>
                        <span class="whitespace-nowrap">{{ $money($item->subtotal) }}</span>
                    </div>
                @endforeach
                <div class="text-sm mt-3 space-y-1">
                    <div class="flex justify-between"><span class="text-slate-500">Tạm tính</span><span>{{ $money($order->subtotal) }}</span></div>
                    @if ($order->discount > 0)
                        <div class="flex justify-between"><span class="text-slate-500">Giảm giá</span><span>-{{ $money($order->discount) }}</span></div>
                    @endif
                    <div class="flex justify-between font-bold text-base"><span>Tổng</span><span class="text-red-500">{{ $money($order->total) }}</span></div>
                </div>
            </section>

            {{-- Lịch sử trạng thái --}}
            <section class="bg-white rounded-2xl p-5 border border-slate-100">
                <h2 class="font-semibold mb-3">Lịch sử</h2>
                <p class="text-sm text-slate-500">{{ $order->created_at->format('H:i d/m/Y') }} · Khách đặt đơn</p>
                @foreach ($order->histories as $h)
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $h->created_at->format('H:i d/m/Y') }} · {{ $labels[$h->to_status] ?? $h->to_status }}
                        bởi {{ $h->changedBy?->name }}
                        @if ($h->note) — <i>{{ $h->note }}</i> @endif
                    </p>
                @endforeach
            </section>
        </div>

        <div class="space-y-6">
            {{-- Thao tác --}}
            <section class="bg-white rounded-2xl p-5 border border-slate-100">
                <h2 class="font-semibold mb-3">Thao tác</h2>
                @forelse (array_diff($next, ['cancelled']) as $to)
                    <form method="POST" action="{{ route('manager.orders.status', $order) }}" class="mb-2">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $to }}">
                        <button class="w-full px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">
                            {{ $actionText[$to] ?? $labels[$to] }}
                        </button>
                    </form>
                @empty
                    @if (!in_array('cancelled', $next))
                        <p class="text-sm text-slate-400">Đơn đã kết thúc, không còn thao tác.</p>
                    @endif
                @endforelse

                @if (in_array('cancelled', $next))
                    <form method="POST" action="{{ route('manager.orders.status', $order) }}" class="mt-4 pt-4 border-t border-slate-100">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <input type="text" name="note" value="{{ old('note') }}" placeholder="Lý do huỷ (bắt buộc)" maxlength="255"
                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm mb-2">
                        <button class="w-full px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm cursor-pointer">Huỷ đơn</button>
                    </form>
                @endif
            </section>

            {{-- Khách hàng --}}
            <section class="bg-white rounded-2xl p-5 border border-slate-100 text-sm space-y-1">
                <h2 class="font-semibold mb-2">Khách hàng</h2>
                <p>{{ $order->user?->name }}</p>
                <p class="text-slate-500">{{ $order->user?->phone ?? 'Chưa có SĐT' }}</p>
                <p class="text-slate-500">{{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}</p>
                @if ($order->delivery_address) <p class="text-slate-500">📍 {{ $order->delivery_address }}</p> @endif
                <p class="text-slate-500">Thanh toán: {{ strtoupper($order->payment_method) }} · {{ $order->payment_status === 'paid' ? 'Đã trả' : 'Chưa trả' }}</p>
                @if ($order->note) <p class="text-amber-600">Ghi chú: {{ $order->note }}</p> @endif
            </section>
        </div>
    </div>
@endsection
