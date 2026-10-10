<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ManagerAccountController extends AdminController
{
    private const FIELDS = ['name', 'email', 'phone', 'branch_id'];

    private const MESSAGES = [
        'name.required'      => 'Vui lòng nhập họ tên.',
        'email.required'     => 'Vui lòng nhập email.',
        'email.email'        => 'Email không hợp lệ.',
        'email.unique'       => 'Email này đã có người sử dụng.',
        'phone.regex'        => 'Số điện thoại không hợp lệ (VD: 0901234567).',
        'branch_id.required' => 'Vui lòng chọn chi nhánh.',
        'branch_id.exists'   => 'Chi nhánh không tồn tại.',
        'password.required'  => 'Vui lòng nhập mật khẩu.',
        'password.min'       => 'Mật khẩu tối thiểu 8 ký tự.',
        'password.confirmed' => 'Nhập lại mật khẩu không khớp.',
    ];

    private function rules(?User $user = null): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone'     => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ];
    }

    /** Chỉ thao tác trên tài khoản quản lý; user khác coi như không tồn tại */
    private function ensureManager(User $user): void
    {
        abort_unless($user->role === 'manager', 404);
    }

    public function index(Request $request)
    {
        $filters = Validator::make($request->only(['branch_id', 'q']), [
            'branch_id' => ['nullable', 'integer'],
            'q'         => ['nullable', 'string', 'max:50'],
        ])->valid();

        $managers = User::where('role', 'manager')
            ->with('branch')
            ->when($filters['branch_id'] ?? null, fn ($q, $id) => $q->where('branch_id', $id))
            ->when(trim($filters['q'] ?? ''), fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->orderByDesc('is_active')->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.managers.index', [
            'managers' => $managers,
            'branches' => Branch::orderBy('name')->get(),
            'filters'  => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.managers.form', ['user' => null, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules() + [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], self::MESSAGES);

        // role, is_active do server quyết định — bỏ qua mọi giá trị client gửi lên
        $user = User::create(Arr::only($data, [...self::FIELDS, 'password']) + ['role' => 'manager', 'is_active' => true]);
        $this->log('manager.create', $user, ['after' => Arr::only($data, self::FIELDS)]);

        return redirect()->route('admin.managers.index')->with('success', "Đã tạo tài khoản quản lý cho {$user->name}.");
    }

    public function edit(User $user)
    {
        $this->ensureManager($user);

        return view('admin.managers.form', ['user' => $user, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureManager($user);

        $user->fill(Arr::only($request->validate($this->rules($user), self::MESSAGES), self::FIELDS));
        $changes = AdminLog::diff($user);
        $user->save();
        $this->log('manager.update', $user, $changes);

        return redirect()->route('admin.managers.index')->with('success', "Đã cập nhật {$user->name}.");
    }

    public function toggleLock(User $user)
    {
        $this->ensureManager($user);

        $user->is_active = !$user->is_active;
        $changes = AdminLog::diff($user);
        $user->save();
        $this->log($user->is_active ? 'manager.unlock' : 'manager.lock', $user, $changes);

        return back()->with('success', $user->is_active ? "Đã mở khoá {$user->name}." : "Đã khoá {$user->name}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->ensureManager($user);

        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']], self::MESSAGES);
        $user->update(['password' => $data['password']]); // cast 'hashed' tự mã hoá
        $this->log('manager.password', $user);

        return back()->with('success', "Đã đặt lại mật khẩu cho {$user->name}.");
    }
}
