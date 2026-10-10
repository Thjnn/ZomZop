<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Chỉ xem — tính/sửa/xác nhận/trả lương là việc của manager */
class PayrollController extends AdminController
{
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $branch   = $branches->firstWhere('id', (int) $request->query('branch_id')) ?? $branches->first();
        $month    = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('month'))
            ? Carbon::createFromFormat('Y-m-d', $request->query('month') . '-01')
            : today()->startOfMonth();

        $payrolls = $branch
            ? Payroll::ofBranch($branch->id)->ofMonth($month->month, $month->year)->with('user')->orderByDesc('total')->get()
            : collect();

        return view('admin.payrolls.index', compact('branches', 'branch', 'month', 'payrolls'));
    }
}
