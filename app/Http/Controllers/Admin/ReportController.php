<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ParsesDateRange;
use App\Http\Controllers\Manager\ReportController as ManagerReport;
use App\Models\Branch;
use App\Services\BranchReport;
use App\Support\XlsxExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends AdminController
{
    use ParsesDateRange;

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
        [$from, $to] = $this->dateRange($request);
        $branch = $this->branch($request);
        $id     = $branch?->id;
        $s      = $report->summary($id, $from, $to);

        $rows = [
            ['Phạm vi', $branch?->name ?? 'Toàn chuỗi'],
            ['Doanh thu', $s['revenue']], ['Số đơn', $s['orders']], ['Hoàn thành', $s['completed']],
            ['Đã huỷ', $s['cancelled']], ['Giá trị TB/đơn', $s['avg_order']], ['Tỉ lệ huỷ (%)', $s['cancel_rate']],
        ];
        if (!$branch) {
            $rows[] = [];
            $rows[] = XlsxExport::bold(['Chi nhánh', 'Số đơn', 'Doanh thu', 'Đơn huỷ']);
            foreach ($report->byBranch($from, $to) as $r) {
                $rows[] = [$r['branch']->name, $r['orders'], $r['revenue'], $r['cancelled']];
            }
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Ngày', 'Số đơn', 'Doanh thu']);
        foreach ($report->daily($id, $from, $to) as $day => $d) {
            $rows[] = [Carbon::parse($day)->format('d/m/Y'), $d['orders'], $d['revenue']];
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Món bán chạy', 'Số lượng', 'Doanh thu']);
        foreach ($report->topItems($id, $from, $to) as $item) {
            $rows[] = [$item->name, (int) $item->qty, (int) $item->revenue];
        }

        return XlsxExport::download("bao-cao-chuoi-{$from->toDateString()}-{$to->toDateString()}.xlsx",
            ['Báo cáo doanh thu', $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y')], $rows);
    }
}
