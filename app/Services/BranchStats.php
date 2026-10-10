<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;

class BranchStats
{
    public const PERIODS = [
        'all'   => 'Tổng',
        'today' => 'Hôm nay',
        'month' => 'Tháng này',
    ];

    /**
     * Số đơn theo từng trạng thái + tổng đơn + doanh thu (chỉ đơn hoàn thành) trong kỳ.
     * $period: all | today | month
     */
    public function statusCounts(?int $branchId, string $period = 'all'): array
    {
        $query = Order::query()->when($branchId, fn ($q, $id) => $q->ofBranch($id));

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', today()->startOfMonth());
        }

        $rows = $query->selectRaw('status, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $result = [];
        foreach (array_keys(OrderStatusService::LABELS) as $status) {
            $result[$status] = (int) ($rows[$status]->orders ?? 0);
        }
        $result['total']   = array_sum($result);
        $result['revenue'] = (int) ($rows['completed']->revenue ?? 0);

        return $result;
    }

    /**
     * Số đơn + doanh thu theo từng điểm thời gian cho biểu đồ, thiếu thì điền 0.
     * $range: year (12 tháng năm nay) | month (từng ngày tháng này) | week (7 ngày gần nhất)
     */
    public function series(?int $branchId, string $range): array
    {
        [$from, $to, $keyOf, $points] = match ($range) {
            'year'  => [
                today()->startOfYear(), today()->endOfYear(),
                fn (Carbon $d) => $d->month,
                collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => "T$m"]),
            ],
            'month' => [
                today()->startOfMonth(), today()->endOfMonth(),
                fn (Carbon $d) => $d->day,
                collect(range(1, today()->daysInMonth))->mapWithKeys(fn ($d) => [$d => (string) $d]),
            ],
            default => [
                today()->subDays(6), today()->endOfDay(),
                fn (Carbon $d) => $d->toDateString(),
                collect(range(6, 0))->mapWithKeys(fn ($i) => [
                    today()->subDays($i)->toDateString() => today()->subDays($i)->format('d/m'),
                ]),
            ],
        };

        // Gom theo ngày bằng SQL (chạy được cả MySQL lẫn SQLite), rồi gộp tiếp theo tháng ở PHP
        $days = Order::query()->when($branchId, fn ($q, $id) => $q->ofBranch($id))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->groupBy('day')
            ->get();

        $orders  = $points->map(fn () => 0)->all();
        $revenue = $orders;
        foreach ($days as $row) {
            $key = $keyOf(Carbon::parse($row->day));
            $orders[$key]  += (int) $row->orders;
            $revenue[$key] += (int) $row->revenue;
        }

        return [
            'labels'  => $points->values()->all(),
            'orders'  => array_values($orders),
            'revenue' => array_values($revenue),
        ];
    }
}
