@extends('layouts.staff')

@section('title', 'Của tôi')

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $badge = ['draft' => 'bg-slate-100 text-slate-600', 'confirmed' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-emerald-100 text-emerald-700'];
        $done  = $attendances->whereNotNull('check_out');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold">Xin chào, {{ $user->name }}</h1>
            <p class="text-sm text-slate-500">Chấm công bằng khuôn mặt tại máy ở quầy. Sai sót giờ công, hãy báo quản lý.</p>
        </div>
        <form method="GET" class="flex items-center gap-2 text-sm">
            <label for="month" class="text-slate-500">Tháng</label>
            <input type="month" id="month" name="month" value="{{ $month->format('Y-m') }}" max="{{ today()->addMonthNoOverflow()->format('Y-m') }}"
                   onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white">
        </form>
    </div>

    {{-- Hôm nay --}}
    <section class="grid md:grid-cols-2 gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h2 class="font-semibold mb-3">Hôm nay · {{ today()->format('d/m/Y') }}</h2>
            @forelse ($today as $a)
                <p class="text-sm py-1">
                    <span class="font-medium">{{ $a->shift?->name ?? 'Ngoài ca' }}</span>:
                    vào {{ $a->check_in->format('H:i') }}
                    @if ($a->check_out) · ra {{ $a->check_out->format('H:i') }} · {{ $a->working_hours }} giờ
                    @else · <span class="text-emerald-600">đang trong ca</span>
                    @endif
                </p>
            @empty
                <p class="text-sm text-slate-400">Chưa chấm công hôm nay.</p>
            @endforelse
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h2 class="font-semibold mb-3">Ca làm của chi nhánh</h2>
            @forelse ($shifts as $s)
                <p class="text-sm py-1 flex justify-between"><span>{{ $s->name }}</span><span class="text-slate-500">{{ substr($s->start_time, 0, 5) }} – {{ substr($s->end_time, 0, 5) }}</span></p>
            @empty
                <p class="text-sm text-slate-400">Chi nhánh chưa khai báo ca làm.</p>
            @endforelse
        </div>
    </section>

    {{-- Lịch làm được xếp --}}
    <section class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h2 class="font-semibold">Lịch làm tháng {{ $month->format('m/Y') }}</h2>
            <a href="{{ route('staff.schedule.export', ['month' => $month->format('Y-m')]) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm hover:bg-slate-50">Xuất Excel</a>
        </div>
        @forelse ($schedule as $date => $items)
            @php $d = \Illuminate\Support\Carbon::parse($date); @endphp
            <div class="flex gap-3 py-1.5 text-sm border-b border-slate-50 last:border-0 {{ $d->isPast() && !$d->isToday() ? 'text-slate-400' : '' }}">
                <span class="w-24 shrink-0 {{ $d->isToday() ? 'font-semibold text-red-600' : '' }}">
                    {{ \App\Http\Controllers\Manager\ScheduleController::DOW[$d->dayOfWeekIso - 1] }} {{ $d->format('d/m') }}
                </span>
                <span>{{ $items->map(fn ($a) => $a->shift->name . ' (' . substr($a->shift->start_time, 0, 5) . '–' . substr($a->shift->end_time, 0, 5) . ')')->implode(' · ') }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">Chưa được xếp lịch trong tháng này.</p>
        @endforelse
    </section>

    {{-- Lương tháng --}}
    <section class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h2 class="font-semibold">Lương tháng {{ $month->format('m/Y') }}</h2>
            @if ($payroll)
                <span class="text-xs px-2 py-0.5 rounded-full {{ $badge[$payroll->status] }}">{{ $status[$payroll->status] }}</span>
            @endif
        </div>
        @if ($payroll)
            @if ($payroll->isDraft())
                <p class="text-xs text-amber-600 mb-3">Tạm tính — quản lý chưa chốt nên số tiền có thể còn thay đổi.</p>
            @endif
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div><dt class="text-slate-400">Giờ làm</dt><dd class="font-semibold">{{ (float) $payroll->total_hours }} giờ · {{ $payroll->total_days }} ngày</dd></div>
                <div><dt class="text-slate-400">Lương cơ bản</dt><dd class="font-semibold">{{ $money($payroll->base_salary) }}</dd></div>
                <div><dt class="text-slate-400">Thưởng / phạt</dt><dd class="font-semibold">+{{ $money($payroll->bonus) }} / −{{ $money($payroll->deduction) }}</dd></div>
                <div><dt class="text-slate-400">Thực nhận</dt><dd class="text-lg font-bold text-red-600">{{ $money($payroll->total) }}</dd></div>
            </dl>
            @if ($payroll->adjustments->isNotEmpty())
                <ul class="mt-4 border-t border-slate-100 pt-3 text-sm space-y-1">
                    @foreach ($payroll->adjustments as $adj)
                        <li class="flex justify-between gap-3">
                            <span>{{ $adj->reason }}</span>
                            <span class="{{ $adj->type === 'bonus' ? 'text-emerald-600' : 'text-red-600' }} whitespace-nowrap">{{ $adj->type === 'bonus' ? '+' : '−' }}{{ $money($adj->amount) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        @else
            <p class="text-sm text-slate-400">Chưa có bảng lương tháng này.</p>
        @endif
    </section>

    {{-- Chấm công trong tháng --}}
    <section class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 p-5 pb-3">
            <h2 class="font-semibold">Chấm công tháng {{ $month->format('m/Y') }}</h2>
            <p class="text-sm text-slate-500">{{ $done->count() }} lượt · {{ round($done->sum('working_hours'), 2) }} giờ</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium">Ngày</th>
                        <th class="px-4 py-2 font-medium">Ca</th>
                        <th class="px-4 py-2 font-medium">Vào</th>
                        <th class="px-4 py-2 font-medium">Ra</th>
                        <th class="px-4 py-2 font-medium">Số giờ</th>
                        <th class="px-4 py-2 font-medium">Ghi chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($attendances as $a)
                        @php $late = $a->lateMinutes(); $early = $a->earlyMinutes(); @endphp
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $a->check_in->format('d/m') }}</td>
                            <td class="px-4 py-2">{{ $a->shift?->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $a->check_in->format('H:i') }}</td>
                            <td class="px-4 py-2">{{ $a->check_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $a->check_out ? $a->working_hours : '—' }}</td>
                            <td class="px-4 py-2 text-slate-500">
                                @if ($late) <span class="text-amber-600">trễ {{ $late }}′</span> @endif
                                @if ($early) <span class="text-amber-600">sớm {{ $early }}′</span> @endif
                                @if ($a->isManualMethod()) <span>chấm tay</span> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Chưa có lượt chấm công nào trong tháng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
