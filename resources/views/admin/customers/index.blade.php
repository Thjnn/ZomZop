@extends('layouts.admin')

@section('title', 'Khách hàng')

@section('content')
    <h1 class="text-xl font-bold mb-4">Khách hàng</h1>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên, email hoặc SĐT" class="px-3 py-2 rounded-lg border border-slate-200">
        <select name="status" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Tất cả</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Đang hoạt động</option>
            <option value="locked" @selected(($filters['status'] ?? '') === 'locked')>Đã khoá</option>
        </select>
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Họ tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">SĐT</th><th class="px-4 py-3">Số đơn</th><th class="px-4 py-3">Đã chi</th><th class="px-4 py-3">Ngày đăng ký</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr class="border-b border-slate-50 {{ $c->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold"><a href="{{ route('admin.customers.show', $c) }}" class="hover:text-red-500">{{ $c->name }}</a></td>
                        <td class="px-4 py-3">{{ $c->email }}</td>
                        <td class="px-4 py-3">{{ $c->phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $c->orders_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ number_format((int) $c->spent, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3">{{ $c->created_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.customers.lock', $c) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $c->is_active ? 'Khoá' : 'Mở khoá' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Không có khách hàng nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
