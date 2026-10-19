<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ParsesDateRange;
use App\Services\BranchReport;
use Illuminate\Http\Request;

class ReportController extends ManagerController
{
    use ParsesDateRange;

    public const TYPE_LABELS = ['takeaway' => 'Mang đi', 'delivery' => 'Giao hàng'];
    public const PAY_LABELS  = \App\Support\PaymentMethods::LABELS;

    public function index(Request $request, BranchReport $report)
    {
        $branchId = $this->branchId();
        [$from, $to, $validator] = $this->dateRange($request);

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

    public function export(Request $request, BranchReport $report)
    {
        [$from, $to, $label, $slug] = $this->exportRange($request);

        return $report->excel($this->branchId(), $from, $to, $this->branchName(), $label)->download("bao-cao-{$slug}.xlsx");
    }
}
