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

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Món</th>
                    <th class="px-4 py-3">Giá gốc</th>
                    <th class="px-4 py-3">Giá chi nhánh · Đang bán</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php $row = $rows[$item->id] ?? null; $on = $row ? $row->is_available : true; @endphp
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
                                <form method="POST" action="{{ route('manager.menu.update', $item) }}" class="flex flex-wrap items-center gap-3">
                                    @csrf @method('PUT')
                                    <input type="number" name="price" value="{{ $row?->price }}" placeholder="{{ $item->base_price }}"
                                           min="1000" max="10000000" step="500" class="w-32 px-3 py-1.5 rounded-lg border border-slate-200">
                                    <input type="hidden" name="is_available" value="0">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" name="is_available" value="1" @checked($on) class="accent-red-500 w-4 h-4"> Bán
                                    </label>
                                    <button class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white cursor-pointer">Lưu</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Không có món nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
