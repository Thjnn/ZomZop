@extends('layouts.manager')

@section('title', 'Tổng quan')

@push('scripts')
    @vite('resources/js/manager-dashboard.js')
@endpush

@section('content')
    @php
        $money  = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $labels = \App\Services\OrderStatusService::LABELS;
        // Màu khớp partials/status-badge + biểu đồ donut
        $colors = [
            'pending'   => '#f59e0b',
            'confirmed' => '#3b82f6',
            'cooking'   => '#f97316',
            'ready'     => '#a855f7',
            'completed' => '#22c55e',
            'cancelled' => '#94a3b8',
        ];
        $icons = [
            'pending'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'confirmed' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
            'cooking'   => '<path d="M12 3c1 3 4 4.5 4 8.5a4 4 0 0 1-8 0c0-2 1-3 2-4 0 2 1 3 2 3 0-3-1-5 0-7.5Z"/><path d="M5 21h14"/>',
            'ready'     => '<path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        ];
        $chartData = [
            'series' => $series,
            'status' => collect($labels)->map(fn ($label, $status) => [
                'label' => $label,
                'count' => $counts[$status],
                'color' => $colors[$status],
            ])->values(),
        ];
    @endphp

    <div class="mb-6">
        <h1 class="text-xl font-bold text-red-500">Chào mừng đến chi nhánh {{ $branch->name }}</h1>
        <p class="text-sm text-slate-600">Theo dõi tình hình kinh doanh và số liệu của chi nhánh</p>
    </div>

    {{-- Phân tích kinh doanh --}}
    <section class="bg-white rounded-2xl border border-slate-100 p-5 mb-6">
        <div class="flex items-center justify-between gap-3 mb-5">
            <h2 class="font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19V5M4 19h16M8 15l3-4 3 2 5-6"/></svg>
                Phân tích kinh doanh
            </h2>
            <form method="GET">
                <select name="period" onchange="this.form.submit()"
                        class="text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-red-100">
                    @foreach (\App\Services\BranchStats::PERIODS as $value => $label)
                        <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($icons as $status => $icon)
                <a href="{{ route('manager.orders.index', ['status' => $status]) }}"
                   class="relative rounded-xl border border-slate-200 p-5 hover:shadow-md transition">
                    <span class="absolute top-4 right-4 w-9 h-9 rounded-full grid place-items-center"
                          style="background: {{ $colors[$status] }}1a; color: {{ $colors[$status] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $icon !!}</svg>
                    </span>
                    <p class="text-sm font-medium text-slate-600 mt-4">{{ $labels[$status] }}</p>
                    <p class="text-2xl font-bold mt-1">{{ $counts[$status] }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid sm:grid-cols-3 gap-4 mt-4">
            @foreach ([
                [$labels['completed'], $counts['completed'], 'text-green-600'],
                [$labels['cancelled'], $counts['cancelled'], 'text-red-500'],
                ['Doanh thu', $money($counts['revenue']), 'text-blue-600'],
            ] as [$label, $value, $color])
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-4">
                    <span class="text-sm font-medium text-slate-600">{{ $label }}</span>
                    <span class="text-lg font-bold {{ $color }}">{{ $value }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Đơn chờ xác nhận: duyệt/huỷ nhanh, gửi về route đổi trạng thái có sẵn rồi quay lại đây --}}
    @if ($pendingOrders->isNotEmpty())
        <section class="bg-white rounded-2xl border border-amber-200 p-5 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Đơn chờ xác nhận
                </h2>
                <a href="{{ route('manager.orders.index', ['status' => 'pending']) }}" class="text-sm text-red-500 hover:underline">Xem tất cả</a>
            </div>

            @foreach ($pendingOrders as $order)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <a href="{{ route('manager.orders.show', $order) }}" class="min-w-0 flex-1 hover:text-red-500">
                        <p class="text-sm font-semibold">Đơn #{{ $order->order_code }}</p>
                        <p class="text-xs text-slate-400 truncate">
                            {{ $order->user?->name }} · {{ $order->created_at->format($order->created_at->isToday() ? 'H:i' : 'H:i d/m') }}
                            · {{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}
                        </p>
                    </a>
                    <span class="text-sm font-semibold whitespace-nowrap">{{ $money($order->total) }}</span>

                    <div class="flex items-start gap-2">
                        <form method="POST" action="{{ route('manager.orders.status', $order) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button class="px-4 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold cursor-pointer">Duyệt</button>
                        </form>
                        <details class="relative">
                            <summary class="list-none [&::-webkit-details-marker]:hidden px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm cursor-pointer">Huỷ</summary>
                            <form method="POST" action="{{ route('manager.orders.status', $order) }}"
                                  class="absolute right-0 z-10 mt-2 w-64 bg-white rounded-xl border border-slate-100 shadow-lg p-3">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <input type="text" name="note" required maxlength="255" placeholder="Lý do huỷ (bắt buộc)"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm mb-2">
                                <button class="w-full px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm cursor-pointer">Xác nhận huỷ</button>
                            </form>
                        </details>
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    {{-- Số liệu cho biểu đồ (đọc ở resources/js/manager-dashboard.js) --}}
    <script type="application/json" id="dashboard-data">@json($chartData)</script>

    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        {{-- Thống kê đơn hàng --}}
        <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-5" data-chart-card="orders">
            <div class="flex items-center justify-between gap-3 mb-2">
                <h2 class="font-semibold">Thống kê đơn hàng</h2>
                @include('manager.partials.range-tabs')
            </div>
            <div id="orders-chart"></div>
        </section>

        {{-- Tỉ lệ trạng thái --}}
        <section class="bg-white rounded-2xl border border-slate-100 p-5">
            <h2 class="font-semibold mb-2">Tỉ lệ trạng thái đơn</h2>
            @if ($counts['total'] > 0)
                <div id="status-chart"></div>
                <div class="flex flex-wrap gap-x-4 gap-y-2 mt-3 text-xs text-slate-600">
                    @foreach ($labels as $status => $label)
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $colors[$status] }}"></span>
                            {{ $label }} ({{ $counts[$status] }})
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 py-16 text-center">Chưa có đơn trong kỳ này.</p>
            @endif
        </section>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Thống kê doanh thu --}}
        <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-5" data-chart-card="revenue">
            <div class="flex items-center justify-between gap-3 mb-2">
                <h2 class="font-semibold">Thống kê doanh thu</h2>
                @include('manager.partials.range-tabs')
            </div>
            <div id="revenue-chart"></div>
        </section>

        {{-- Đơn gần đây --}}
        <section class="bg-white rounded-2xl border border-slate-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">Đơn gần đây</h2>
                <a href="{{ route('manager.orders.index') }}" class="text-sm text-red-500 hover:underline">Xem tất cả</a>
            </div>
            @forelse ($recentOrders as $order)
                <a href="{{ route('manager.orders.show', $order) }}"
                   class="flex items-center justify-between gap-3 py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }} hover:bg-slate-50 -mx-2 px-2 rounded-lg">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold">Đơn #{{ $order->order_code }}</p>
                        <p class="text-xs text-slate-400">{{ $order->created_at->format('H:i d/m/Y') }} · {{ $money($order->total) }}</p>
                    </div>
                    @include('manager.partials.status-badge', ['status' => $order->status])
                </a>
            @empty
                <p class="text-sm text-slate-400">Chưa có đơn nào.</p>
            @endforelse
        </section>
    </div>
@endsection
