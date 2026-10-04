<?php

namespace App\Http\Controllers\Manager;

use App\Services\BranchReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ReportController extends ManagerController
{
    public function index(Request $request, BranchReport $report)
    {
        $branchId = $this->branchId();
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

        return view('manager.reports.index', [
            'from'     => $from,
            'to'       => $to,
            'summary'  => $report->summary($branchId, $from, $to),
            'daily'    => $report->daily($branchId, $from, $to),
            'byType'   => $report->byType($branchId, $from, $to),
            'byPay'    => $report->byPayment($branchId, $from, $to),
            'topItems' => $report->topItems($branchId, $from, $to),
        ])->withErrors($validator);
    }
}
