<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ParsesDateRange;
use App\Services\BranchReport;
use App\Support\XlsxExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends ManagerController
{
    use ParsesDateRange;

    public const TYPE_LABELS = ['takeaway' => 'Mang đi', 'delivery' => 'Giao hàng'];
    public const PAY_LABELS  = ['cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPay'];

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
        $branchId = $this->branchId();
        [$from, $to] = $this->dateRange($request);
        $s = $report->summary($branchId, $from, $to);

        $rows = [
            ['Doanh thu', $s['revenue']], ['Số đơn', $s['orders']], ['Hoàn thành', $s['completed']],
            ['Đã huỷ', $s['cancelled']], ['Giá trị TB/đơn', $s['avg_order']], ['Tỉ lệ huỷ (%)', $s['cancel_rate']],
            [], XlsxExport::bold(['Ngày', 'Số đơn', 'Doanh thu']),
        ];
        foreach ($report->daily($branchId, $from, $to) as $day => $d) {
            $rows[] = [Carbon::parse($day)->format('d/m/Y'), $d['orders'], $d['revenue']];
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Hình thức', 'Số đơn', 'Doanh thu']);
        foreach ($report->byType($branchId, $from, $to) as $k => $d) {
            $rows[] = [self::TYPE_LABELS[$k], $d['orders'], $d['revenue']];
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Thanh toán', 'Số đơn', 'Doanh thu']);
        foreach ($report->byPayment($branchId, $from, $to) as $k => $d) {
            $rows[] = [self::PAY_LABELS[$k], $d['orders'], $d['revenue']];
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Món bán chạy', 'Số lượng', 'Doanh thu']);
        foreach ($report->topItems($branchId, $from, $to) as $item) {
            $rows[] = [$item->name, (int) $item->qty, (int) $item->revenue];
        }

        return XlsxExport::download("bao-cao-{$from->toDateString()}-{$to->toDateString()}.xlsx",
            ['Báo cáo doanh thu', $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y')], $rows);
    }
}
