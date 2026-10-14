<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Kỳ xuất báo cáo: ?period=day|month|year &day=Y-m-d &month=Y-m &year=Y (sai/thiếu → kỳ chứa $anchor, mặc định theo tháng) */
trait ParsesPeriod
{
    /** @return array{period: string, from: Carbon, to: Carbon, label: string, slug: string} */
    protected function period(Request $request, ?Carbon $anchor = null, array $allowed = ['day', 'month', 'year']): array
    {
        $anchor ??= today();
        $v = Validator::make($request->only(['period', 'day', 'month', 'year']), [
            'period' => ['nullable', Rule::in($allowed)],
            'day'    => ['nullable', 'date_format:Y-m-d'],
            'month'  => ['nullable', 'date_format:Y-m'],
            'year'   => ['nullable', 'integer', 'between:2000,2100'],
        ])->valid();

        $period = $v['period'] ?? (in_array('month', $allowed) ? 'month' : $allowed[0]);

        if ($period === 'day') {
            $from = isset($v['day']) ? Carbon::parse($v['day']) : $anchor->copy()->startOfDay();

            return ['period' => 'day', 'from' => $from, 'to' => $from->copy()->endOfDay(),
                'label' => 'Ngày ' . $from->format('d/m/Y'), 'slug' => $from->format('Y-m-d')];
        }

        if ($period === 'year') {
            $from = isset($v['year']) ? Carbon::create((int) $v['year']) : $anchor->copy()->startOfYear();

            return ['period' => 'year', 'from' => $from, 'to' => $from->copy()->endOfYear(),
                'label' => 'Năm ' . $from->year, 'slug' => (string) $from->year];
        }

        $from = isset($v['month']) ? Carbon::parse($v['month'] . '-01') : $anchor->copy()->startOfMonth();

        return ['period' => 'month', 'from' => $from, 'to' => $from->copy()->endOfMonth(),
            'label' => 'Tháng ' . $from->format('m/Y'), 'slug' => $from->format('Y-m')];
    }
}
