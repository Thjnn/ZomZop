<?php

namespace App\Http\Controllers\Manager;

use App\Models\Branch;

class DashboardController extends ManagerController
{
    public function index()
    {
        $branch = Branch::findOrFail($this->branchId());

        return view('manager.dashboard', compact('branch'));
    }
}
