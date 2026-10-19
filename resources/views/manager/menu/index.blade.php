@extends('layouts.manager')

@section('title', 'Menu & giá')

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp

    <h1 class="text-xl font-bold mb-1">Menu & giá chi nhánh</h1>
    <p class="text-sm text-slate-500 mb-4">Để trống ô giá = dùng giá gốc. Giảm giá % của món (do admin đặt) vẫn áp lên giá chi nhánh.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <label class="flex flex-col gap-1">
            <span class="text-xs text-slate-400">Danh mục</span>
            <select name="category" class="px-3 py-2 rounded-lg border border-slate-200">
                <option value="">Tất cả</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(($filters['category'] ?? '') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1 flex-1 min-w-40">
            <span class="text-xs text-slate-400">Tên món</span>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="px-3 py-2 rounded-lg border border-slate-200">
        </label>
        <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lọc</button>
    </form>

    <form method="POST" action="{{ route('manager.menu.update') }}">
    @csrf @method('PUT')
    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Món</th>
                    <th class="px-4 py-3">Giá gốc</th>
                    <th class="px-4 py-3">Giá chi nhánh · Tồn (trống = không giới hạn) · Đang bán · Bán chạy</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $row = $rows[$item->id] ?? null;
                        $on  = $row ? $row->is_available : true;
                        $key = "items.{$item->id}";
                    @endphp
                    <tr class="border-b border-slate-50 {{ (!$item->is_available || !$on) ? 'bg-slate-50 text-slate-400' : '' }}">
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $item->name }}</p>
                            <p class="text-xs text-slate-400">{{ $item->category?->name }}@if ($item->discount_percent > 0) · giảm {{ $item->discount_percent }}% @endif</p>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $money($item->base_price) }}</td>
                        <td class="px-4 py-3">
                            @if (!$item->is_available)
                                <span class="text-xs">Ngừng bán toàn chuỗi</span>
                            @else
                                <div class="flex flex-wrap items-center gap-3">
                                    <input type="number" name="items[{{ $item->id }}][price]" value="{{ old("$key.price", $row?->price) }}" placeholder="{{ $item->base_price }}"
                                           min="1000" max="10000000" step="500" aria-label="Giá chi nhánh {{ $item->name }}"
                                           class="w-32 px-3 py-1.5 rounded-lg border {{ $errors->has("$key.price") ? 'border-red-400' : 'border-slate-200' }}">
                                    <input type="number" name="items[{{ $item->id }}][stock_qty]" value="{{ old("$key.stock_qty", $row?->stock_qty) }}" placeholder="∞"
                                           min="0" max="100000" aria-label="Tồn kho {{ $item->name }}" title="Số phần còn bán; 0 = hết hàng, để trống = không giới hạn"
                                           class="w-20 px-3 py-1.5 rounded-lg border {{ $errors->has("$key.stock_qty") ? 'border-red-400' : (($row?->stock_qty === 0) ? 'border-amber-400 bg-amber-50' : 'border-slate-200') }}">
                                    <input type="hidden" name="items[{{ $item->id }}][is_available]" value="0">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" name="items[{{ $item->id }}][is_available]" value="1" @checked(old("$key.is_available", $on)) class="accent-red-500 w-4 h-4"> Bán
                                    </label>
                                    <input type="hidden" name="items[{{ $item->id }}][is_bestseller]" value="0">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" name="items[{{ $item->id }}][is_bestseller]" value="1" @checked(old("$key.is_bestseller", $row?->is_bestseller)) class="accent-red-500 w-4 h-4"> 🔥 Bán chạy
                                    </label>
                                </div>
                                @if ($item->toppings->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span>Topping còn:</span>
                                        @foreach ($item->toppings as $t)
                                            <input type="hidden" name="toppings[{{ $t->id }}]" value="0">
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="checkbox" name="toppings[{{ $t->id }}]" value="1" @checked(old("toppings.{$t->id}", !isset($toppingsOff[$t->id]))) class="accent-red-500 w-3.5 h-3.5">
                                                {{ $t->name }} ({{ $money($t->price) }})
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Không có món nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->isNotEmpty())
        <div class="sticky bottom-0 mt-4 flex justify-end py-3 bg-slate-50/90 backdrop-blur-sm">
            <button class="px-6 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold shadow-lg cursor-pointer">Lưu tất cả</button>
        </div>
    @endif
    </form>
@endsection
