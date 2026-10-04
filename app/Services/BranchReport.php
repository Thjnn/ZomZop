<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BranchReport
{
    /** Đơn của chi nhánh, tạo trong [from, to] tính theo ngày (gồm cả ngày cuối) */
    private function orders(int $branchId, Carbon $from, Carbon $to): Builder
    {
        return Order::ofBranch($branchId)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString());
    }

    public function summary(int $branchId, Carbon $from, Carbon $to): array
    {
        $row = $this->orders($branchId, $from, $to)
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->first();

        $orders    = (int) $row->orders;
        $revenue   = (int) $row->revenue;
        $completed = (int) $row->completed;
        $cancelled = (int) $row->cancelled;

        return [
            'revenue'     => $revenue,
            'orders'      => $orders,
            'completed'   => $completed,
            'cancelled'   => $cancelled,
            'avg_order'   => $completed ? intdiv($revenue, $completed) : 0,
            'cancel_rate' => $orders ? round($cancelled / $orders * 100, 1) : 0.0,
        ];
    }

    public function daily(int $branchId, Carbon $from, Carbon $to): array
    {
        $rows = $this->orders($branchId, $from, $to)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->groupBy('day')
            ->get()->keyBy('day');

        $result = [];
        for ($d = $from->copy()->startOfDay(); $d->lte($to); $d->addDay()) {
            $key = $d->toDateString();
            $result[$key] = ['revenue' => (int) ($rows[$key]->revenue ?? 0), 'orders' => (int) ($rows[$key]->orders ?? 0)];
        }

        return $result;
    }

    private function completedGroupedBy(string $column, array $keys, int $branchId, Carbon $from, Carbon $to): array
    {
        $rows = $this->orders($branchId, $from, $to)
            ->where('status', 'completed')
            ->selectRaw("{$column} as k, COUNT(*) as orders, SUM(total) as revenue")
            ->groupBy('k')
            ->get()->keyBy('k');

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = ['orders' => (int) ($rows[$key]->orders ?? 0), 'revenue' => (int) ($rows[$key]->revenue ?? 0)];
        }

        return $result;
    }

    public function byType(int $branchId, Carbon $from, Carbon $to): array
    {
        return $this->completedGroupedBy('type', ['takeaway', 'delivery'], $branchId, $from, $to);
    }

    public function byPayment(int $branchId, Carbon $from, Carbon $to): array
    {
        return $this->completedGroupedBy('payment_method', ['cash', 'momo', 'vnpay'], $branchId, $from, $to);
    }

    public function topItems(int $branchId, Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', '>=', $from->toDateString())
            ->whereDate('orders.created_at', '<=', $to->toDateString())
            ->groupBy('order_items.menu_item_id', 'order_items.name_snapshot')
            ->selectRaw('order_items.name_snapshot as name, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as revenue')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }
}
