@extends('layouts.admin')

@section('title', 'Món ăn')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Món ăn toàn chuỗi</h1>
        <a href="{{ route('admin.menu-items.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Thêm món</a>
    </div>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tìm tên món…" maxlength="50" class="px-3 py-2 rounded-lg border border-slate-200">
        <select name="category" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Mọi danh mục</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected(($filters['category'] ?? null) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Mọi trạng thái</option>
            <option value="on" @selected(($filters['status'] ?? null) === 'on')>Đang bán</option>
            <option value="off" @selected(($filters['status'] ?? null) === 'off')>Ngừng bán</option>
        </select>
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Ảnh</th><th class="px-4 py-3">Tên</th><th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3 text-right">Giá gốc</th><th class="px-4 py-3 text-right">Giảm</th>
                    <th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $m)
                    <tr class="border-b border-slate-50 {{ $m->is_available ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3"><img src="{{ $m->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover bg-slate-100"></td>
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $m->name }}</p>
                            @if ($m->tagList())
                                <p class="text-xs text-slate-400">{{ implode(' · ', $m->tagList()) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $m->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">{{ number_format($m->base_price, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3 text-right">{{ $m->discount_percent ? $m->discount_percent . '%' : '—' }}</td>
                        <td class="px-4 py-3">{{ $m->is_available ? 'Đang bán' : 'Ngừng bán' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.menu-items.edit', $m) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            @if (Route::has('admin.menu-items.images'))
                                <a href="{{ route('admin.menu-items.images', $m) }}" class="text-red-500 hover:underline mr-3">Ảnh</a>
                            @endif
                            <form method="POST" action="{{ route('admin.menu-items.toggle', $m) }}" class="inline"
                                  onsubmit="return {{ $m->is_available ? 'confirm(\'Ngừng bán món này ở mọi chi nhánh?\')' : 'true' }}">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $m->is_available ? 'Ngừng bán' : 'Mở bán' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Không có món nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection
