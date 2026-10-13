<?php

namespace App\Http\Controllers\Manager;

use App\Models\Branch;
use App\Models\Order;
use App\Models\MenuItem;
use App\Services\BranchMenu;
use App\Services\BranchStats;
use Illuminate\Http\Request;

class DashboardController extends ManagerController
{
    public function index(Request $request, BranchStats $stats, BranchMenu $menu)
    {
        $branchId = $this->branchId();
        $period   = $request->string('period')->toString();
        $period   = array_key_exists($period, BranchStats::PERIODS) ? $period : 'today';

        return view('manager.dashboard', [
            'branch'       => Branch::findOrFail($branchId),
            'period'       => $period,
            'counts'       => $stats->statusCounts($branchId, $period),
            'series'       => [
                'year'  => $stats->series($branchId, 'year'),
                'month' => $stats->series($branchId, 'month'),
                'week'  => $stats->series($branchId, 'week'),
            ],
            'recentOrders' => Order::ofBranch($branchId)->latest()->limit(5)->get(),
            // Đơn chờ lâu nhất lên trước, gồm cả đơn tồn từ hôm trước
            'pendingOrders' => Order::ofBranch($branchId)->pending()->with('user')->oldest()->limit(5)->get(),
            // Món chi nhánh đang bán, kèm cờ "ưu đãi từ đầu bếp" để tick trên dashboard
            'chefItems'     => $menu->filterAvailable(MenuItem::query(), $branchId)
                ->withExists(['branchMenuItems as is_chef_pick' => fn ($q) => $q->where('branch_id', $branchId)->where('is_chef_pick', true)])
                ->orderBy('name')->get(['menu_items.id', 'menu_items.name', 'menu_items.discount_percent']),
        ]);
    }
}
