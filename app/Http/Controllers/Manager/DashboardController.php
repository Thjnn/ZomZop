<?php

namespace App\Http\Controllers\Manager;

use App\Models\Branch;
use App\Models\Order;
use App\Services\BranchStats;
use Illuminate\Http\Request;

class DashboardController extends ManagerController
{
    public function index(Request $request, BranchStats $stats)
    {
        $branchId = $this->branchId();
        $period   = $request->string('period')->toString();
        $period   = array_key_exists($period, BranchStats::PERIODS) ? $period : 'all';

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
        ]);
    }
}
