@extends('layouts.manager')

@section('title', 'Bảng lương')

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
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
            <x-export-excel :action="route('manager.payrolls.export')" :periods="['month', 'year']" :anchor="$month" />
            <form method="POST" action="{{ route('manager.payrolls.recalculate') }}">
                @csrf <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <button class="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Tính lại</button>
            </form>
        </div>
    </div>

    @error('payroll') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
    @foreach (['type', 'amount', 'reason'] as $f)
        @error($f) <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
    @endforeach

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
                            <span class="text-emerald-600">+{{ $money($p->bonus) }}</span> / <span class="text-red-600">−{{ $money($p->deduction) }}</span>
                            <a href="#adj-{{ $p->id }}" class="block text-xs text-slate-500 hover:text-red-500">
                                {{ $p->adjustments->count() ? $p->adjustments->count() . ' khoản · xem lý do' : ($p->isDraft() ? '+ Thêm thưởng / phạt' : 'Không có khoản nào') }}
                            </a>
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
                    {{-- Chi tiết thưởng / phạt: mở khi bấm link (#adj-id) hoặc khi vừa nhập lỗi ở dòng này --}}
                    <tr id="adj-{{ $p->id }}" class="{{ (int) old('payroll_id') === $p->id ? 'table-row' : 'hidden target:table-row' }} bg-slate-50/70 border-b border-slate-100">
                        <td colspan="9" class="px-4 py-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-semibold text-slate-700">Thưởng / phạt của {{ $u->name }}</p>
                                <a href="#_" class="text-xs text-slate-400 hover:text-slate-600">Đóng ✕</a>
                            </div>
                            <div class="space-y-2">
                                @forelse ($p->adjustments as $a)
                                    <div class="flex flex-wrap items-center gap-2 bg-white rounded-xl border border-slate-100 px-3 py-2">
                                        @if ($p->isDraft())
                                            <form method="POST" action="{{ route('manager.payrolls.adjustments.update', $a) }}" class="flex flex-1 flex-wrap items-center gap-2">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="payroll_id" value="{{ $p->id }}">
                                                <select name="type" class="px-2 py-1 rounded-lg border border-slate-200 text-sm">
                                                    @foreach (\App\Models\PayrollAdjustment::TYPES as $k => $label)
                                                        <option value="{{ $k }}" @selected($a->type === $k)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="number" name="amount" min="1000" step="1000" value="{{ $a->amount }}" required class="w-32 px-2 py-1 rounded-lg border border-slate-200 text-sm text-right">
                                                <input type="text" name="reason" value="{{ $a->reason }}" maxlength="255" required class="flex-1 min-w-48 px-2 py-1 rounded-lg border border-slate-200 text-sm">
                                                <button class="px-3 py-1 rounded-lg bg-slate-800 text-white text-xs cursor-pointer">Lưu</button>
                                            </form>
                                            <form method="POST" action="{{ route('manager.payrolls.adjustments.destroy', $a) }}" onsubmit="return confirm('Xoá khoản này?')">
                                                @csrf @method('DELETE')
                                                <button class="px-2 py-1 text-xs text-red-500 hover:underline cursor-pointer">Xoá</button>
                                            </form>
                                        @else
                                            <span class="text-xs px-2 py-0.5 rounded-full {{ $a->type === 'bonus' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ \App\Models\PayrollAdjustment::TYPES[$a->type] }}</span>
                                            <span class="font-semibold w-28 text-right">{{ $money($a->amount) }}</span>
                                            <span class="flex-1">{{ $a->reason }}</span>
                                        @endif
                                        <span class="w-full text-[11px] text-slate-400">
                                            {{ $a->creator?->name ?? 'Hệ thống' }} · {{ $a->updated_at->format('d/m/Y H:i') }}{{ $a->updated_at->ne($a->created_at) ? ' (đã sửa)' : '' }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">Chưa có khoản thưởng / phạt nào.</p>
                                @endforelse

                                @if ($p->isDraft())
                                    <form method="POST" action="{{ route('manager.payrolls.adjustments.store', $p) }}" class="flex flex-wrap items-center gap-2 pt-2">
                                        @csrf
                                        <input type="hidden" name="payroll_id" value="{{ $p->id }}">
                                        @php $fromThis = (int) old('payroll_id') === $p->id; @endphp
                                        <select name="type" class="px-2 py-1 rounded-lg border border-slate-200 text-sm">
                                            @foreach (\App\Models\PayrollAdjustment::TYPES as $k => $label)
                                                <option value="{{ $k }}" @selected($fromThis && old('type') === $k)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="amount" min="1000" step="1000" placeholder="Số tiền" value="{{ $fromThis ? old('amount') : '' }}" required class="w-32 px-2 py-1 rounded-lg border border-slate-200 text-sm text-right">
                                        <input type="text" name="reason" maxlength="255" placeholder="Lý do, ví dụ: Làm thêm giờ ngày lễ 2/9" value="{{ $fromThis ? old('reason') : '' }}" required class="flex-1 min-w-48 px-2 py-1 rounded-lg border border-slate-200 text-sm">
                                        <button class="px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-semibold cursor-pointer">+ Thêm khoản</button>
                                    </form>
                                @endif
                            </div>
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
                        <td class="px-4 py-3 text-right whitespace-nowrap">+{{ $money($payrolls->sum('bonus')) }} / −{{ $money($payrolls->sum('deduction')) }}</td>
                        <td class="px-4 py-3 text-right">{{ $money($payrolls->sum('total')) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
