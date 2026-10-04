<?php

namespace App\Http\Controllers\Manager;

use App\Models\Branch;
use App\Models\Order;
use App\Services\BranchStats;

class DashboardController extends ManagerController
{
    public function index(BranchStats $stats)
    {
        $branchId = $this->branchId();

        return view('manager.dashboard', [
            'branch'        => Branch::findOrFail($branchId),
            'today'         => $stats->today($branchId),
            'revenue7'      => $stats->revenueLastDays($branchId, 7),
            'topItems'      => $stats->topItemsToday($branchId),
            'pendingOrders' => Order::ofBranch($branchId)->pending()->with('user')->oldest()->limit(10)->get(),
        ]);
    }
}
