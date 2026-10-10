<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Manager\StaffController as ManagerStaff;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Chỉ xem — thêm/sửa/khoá nhân viên là việc của manager chi nhánh */
class StaffController extends AdminController
{
    public function index(Request $request)
    {
        $filters = Validator::make($request->only(['branch_id', 'q']), [
            'branch_id' => ['nullable', 'integer'],
            'q'         => ['nullable', 'string', 'max:50'],
        ])->valid();

        $staff = User::whereIn('role', array_keys(ManagerStaff::ROLES))
            ->with(['branch', 'latestSalary'])
            ->when($filters['branch_id'] ?? null, fn ($q, $id) => $q->where('branch_id', $id))
            ->when(trim($filters['q'] ?? ''), fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->orderByDesc('is_active')->orderBy('branch_id')->orderBy('name')
            ->paginate(30)->withQueryString();

        return view('admin.staff.index', [
            'staff'    => $staff,
            'roles'    => ManagerStaff::ROLES,
            'branches' => Branch::orderBy('name')->get(),
            'filters'  => $filters,
        ]);
    }
}
