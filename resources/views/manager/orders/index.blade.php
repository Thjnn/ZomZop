@extends('layouts.manager')

@section('title', 'Đơn hàng')

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp

    <h1 class="text-xl font-bold mb-4">Đơn hàng</h1>

    {{-- Bộ lọc --}}
    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <label class="flex flex-col gap-1">
            <span class="text-xs text-slate-400">Trạng thái</span>
            <select name="status" class="px-3 py-2 rounded-lg border border-slate-200">
                <option value="">Tất cả</option>
                @foreach ($labels as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1">
            <span class="text-xs text-slate-400">Ngày</span>
            <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="px-3 py-2 rounded-lg border border-slate-200">
        </label>
        <label class="flex flex-col gap-1 flex-1 min-w-40">
            <span class="text-xs text-slate-400">Mã đơn / mã lấy hàng</span>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="VD: ZZ8F3K hoặc B07" class="px-3 py-2 rounded-lg border border-slate-200">
        </label>
        <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lọc</button>
        <a href="{{ route('manager.orders.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200">Xoá lọc</a>
    </form>

    {{-- Bảng đơn (cuộn ngang trên điện thoại) --}}
    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Mã đơn</th>
                    <th class="px-4 py-3">Khách</th>
                    <th class="px-4 py-3">Loại</th>
                    <th class="px-4 py-3">Số món</th>
                    <th class="px-4 py-3">Tổng</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Thời gian</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b border-slate-50 hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold">
                            @if (Route::has('manager.orders.show'))
                                <a href="{{ route('manager.orders.show', $order) }}" class="text-red-500 hover:underline">{{ $order->order_code }}</a>
                            @else
                                {{ $order->order_code }}
                            @endif
                            <span class="block text-xs text-slate-400 font-normal">Lấy hàng: {{ $order->pickup_code }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $order->user?->name }}</td>
                        <td class="px-4 py-3">{{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}</td>
                        <td class="px-4 py-3">{{ $order->items_count }}</td>
                        <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $money($order->total) }}</td>
                        <td class="px-4 py-3">@include('manager.partials.status-badge', ['status' => $order->status])</td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $order->created_at->format('H:i d/m') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Không có đơn nào phù hợp.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
