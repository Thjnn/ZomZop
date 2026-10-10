@extends('layouts.admin')

@section('title', 'Tài khoản quản lý')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Tài khoản quản lý</h1>
        <a href="{{ route('admin.managers.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Tạo tài khoản</a>
    </div>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Tất cả chi nhánh</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}" @selected(($filters['branch_id'] ?? null) == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên hoặc email" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Họ tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">SĐT</th><th class="px-4 py-3">Chi nhánh</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($managers as $u)
                    <tr class="border-b border-slate-50 {{ $u->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">{{ $u->phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $u->branch?->name ?? 'Chưa gán' }}</td>
                        <td class="px-4 py-3">{{ $u->is_active ? 'Hoạt động' : 'Đã khoá' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.managers.edit', $u) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.managers.lock', $u) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $u->is_active ? 'Khoá' : 'Mở khoá' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Không có tài khoản nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $managers->links() }}</div>
@endsection
