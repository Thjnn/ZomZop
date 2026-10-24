@extends('layouts.manager')

@section('title', 'Ca làm')

@section('content')
    @php
        $input = 'px-3 py-1.5 rounded-lg border border-slate-200 text-sm';
        $hm = fn ($t) => substr($t, 0, 5);
    @endphp

    <h1 class="text-xl font-bold mb-4">Ca làm</h1>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto mb-6">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Tên ca · Giờ</th><th class="px-4 py-3">Lượt chấm công</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($shifts as $shift)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('manager.shifts.update', $shift) }}" class="flex flex-wrap items-center gap-2">
                                @csrf @method('PUT')
                                <input name="name" value="{{ $shift->name }}" class="{{ $input }} w-32" required>
                                <input type="time" name="start_time" value="{{ $hm($shift->start_time) }}" class="{{ $input }}" required>
                                <span>→</span>
                                <input type="time" name="end_time" value="{{ $hm($shift->end_time) }}" class="{{ $input }}" required>
                                @if ($shift->end_time < $shift->start_time) <span class="text-xs text-amber-600">qua đêm</span> @endif
                                <button class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white cursor-pointer">Lưu</button>
                            </form>
                        </td>
                        <td class="px-4 py-3">{{ $shift->attendances_count }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($shift->attendances_count === 0)
                                <form method="POST" action="{{ route('manager.shifts.destroy', $shift) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-500 hover:text-red-500 cursor-pointer">Xoá</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Chưa có ca nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('manager.shifts.store') }}" class="bg-white rounded-2xl p-5 border border-slate-100 flex flex-wrap items-end gap-3 text-sm">
        @csrf
        <h2 class="font-semibold w-full">Thêm ca</h2>
        <input name="name" value="{{ old('name') }}" placeholder="Tên ca (VD: Ca trưa)" class="{{ $input }}" required>
        <input type="time" name="start_time" value="{{ old('start_time') }}" class="{{ $input }}" required>
        <span>→</span>
        <input type="time" name="end_time" value="{{ old('end_time') }}" class="{{ $input }}" required>
        <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Thêm</button>
    </form>

    {{-- Xếp lịch tuần --}}
    @php
        $prev = $week->copy()->subWeek()->toDateString();
        $next = $week->copy()->addWeek()->toDateString();
        $roles = \App\Http\Controllers\Manager\StaffController::ROLES;
    @endphp
    <section id="lich" class="bg-white rounded-2xl border border-slate-100 mt-6">
        <div class="flex flex-wrap items-center justify-between gap-3 p-5 pb-3">
            <div class="flex items-center gap-2">
                <h2 class="font-semibold">Xếp lịch tuần</h2>
                <a href="{{ route('manager.shifts.index', ['week' => $prev]) }}#lich" class="px-2 py-1 rounded-lg border border-slate-200 hover:bg-slate-50" aria-label="Tuần trước">◀</a>
                <span class="text-sm">{{ $week->format('d/m') }} – {{ $week->copy()->addDays(6)->format('d/m/Y') }}</span>
                <a href="{{ route('manager.shifts.index', ['week' => $next]) }}#lich" class="px-2 py-1 rounded-lg border border-slate-200 hover:bg-slate-50" aria-label="Tuần sau">▶</a>
            </div>
            <a href="{{ route('manager.schedule.export', ['week' => $week->toDateString()]) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm hover:bg-slate-50">Xuất Excel</a>
        </div>

        @if ($shifts->isEmpty() || $staff->isEmpty())
            <p class="px-5 pb-5 text-sm text-slate-400">Cần có ít nhất một ca và một nhân viên đang hoạt động để xếp lịch.</p>
        @else
            <form method="POST" action="{{ route('manager.schedule.update') }}">
                @csrf @method('PUT')
                <input type="hidden" name="week" value="{{ $week->toDateString() }}">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-left">
                            <tr>
                                <th class="px-4 py-2 font-medium">Nhân viên</th>
                                @foreach ($days as $d)
                                    <th class="px-3 py-2 font-medium whitespace-nowrap {{ $d->isToday() ? 'text-red-600' : '' }}">
                                        {{ \App\Http\Controllers\Manager\ScheduleController::DOW[$d->dayOfWeekIso - 1] }} {{ $d->format('d/m') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($staff as $u)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span class="font-medium">{{ $u->name }}</span>
                                        <span class="text-xs text-slate-400">{{ $roles[$u->role] ?? '' }}</span>
                                    </td>
                                    @foreach ($days as $date => $d)
                                        <td class="px-3 py-2 align-top">
                                            @foreach ($shifts as $s)
                                                <label class="flex items-center gap-1 whitespace-nowrap cursor-pointer">
                                                    <input type="checkbox" name="slots[{{ $u->id }}][{{ $date }}][]" value="{{ $s->id }}"
                                                           @checked(isset($assigned["{$u->id}|{$date}|{$s->id}"])) class="accent-red-500">
                                                    <span class="text-xs">{{ $s->name }}</span>
                                                </label>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-5 pt-3 text-right">
                    <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu lịch tuần</button>
                </div>
            </form>
        @endif
    </section>
@endsection
