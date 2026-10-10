@extends('layouts.admin')

@section('title', $customer->name)

@section('content')
    <a href="{{ route('admin.customers.index') }}" class="text-sm text-slate-500 hover:underline">← Khách hàng</a>
    <div class="bg-white rounded-2xl border border-slate-100 p-6 my-4 grid sm:grid-cols-4 gap-4 text-sm">
        <div><p class="text-xs text-slate-400">Họ tên</p><p class="font-semibold">{{ $customer->name }}</p></div>
        <div><p class="text-xs text-slate-400">Email / SĐT</p><p>{{ $customer->email }}<br>{{ $customer->phone ?? '—' }}</p></div>
        <div><p class="text-xs text-slate-400">Đã chi (đơn hoàn thành)</p><p class="font-semibold">{{ number_format($spent, 0, ',', '.') }}đ</p></div>
        <div><p class="text-xs text-slate-400">Trạng thái</p><p>{{ $customer->is_active ? 'Đang hoạt động' : 'Đã khoá' }}</p></div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Mã đơn</th><th class="px-4 py-3">Chi nhánh</th><th class="px-4 py-3">Ngày</th><th class="px-4 py-3">Tổng</th><th class="px-4 py-3">Trạng thái</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold">
                            @if (Route::has('admin.orders.show'))
                                <a href="{{ route('admin.orders.show', $o) }}" class="hover:text-red-500">{{ $o->order_code }}</a>
                            @else
                                {{ $o->order_code }}
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $o->branch?->name }}</td>
                        <td class="px-4 py-3">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ number_format($o->total, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3">@include('manager.partials.status-badge', ['status' => $o->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Khách chưa có đơn nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
