<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends AdminController
{
    private const OPEN_STATUSES = ['pending', 'confirmed', 'cooking', 'ready'];

    private const MESSAGES = [
        'name.required'          => 'Vui lòng nhập tên chi nhánh.',
        'name.unique'            => 'Tên chi nhánh này đã có.',
        'address.required'       => 'Vui lòng nhập địa chỉ.',
        'phone.regex'            => 'Số điện thoại không hợp lệ (VD: 0273123456).',
        'open_time.required'     => 'Vui lòng nhập giờ mở cửa.',
        'open_time.date_format'  => 'Giờ mở cửa không hợp lệ (HH:MM).',
        'close_time.required'    => 'Vui lòng nhập giờ đóng cửa.',
        'close_time.date_format' => 'Giờ đóng cửa không hợp lệ (HH:MM).',
        'close_time.different'   => 'Giờ đóng cửa phải khác giờ mở cửa.',
    ];

    private function rules(?Branch $branch = null): array
    {
        return [
            'name'       => ['required', 'string', 'max:100', Rule::unique('branches', 'name')->ignore($branch?->id)],
            'address'    => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'open_time'  => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'different:open_time'],
        ];
    }

    public function index()
    {
        $branches = Branch::query()
            ->withCount([
                'users as managers_count' => fn ($q) => $q->where('role', 'manager')->where('is_active', true),
                'users as staff_count'    => fn ($q) => $q->whereIn('role', ['staff', 'kitchen'])->where('is_active', true),
            ])
            ->orderByDesc('is_active')->orderBy('name')
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.form', ['branch' => null]);
    }

    public function store(Request $request)
    {
        $data   = $request->validate($this->rules(), self::MESSAGES);
        $branch = Branch::create($data + ['is_active' => true]);
        $this->log('branch.create', $branch, ['after' => $data]);

        return redirect()->route('admin.branches.index')->with('success', "Đã tạo chi nhánh {$branch->name}.");
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->fill($request->validate($this->rules($branch), self::MESSAGES));
        $changes = AdminLog::diff($branch);
        $branch->save();
        $this->log('branch.update', $branch, $changes);

        return redirect()->route('admin.branches.index')->with('success', "Đã cập nhật {$branch->name}.");
    }

    /** Đóng/mở chi nhánh. Đóng = khách không chọn được; manager vẫn xử lý nốt đơn đang chạy */
    public function toggle(Branch $branch)
    {
        $branch->is_active = !$branch->is_active;
        $changes = AdminLog::diff($branch);
        $branch->save();
        $this->log($branch->is_active ? 'branch.open' : 'branch.close', $branch, $changes);

        $redirect = back()->with('success', $branch->is_active ? "Đã mở lại {$branch->name}." : "Đã đóng {$branch->name}.");

        $open = $branch->is_active ? 0 : Order::ofBranch($branch->id)->whereIn('status', self::OPEN_STATUSES)->count();

        return $open
            ? $redirect->with('warning', "Chi nhánh còn {$open} đơn đang xử lý — quản lý chi nhánh vẫn xử lý được các đơn này.")
            : $redirect;
    }
}
