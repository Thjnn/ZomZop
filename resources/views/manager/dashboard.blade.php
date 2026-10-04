@extends('layouts.manager')

@section('title', 'Tổng quan')

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp

    <h1 class="text-xl font-bold mb-1">Tổng quan</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $branch->name }} · {{ now()->format('d/m/Y') }}</p>

    {{-- 4 ô số liệu hôm nay --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Doanh thu hôm nay', $money($today['revenue']), 'text-green-600'],
            ['Tổng đơn hôm nay', $today['orders'], 'text-slate-800'],
            ['Chờ xác nhận', $today['pending'], 'text-amber-600'],
            ['Đã huỷ', $today['cancelled'], 'text-slate-500'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-2xl p-4 border border-slate-100">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1 {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Doanh thu 7 ngày: thanh div, cao theo % ngày lớn nhất --}}
        <section class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100">
            <h2 class="font-semibold mb-4">Doanh thu 7 ngày gần nhất</h2>
            @php $max = max(1, max($revenue7)); @endphp
            <div class="flex items-end gap-2 h-48">
                @foreach ($revenue7 as $day => $value)
                    <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ $money($value) }}">
                        <div class="w-full rounded-t-md bg-red-400" style="height: {{ round($value / $max * 100) }}%"></div>
                        <span class="text-[10px] text-slate-400 mt-1">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Món bán chạy hôm nay --}}
        <section class="bg-white rounded-2xl p-5 border border-slate-100">
            <h2 class="font-semibold mb-4">Món bán chạy hôm nay</h2>
            @forelse ($topItems as $i => $item)
                <div class="flex items-center justify-between py-2 text-sm {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <span class="truncate">{{ $i + 1 }}. {{ $item->name }}</span>
                    <span class="text-slate-500 whitespace-nowrap">{{ $item->qty }} phần</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Chưa có đơn hoàn thành hôm nay.</p>
            @endforelse
        </section>
    </div>

    {{-- Đơn đang chờ xác nhận --}}
    <section class="bg-white rounded-2xl p-5 border border-slate-100 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Đơn chờ xác nhận</h2>
            @if (Route::has('manager.orders.index'))
                <a href="{{ route('manager.orders.index', ['status' => 'pending']) }}" class="text-sm text-red-500 hover:underline">Xem tất cả →</a>
            @endif
        </div>
        @forelse ($pendingOrders as $order)
            <div class="flex items-center justify-between py-2 text-sm {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                <div class="min-w-0">
                    <p class="font-semibold">{{ $order->order_code }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $order->user?->name }} · {{ $order->created_at->format($order->created_at->isToday() ? 'H:i' : 'H:i d/m') }} · {{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}</p>
                </div>
                <span class="font-semibold whitespace-nowrap">{{ $money($order->total) }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">Không có đơn nào đang chờ. 🎉</p>
        @endforelse
    </section>
@endsection
