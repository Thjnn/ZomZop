<?php

namespace App\Http\Controllers\Manager;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends ManagerController
{
    public const ROLES = ['staff' => 'Nhân viên', 'kitchen' => 'Bếp'];

    private const MESSAGES = [
        'name.required'      => 'Vui lòng nhập họ tên.',
        'email.required'     => 'Vui lòng nhập email.',
        'email.email'        => 'Email không hợp lệ.',
        'email.unique'       => 'Email này đã có người sử dụng.',
        'phone.regex'        => 'Số điện thoại không hợp lệ (VD: 0901234567).',
        'role.in'            => 'Vai trò chỉ được là Nhân viên hoặc Bếp.',
        'password.required'  => 'Vui lòng nhập mật khẩu.',
        'password.min'       => 'Mật khẩu tối thiểu 8 ký tự.',
        'password.confirmed' => 'Nhập lại mật khẩu không khớp.',
    ];

    private function rules(?User $user = null): array
    {
        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'role'  => ['required', Rule::in(array_keys(self::ROLES))],
        ];
    }

    public function index()
    {
        $staff = User::where('branch_id', $this->branchId())
            ->whereIn('role', array_keys(self::ROLES))
            ->orderByDesc('is_active')->orderBy('name')
            ->get();

        return view('manager.staff.index', ['staff' => $staff, 'roles' => self::ROLES]);
    }

    public function create()
    {
        return view('manager.staff.form', ['user' => null, 'roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules() + [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], self::MESSAGES);

        // branch_id, is_active do server quyết định — bỏ qua mọi giá trị client gửi lên
        User::create($data + ['branch_id' => $this->branchId(), 'is_active' => true]);

        return redirect()->route('manager.staff.index')->with('success', "Đã tạo tài khoản cho {$data['name']}.");
    }

    public function edit(User $user)
    {
        $this->ensureOwnStaff($user);

        return view('manager.staff.form', ['user' => $user, 'roles' => self::ROLES]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureOwnStaff($user);

        $data = $request->validate($this->rules($user), self::MESSAGES);
        $user->update($data);

        return redirect()->route('manager.staff.index')->with('success', "Đã cập nhật {$user->name}.");
    }

    public function toggleLock(User $user)
    {
        $this->ensureOwnStaff($user);

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', $user->is_active ? "Đã mở khoá {$user->name}." : "Đã khoá {$user->name}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->ensureOwnStaff($user);

        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']], self::MESSAGES);
        $user->update(['password' => $data['password']]); // cast 'hashed' trong User tự mã hoá

        return back()->with('success', "Đã đặt lại mật khẩu cho {$user->name}.");
    }
}
