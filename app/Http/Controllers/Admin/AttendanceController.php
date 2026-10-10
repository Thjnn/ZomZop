<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\Branch;
use Illuminate\Http\Request;

/** Chỉ xem — chấm công tay / chấm ra hộ là việc của manager */
class AttendanceController extends AdminController
{
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $branch   = $branches->firstWhere('id', (int) $request->query('branch_id')) ?? $branches->first();
        $date     = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date')) && strtotime($request->query('date'))
            ? $request->query('date')
            : today()->toDateString();

        $attendances = $branch
            ? Attendance::ofBranch($branch->id)->whereDate('check_in', $date)->with(['user', 'shift'])->orderBy('check_in')->get()
            : collect();

        return view('admin.attendances.index', compact('branches', 'branch', 'date', 'attendances'));
    }
}
