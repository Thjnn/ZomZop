<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ParsesDateRange;
use App\Http\Controllers\Concerns\ParsesPeriod;
use App\Http\Controllers\Manager\ReportController as ManagerReport;
use App\Models\Branch;
use App\Services\BranchReport;
use Illuminate\Http\Request;

class ReportController extends AdminController
{
    use ParsesDateRange, ParsesPeriod;

    /** Chi nhánh đang lọc; id không tồn tại → coi như toàn chuỗi */
    private function branch(Request $request): ?Branch
    {
        return ctype_digit((string) $request->query('branch_id')) ? Branch::find($request->query('branch_id')) : null;
    }

    public function index(Request $request, BranchReport $report)
    {
        [$from, $to, $validator] = $this->dateRange($request);
        $branch = $this->branch($request);
        $id     = $branch?->id;

        return view('admin.reports.index', [
            'from'       => $from,
            'to'         => $to,
            'branch'     => $branch,
            'branches'   => Branch::orderBy('name')->get(),
            'summary'    => $report->summary($id, $from, $to),
            'daily'      => $report->daily($id, $from, $to),
            'byType'     => $report->byType($id, $from, $to),
            'byPay'      => $report->byPayment($id, $from, $to),
            'topItems'   => $report->topItems($id, $from, $to),
            'byBranch'   => $branch ? null : $report->byBranch($from, $to),
            'typeLabels' => ManagerReport::TYPE_LABELS,
            'payLabels'  => ManagerReport::PAY_LABELS,
        ])->withErrors($validator);
    }

    public function export(Request $request, BranchReport $report)
    {
        [$from, $to, $label, $slug] = $this->exportRange($request);
        $branch = $this->branch($request);

        return $report->excel($branch?->id, $from, $to, $branch?->name ?? 'Toàn chuỗi', $label, !$branch)
            ->download("bao-cao-chuoi-{$slug}.xlsx");
    }
}
