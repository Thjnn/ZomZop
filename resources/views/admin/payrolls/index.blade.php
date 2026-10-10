@extends('layouts.admin')

@section('title', 'Bảng lương')

@section('content')
    @php
        $money  = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $status = ['draft' => 'Nháp', 'confirmed' => 'Đã xác nhận', 'paid' => 'Đã trả'];
    @endphp
    <h1 class="text-xl font-bold mb-1">Bảng lương</h1>
    <p class="text-sm text-slate-500 mb-4">Chỉ xem. Tính, sửa thưởng/khấu trừ, xác nhận và trả lương do quản lý chi nhánh thực hiện.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            @foreach ($branches as $b) <option value="{{ $b->id }}" @selected($branch?->id === $b->id)>{{ $b->name }}</option> @endforeach
        </select>
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Giờ công</th><th class="px-4 py-3">Ngày công</th><th class="px-4 py-3">Lương cơ bản</th><th class="px-4 py-3">Thưởng</th><th class="px-4 py-3">Khấu trừ</th><th class="px-4 py-3">Tổng</th><th class="px-4 py-3">Trạng thái</th></tr>
            </thead>
            <tbody>
                @forelse ($payrolls as $p)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold">{{ $p->user?->name }}</td>
                        <td class="px-4 py-3">{{ $p->total_hours }}</td>
                        <td class="px-4 py-3">{{ $p->total_days }}</td>
                        <td class="px-4 py-3">{{ $money($p->base_salary) }}</td>
                        <td class="px-4 py-3">{{ $money($p->bonus) }}</td>
                        <td class="px-4 py-3">{{ $money($p->deduction) }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $money($p->total) }}</td>
                        <td class="px-4 py-3">{{ $status[$p->status] ?? $p->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Chi nhánh chưa có bảng lương tháng này.</td></tr>
                @endforelse
            </tbody>
            @if ($payrolls->isNotEmpty())
                <tfoot><tr class="font-bold"><td class="px-4 py-3" colspan="6">Tổng quỹ lương</td><td class="px-4 py-3">{{ $money($payrolls->sum('total')) }}</td><td></td></tr></tfoot>
            @endif
        </table>
    </div>
@endsection
