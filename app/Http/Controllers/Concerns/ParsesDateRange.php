<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

trait ParsesDateRange
{
    /** @return array{0: Carbon, 1: Carbon, 2: \Illuminate\Validation\Validator} khoảng sai → khoảng mặc định (7 ngày gần nhất) */
    protected function dateRange(Request $request): array
    {
        $defaultFrom = today()->subDays(6);
        $defaultTo   = today();

        $validator = Validator::make($request->only(['from', 'to']), [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to'   => ['nullable', 'date_format:Y-m-d'],
        ], [
            'from.date_format' => 'Ngày bắt đầu không hợp lệ.',
            'to.date_format'   => 'Ngày kết thúc không hợp lệ.',
        ]);

        $valid = $validator->valid();
        $from  = isset($valid['from']) ? Carbon::parse($valid['from']) : $defaultFrom;
        $to    = isset($valid['to']) ? Carbon::parse($valid['to']) : $defaultTo;

        $validator->after(function ($v) use ($from, $to) {
            if ($to->lt($from)) {
                $v->errors()->add('to', 'Ngày kết thúc phải từ ngày bắt đầu trở đi.');
            } elseif ($from->diffInDays($to) > 366) {
                $v->errors()->add('to', 'Khoảng ngày tối đa 366 ngày.');
            }
        });

        if ($validator->fails()) {
            // Có lỗi thì dùng khoảng mặc định nếu khoảng hiện tại không dùng được
            if ($to->lt($from) || $from->diffInDays($to) > 366) {
                [$from, $to] = [$defaultFrom, $defaultTo];
            }
        }

        return [$from, $to, $validator];
    }

    /**
     * Kỳ xuất Excel của trang báo cáo: không có period hoặc period=range → khoảng từ/đến đang lọc,
     * còn lại theo ngày/tháng/năm (cần trait ParsesPeriod).
     *
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: string} [from, to, nhãn, đuôi tên file]
     */
    protected function exportRange(Request $request): array
    {
        if ($request->query('period', 'range') === 'range') {
            [$from, $to] = $this->dateRange($request);

            return [$from, $to, $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y'), $from->toDateString() . '-' . $to->toDateString()];
        }

        $p = $this->period($request);

        return [$p['from'], $p['to'], $p['label'], $p['slug']];
    }
}
