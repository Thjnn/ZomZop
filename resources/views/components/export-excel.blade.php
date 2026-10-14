{{-- Nút Xuất Excel kèm chọn kỳ: ngày / tháng (mặc định) / năm, và "khoảng đang lọc" nếu truyền $range = [from, to] --}}
@props(['action', 'periods' => ['day', 'month', 'year'], 'anchor' => null, 'hidden' => [], 'range' => null])
@php
    $anchor  = \Illuminate\Support\Carbon::parse($anchor ?? today());
    $periods = $range ? ['range', ...$periods] : $periods;
    $default = in_array('month', $periods) ? 'month' : $periods[0];
    $labels  = ['range' => 'Khoảng đang lọc', 'day' => 'Theo ngày', 'month' => 'Theo tháng', 'year' => 'Theo năm'];
    $field   = 'w-full px-3 py-2 rounded-lg border border-slate-200 text-sm';
@endphp
<details class="relative">
    <summary class="list-none cursor-pointer select-none px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">Xuất Excel ▾</summary>
    <form method="GET" action="{{ $action }}" class="absolute right-0 z-30 mt-2 w-64 grid gap-2 p-3 bg-white border border-slate-200 rounded-xl shadow-lg text-sm text-slate-700">
        @foreach ($hidden as $name => $value)
            @if (filled($value)) <input type="hidden" name="{{ $name }}" value="{{ $value }}"> @endif
        @endforeach

        <label class="grid gap-1"><span class="text-xs text-slate-400">Kỳ xuất</span>
            <select name="period" class="{{ $field }}"
                    onchange="for (const f of this.form.querySelectorAll('fieldset[data-p]')) { f.disabled = f.hidden = f.dataset.p !== this.value }">
                @foreach ($periods as $p) <option value="{{ $p }}" @selected($p === $default)>{{ $labels[$p] }}</option> @endforeach
            </select>
        </label>

        @if ($range)
            <fieldset data-p="range" @disabled($default !== 'range') @if ($default !== 'range') hidden @endif class="text-xs text-slate-500">
                <input type="hidden" name="from" value="{{ $range[0]->toDateString() }}"><input type="hidden" name="to" value="{{ $range[1]->toDateString() }}">
                {{ $range[0]->format('d/m/Y') }} – {{ $range[1]->format('d/m/Y') }}
            </fieldset>
        @endif
        @foreach (['day' => ['date', $anchor->toDateString()], 'month' => ['month', $anchor->format('Y-m')], 'year' => ['number', $anchor->year]] as $p => [$type, $value])
            @if (in_array($p, $periods))
                <fieldset data-p="{{ $p }}" @disabled($default !== $p) @if ($default !== $p) hidden @endif>
                    <input type="{{ $type }}" name="{{ $p }}" value="{{ $value }}" required class="{{ $field }}" @if ($p === 'year') min="2000" max="2100" @endif>
                </fieldset>
            @endif
        @endforeach

        <button class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold cursor-pointer">Tải file .xlsx</button>
    </form>
</details>
