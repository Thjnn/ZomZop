@extends('layouts.manager')

@section('title', 'Chấm công')

@section('content')
    @php $input = 'px-3 py-2 rounded-lg border border-slate-200 text-sm'; @endphp

    @php
        $today = today();
        $nav = 'px-3 py-2 border border-slate-200 bg-white hover:bg-slate-50';
        $dayUrl = fn ($d) => route('manager.attendances.index', ['date' => $d->toDateString()]);
    @endphp

    <h1 class="text-xl font-bold mb-4">Chấm công</h1>

    {{-- Lịch tháng (theo mẫu FullScreenCalendar) --}}
    <div class="bg-white rounded-2xl border border-slate-100 mb-6 overflow-hidden">
        <div class="flex flex-col gap-4 p-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <div class="hidden md:flex w-16 flex-col items-center rounded-lg border border-slate-200 bg-slate-50 p-0.5">
                    <span class="p-1 text-xs uppercase text-slate-500">Th{{ $today->month }}</span>
                    <span class="w-full rounded-lg border border-slate-200 bg-white text-center text-lg font-bold">{{ $today->day }}</span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Tháng {{ $month->month }}, {{ $month->year }}</h2>
                    <p class="text-sm text-slate-500">{{ $month->format('d/m/Y') }} – {{ $month->copy()->endOfMonth()->format('d/m/Y') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <div class="inline-flex -space-x-px">
                    <a href="{{ $dayUrl($month->copy()->subMonth()) }}" class="{{ $nav }} rounded-s-lg" aria-label="Tháng trước">‹</a>
                    <a href="{{ $dayUrl($today) }}" class="{{ $nav }}">Hôm nay</a>
                    <a href="{{ $dayUrl($month->copy()->addMonth()) }}" class="{{ $nav }} rounded-e-lg" aria-label="Tháng sau">›</a>
                </div>
                <form method="GET"><input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="{{ $input }}"></form>
                <x-export-excel :action="route('manager.attendances.export')" :anchor="$date" />
            </div>
        </div>

        <div class="grid grid-cols-7 border-y border-slate-100 text-center text-xs font-semibold text-slate-500">
            @foreach (['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'] as $w)
                <div class="py-2.5 {{ $loop->last ? '' : 'border-r border-slate-100' }}">{{ $w }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7 text-xs">
            @foreach ($calendar as $day)
                @php
                    $key = $day->toDateString();
                    $items = $byDay->get($key, collect());
                    $selected = $key === $date;
                    $inMonth = $day->month === $month->month;
                @endphp
                <a href="{{ $dayUrl($day) }}"
                   class="flex flex-col h-14 lg:h-28 border-b border-r border-slate-100 p-1.5 lg:p-2 hover:bg-slate-50 {{ $inMonth ? '' : 'bg-slate-50/60 text-slate-400' }} {{ $selected ? 'ring-2 ring-inset ring-red-400' : '' }}">
                    <time datetime="{{ $key }}"
                          class="ml-auto lg:ml-0 flex size-6 items-center justify-center rounded-full
                                 {{ $selected ? 'bg-red-500 text-white font-semibold' : ($day->isToday() ? 'text-red-500 font-semibold' : '') }}">{{ $day->day }}</time>

                    @if ($items->isNotEmpty())
                        {{-- Màn nhỏ: chấm tròn --}}
                        <div class="lg:hidden mt-auto flex flex-wrap gap-0.5">
                            @foreach ($items->take(6) as $a)
                                <span class="size-1.5 rounded-full {{ $a->late_reason ? 'bg-amber-500' : 'bg-slate-400' }}"></span>
                            @endforeach
                        </div>
                        {{-- Màn lớn: tên + giờ vào --}}
                        <div class="hidden lg:block mt-1 space-y-1">
                            @foreach ($items->take(2) as $a)
                                <p class="truncate rounded border px-1.5 py-0.5 {{ $a->late_reason ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-slate-200 bg-slate-50' }}">
                                    {{ $a->check_in->format('H:i') }} · {{ $a->user?->name }}
                                </p>
                            @endforeach
                            @if ($items->count() > 2)
                                <p class="text-slate-500">+ {{ $items->count() - 2 }} lượt nữa</p>
                            @endif
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <h2 class="font-semibold mb-3">Ngày {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</h2>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto mb-6">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Ca</th><th class="px-4 py-3">Vào</th>
                    <th class="px-4 py-3">Ra</th><th class="px-4 py-3">Số giờ</th><th class="px-4 py-3">Cách chấm</th></tr>
            </thead>
            <tbody>
                @forelse ($attendances as $a)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold">{{ $a->user?->name }}</td>
                        <td class="px-4 py-3">{{ $a->shift?->name }}</td>
                        <td class="px-4 py-3">{{ $a->check_in->toDateString() === $date ? $a->check_in->format('H:i') : $a->check_in->format('d/m H:i') }}</td>
                        <td class="px-4 py-3">
                            @if ($a->check_out)
                                {{ $a->check_out->format('H:i') }}
                            @else
                                <form method="POST" action="{{ route('manager.attendances.checkout', $a) }}">
                                    @csrf @method('PATCH')
                                    <button class="px-2 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-xs cursor-pointer">Chấm ra</button>
                                </form>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $a->check_out ? number_format($a->working_hours, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            @if ($a->method === 'face')
                                <span class="{{ $a->face_confidence !== null && $a->face_confidence < 30 ? 'text-orange-600 font-semibold' : '' }}">Khuôn mặt · {{ round((float) $a->face_confidence) }}%</span>
                                @if ($a->photo_path)
                                    · <a href="{{ route('manager.attendances.photo', [$a, 'in']) }}" target="_blank" class="text-red-500 hover:underline">Ảnh vào</a>
                                @endif
                                @if ($a->check_out)
                                    · <a href="{{ route('manager.attendances.photo', [$a, 'out']) }}" target="_blank" class="text-red-500 hover:underline">Ảnh ra</a>
                                @endif
                            @else
                                Thủ công
                            @endif
                            @if ($a->note) · {{ $a->note }} @endif
                            @if ($a->late_reason)
                                <span class="block text-xs text-amber-700">Trễ {{ $a->lateMinutes() }}' · {{ $a->late_reason }}</span>
                            @endif
                            @if ($a->early_reason)
                                <span class="block text-xs text-amber-700">Ra sớm {{ $a->earlyMinutes() }}' · {{ $a->early_reason }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Chưa có chấm công ngày này.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('manager.attendances.store') }}" class="bg-white rounded-2xl p-5 border border-slate-100 grid sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
        @csrf
        <h2 class="font-semibold sm:col-span-2 lg:col-span-3">Chấm công thủ công</h2>
        <label class="flex flex-col gap-1">Nhân viên
            <select name="user_id" class="{{ $input }}" required>
                @foreach ($staff as $s) <option value="{{ $s->id }}" @selected(old('user_id') == $s->id)>{{ $s->name }}</option> @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1">Ca
            <select name="shift_id" class="{{ $input }}" required>
                @foreach ($shifts as $sh) <option value="{{ $sh->id }}" @selected(old('shift_id') == $sh->id)>{{ $sh->name }} ({{ substr($sh->start_time, 0, 5) }}–{{ substr($sh->end_time, 0, 5) }})</option> @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1">Giờ vào
            <input type="datetime-local" name="check_in" value="{{ old('check_in', now()->format('Y-m-d\TH:i')) }}" class="{{ $input }}" required>
        </label>
        <label class="flex flex-col gap-1">Giờ ra (để trống nếu đang làm)
            <input type="datetime-local" name="check_out" value="{{ old('check_out') }}" class="{{ $input }}">
        </label>
        <label class="flex flex-col gap-1 sm:col-span-2">Ghi chú
            <input name="note" value="{{ old('note') }}" maxlength="255" class="{{ $input }}">
        </label>
        <div class="flex items-end"><button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer w-full">Chấm công</button></div>
    </form>
@endsection
