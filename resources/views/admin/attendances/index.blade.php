@extends('layouts.admin')

@section('title', 'Chấm công')

@section('content')
    <h1 class="text-xl font-bold mb-1">Chấm công</h1>
    <p class="text-sm text-slate-500 mb-4">Chỉ xem. Chấm công tay và chấm ra hộ do quản lý chi nhánh thực hiện.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            @foreach ($branches as $b) <option value="{{ $b->id }}" @selected($branch?->id === $b->id)>{{ $b->name }}</option> @endforeach
        </select>
        <input type="date" name="date" value="{{ $date }}" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Ca</th><th class="px-4 py-3">Vào</th><th class="px-4 py-3">Ra</th><th class="px-4 py-3">Cách chấm</th><th class="px-4 py-3">Trễ / sớm</th></tr>
            </thead>
            <tbody>
                @forelse ($attendances as $a)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold">{{ $a->user?->name }}</td>
                        <td class="px-4 py-3">{{ $a->shift?->name }}</td>
                        <td class="px-4 py-3">{{ $a->check_in?->format('H:i') }}</td>
                        <td class="px-4 py-3">{{ $a->check_out?->format('H:i') ?? 'Chưa ra' }}</td>
                        <td class="px-4 py-3">{{ $a->method === 'face' ? 'Khuôn mặt' : 'Thủ công' }}</td>
                        <td class="px-4 py-3 text-xs">
                            @if ($a->lateMinutes() > 0) <p>Trễ {{ $a->lateMinutes() }}'@if ($a->late_reason): {{ $a->late_reason }}@endif</p> @endif
                            @if ($a->earlyMinutes() > 0) <p>Sớm {{ $a->earlyMinutes() }}'@if ($a->early_reason): {{ $a->early_reason }}@endif</p> @endif
                            @if ($a->lateMinutes() <= 0 && $a->earlyMinutes() <= 0 && ($a->late_reason || $a->early_reason)) <p>{{ $a->late_reason ?? $a->early_reason }}</p> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Không có lượt chấm công nào trong ngày.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
