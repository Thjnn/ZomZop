@extends('layouts.manager')

@section('title', 'Chấm công')

@section('content')
    @php $input = 'px-3 py-2 rounded-lg border border-slate-200 text-sm'; @endphp

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl font-bold">Chấm công</h1>
        <form method="GET" class="flex items-center gap-2 text-sm">
            <input type="date" name="date" value="{{ $date }}" class="{{ $input }}">
            <button class="px-3 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
        </form>
    </div>

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
                        <td class="px-4 py-3 text-slate-500">{{ $a->method === 'face' ? 'Khuôn mặt' : 'Thủ công' }}@if ($a->note) · {{ $a->note }} @endif</td>
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
