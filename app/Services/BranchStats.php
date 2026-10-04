<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BranchStats
{
    public function today(int $branchId): array
    {
        $row = Order::ofBranch($branchId)
            ->whereDate('created_at', today())
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->first();

        return [
            'revenue'   => (int) $row->revenue,
            'orders'    => (int) $row->orders,
            'pending'   => (int) $row->pending,
            'cancelled' => (int) $row->cancelled,
        ];
    }

    public function revenueLastDays(int $branchId, int $days = 7): array
    {
        $from = today()->subDays($days - 1);

        $rows = Order::ofBranch($branchId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $result[$day] = (int) ($rows[$day] ?? 0);
        }

        return $result;
    }

    public function topItemsToday(int $branchId, int $limit = 5): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', today())
            ->groupBy('order_items.menu_item_id', 'order_items.name_snapshot')
            ->selectRaw('order_items.name_snapshot as name, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as revenue')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }
}
