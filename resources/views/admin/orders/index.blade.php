@extends('layouts.admin')

@section('title', 'Đơn hàng')

@section('content')
    <h1 class="text-xl font-bold mb-1">Đơn hàng toàn chuỗi</h1>
    <p class="text-sm text-slate-500 mb-4">Chỉ xem. Xử lý đơn do quản lý chi nhánh thực hiện.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Tất cả chi nhánh</option>
            @foreach ($branches as $b) <option value="{{ $b->id }}" @selected(($filters['branch_id'] ?? null) == $b->id)>{{ $b->name }}</option> @endforeach
        </select>
        <select name="status" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Mọi trạng thái</option>
            @foreach ($labels as $k => $label) <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $label }}</option> @endforeach
        </select>
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="px-3 py-2 rounded-lg border border-slate-200">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Mã đơn / mã nhận" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Mã đơn</th><th class="px-4 py-3">Chi nhánh</th><th class="px-4 py-3">Khách</th><th class="px-4 py-3">Thời gian</th><th class="px-4 py-3">Tổng</th><th class="px-4 py-3">Trạng thái</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold"><a href="{{ route('admin.orders.show', $o) }}" class="hover:text-red-500">{{ $o->order_code }}</a></td>
                        <td class="px-4 py-3">{{ $o->branch?->name }}</td>
                        <td class="px-4 py-3">{{ $o->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ number_format($o->total, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3">@include('manager.partials.status-badge', ['status' => $o->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Không có đơn nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
