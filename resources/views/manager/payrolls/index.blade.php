@extends('layouts.manager')

@section('title', 'Bảng lương')

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $input = 'w-24 px-2 py-1 rounded-lg border border-slate-200 text-sm text-right';
        $monthEnd = $month->copy()->endOfMonth();
        $badge = ['draft' => 'bg-slate-100 text-slate-600', 'confirmed' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-emerald-100 text-emerald-700'];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold">Bảng lương</h1>
            <p class="text-sm text-slate-500">Tháng {{ $month->format('m/Y') }} · tính theo giờ đã chấm ra</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="px-3 py-2 rounded-lg border border-slate-200">
                <button class="px-3 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
            </form>
            <a href="{{ route('manager.payrolls.export', ['month' => $month->format('Y-m')]) }}" class="px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-sm">Xuất Excel</a>
            <form method="POST" action="{{ route('manager.payrolls.recalculate') }}">
                @csrf <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <button class="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Tính lại</button>
            </form>
        </div>
    </div>

    @error('payroll') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
    @error('bonus') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
    @error('deduction') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Lương/giờ</th>
                    <th class="px-4 py-3 text-right">Giờ</th><th class="px-4 py-3 text-right">Ngày</th>
                    <th class="px-4 py-3 text-right">Cơ bản</th><th class="px-4 py-3 text-right">Thưởng / Phạt</th>
                    <th class="px-4 py-3 text-right">Tổng</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payrolls as $p)
                    @php
                        $u = $p->user;
                        $probEnd = $u->probationEndsAt();
                        $probInMonth = $probEnd && $probEnd->gte($month) && $u->started_at->lte($monthEnd);
                    @endphp
                    <tr class="border-b border-slate-50 align-middle">
                        <td class="px-4 py-3">
                            <span class="font-semibold">{{ $u->name }}</span>
                            <span class="text-xs text-slate-400">{{ \App\Http\Controllers\Manager\StaffController::ROLES[$u->role] ?? $u->role }}</span>
                            @if ($probInMonth)
                                <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Thử việc đến {{ $probEnd->format('d/m') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $u->latestSalary ? $money($u->latestSalary->rate) : '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $p->total_hours, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ $p->total_days }}</td>
                        <td class="px-4 py-3 text-right">{{ $money($p->base_salary) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if ($p->isDraft())
                                <form method="POST" action="{{ route('manager.payrolls.update', $p) }}" class="flex justify-end gap-1">
                                    @csrf @method('PUT')
                                    <input type="number" name="bonus" min="0" step="1" value="{{ $p->bonus }}" class="{{ $input }}" title="Thưởng">
                                    <input type="number" name="deduction" min="0" step="1" value="{{ $p->deduction }}" class="{{ $input }}" title="Phạt">
                                    <button class="px-2 rounded-lg bg-slate-100 hover:bg-slate-200 cursor-pointer">Lưu</button>
                                </form>
                            @else
                                +{{ $money($p->bonus) }} / −{{ $money($p->deduction) }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $money($p->total) }}</td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full {{ $badge[$p->status] }}">{{ $status[$p->status] }}</span></td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if ($p->isDraft())
                                <form method="POST" action="{{ route('manager.payrolls.confirm', $p) }}">@csrf @method('PATCH')
                                    <button class="text-red-500 hover:underline cursor-pointer">Chốt</button></form>
                            @elseif ($p->isConfirmed())
                                <form method="POST" action="{{ route('manager.payrolls.pay', $p) }}">@csrf @method('PATCH')
                                    <button class="text-emerald-600 hover:underline cursor-pointer">Đã trả</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-slate-400">Tháng này chưa có nhân viên nào có lương.</td></tr>
                @endforelse
            </tbody>
            @if ($payrolls->isNotEmpty())
                <tfoot class="font-semibold">
                    <tr>
                        <td class="px-4 py-3" colspan="2">Tổng cộng</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $payrolls->sum('total_hours'), 2, ',', '.') }}</td>
                        <td></td>
                        <td class="px-4 py-3 text-right">{{ $money($payrolls->sum('base_salary')) }}</td>
                        <td></td>
                        <td class="px-4 py-3 text-right">{{ $money($payrolls->sum('total')) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
