@extends('layouts.staff')

@section('title', 'Màn hình bếp')

@push('head')
    {{-- Tự tải lại mỗi 30 giây để thấy đơn mới --}}
    <meta http-equiv="refresh" content="30">
@endpush

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold">Màn hình bếp</h1>
            <p class="text-sm text-slate-500">Tự cập nhật mỗi 30 giây · lúc {{ now()->format('H:i') }}. Quản lý chuyển trạng thái đơn.</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        @foreach ([['Chờ nấu', $waiting, 'border-amber-300', 'bg-amber-100 text-amber-800'], ['Đang nấu', $cooking, 'border-red-300', 'bg-red-100 text-red-700']] as [$title, $list, $border, $pill])
            <section>
                <h2 class="flex items-center gap-2 text-lg font-bold mb-3">
                    {{ $title }} <span class="text-sm px-2.5 py-0.5 rounded-full {{ $pill }}">{{ $list->count() }}</span>
                </h2>
                <div class="space-y-3">
                    @forelse ($list as $order)
                        <article class="bg-white rounded-2xl border-2 {{ $border }} p-4">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <p class="text-2xl font-extrabold tracking-wide">{{ $order->order_code }}</p>
                                    <p class="text-sm text-slate-500">
                                        {{ $order->isDelivery() ? 'Giao hàng' : 'Mang đi' }}
                                        @if ($order->isDelivery() && $order->delivery_speed === 'express')
                                            · <span class="font-semibold text-red-600">HỎA TỐC</span>
                                        @endif
                                    </p>
                                </div>
                                <p class="text-right text-sm text-slate-500 whitespace-nowrap">
                                    đặt {{ $order->created_at->format('H:i') }}<br>
                                    <span class="font-semibold text-slate-700">{{ (int) $order->created_at->diffInMinutes(now()) }} phút</span>
                                </p>
                            </div>
                            <ul class="text-lg space-y-1">
                                @foreach ($order->items as $item)
                                    <li>
                                        <span class="font-bold">{{ $item->quantity }} ×</span> {{ $item->name_snapshot }}
                                        @if ($item->note) <span class="block text-sm text-amber-700">↳ {{ $item->note }}</span> @endif
                                    </li>
                                @endforeach
                            </ul>
                            @if ($order->note)
                                <p class="mt-2 text-sm bg-amber-50 text-amber-800 rounded-lg px-3 py-2">Ghi chú: {{ $order->note }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="text-slate-400 bg-white rounded-2xl border border-slate-100 p-6 text-center">Không có đơn.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
@endsection
