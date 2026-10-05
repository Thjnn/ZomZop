<?php

namespace App\Http\Controllers\Manager;

use App\Services\BranchReport;
use App\Support\XlsxExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ReportController extends ManagerController
{
    public const TYPE_LABELS = ['takeaway' => 'Mang đi', 'delivery' => 'Giao hàng'];
    public const PAY_LABELS  = ['cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPay'];

    /** @return array{0: Carbon, 1: Carbon, 2: \Illuminate\Validation\Validator} khoảng sai → khoảng mặc định */
    private function range(Request $request): array
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

    public function index(Request $request, BranchReport $report)
    {
        $branchId = $this->branchId();
        [$from, $to, $validator] = $this->range($request);

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
        [$from, $to] = $this->range($request);
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
