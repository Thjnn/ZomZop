<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\User;
use App\Services\BranchReport;
use App\Services\BranchStats;

class DashboardController extends AdminController
{
    public function index(BranchStats $stats, BranchReport $report)
    {
        return view('admin.dashboard', [
            'today'        => $stats->statusCounts(null, 'today'),
            'month'        => $stats->statusCounts(null, 'month'),
            'ranking'      => $report->byBranch(today()->startOfMonth(), today()),
            'branches'     => Branch::where('is_active', true)->count(),
            'newCustomers' => User::where('role', 'customer')->where('created_at', '>=', today()->startOfMonth())->count(),
            'series'       => $stats->series(null, 'week'),
        ]);
    }
}
