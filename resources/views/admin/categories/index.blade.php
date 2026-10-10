@extends('layouts.admin')

@section('title', 'Danh mục')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Danh mục</h1>
        <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Thêm danh mục</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Icon</th><th class="px-4 py-3">Tên</th><th class="px-4 py-3">Đường dẫn</th>
                    <th class="px-4 py-3">Thứ tự</th><th class="px-4 py-3">Số món</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $c)
                    <tr class="border-b border-slate-50 {{ $c->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3">
                            @if ($c->icon)
                                <img src="{{ asset('images/categories/' . $c->icon) }}" alt="" class="w-10 h-10 rounded-lg object-cover bg-slate-100">
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold">{{ $c->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">/category/{{ $c->slug }}</td>
                        <td class="px-4 py-3">{{ $c->sort_order }}</td>
                        <td class="px-4 py-3">{{ $c->menu_items_count }}</td>
                        <td class="px-4 py-3">{{ $c->is_active ? 'Đang hiện' : 'Đang ẩn' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.categories.edit', $c) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.categories.toggle', $c) }}" class="inline"
                                  onsubmit="return {{ $c->is_active ? 'confirm(\'Ẩn danh mục này? Mọi món trong đó sẽ không hiện với khách.\')' : 'true' }}">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $c->is_active ? 'Ẩn' : 'Hiện lại' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Chưa có danh mục nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
