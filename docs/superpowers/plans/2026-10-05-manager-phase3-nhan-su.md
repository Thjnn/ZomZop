# Dashboard Manager — Giai đoạn 3: Nhân Sự — Kế Hoạch Triển Khai

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Mục tiêu:** Manager quản lý nhân viên chi nhánh mình: tạo tài khoản staff/kitchen, sửa, khoá/mở, đặt lại mật khẩu; quản lý ca làm; chấm công tay (vào ca / ra ca) và xem bảng chấm công theo ngày.

**Kiến trúc:** 3 controller trong `App\Http\Controllers\Manager` (`StaffController`, `ShiftController`, `AttendanceController`), dùng chung `ManagerController::branchId()`. Thêm `ensureOwnStaff()` / `ensureOwnShift()` vào controller cha để mọi thao tác chỉ chạm dữ liệu của chi nhánh mình (khác chi nhánh → 404).

**Tech Stack:** Laravel 13, Blade, Tailwind 4, PHPUnit (SQLite in-memory).

**Spec:** Không có file spec. Quyết định của chủ dự án (2026-10-05): *manager được tạo tài khoản cho nhân viên*.

## Quyết định thiết kế

1. **Manager chỉ quản lý tài khoản role `staff` và `kitchen` thuộc chi nhánh mình.** Không tạo được manager/admin, không thấy khách hàng, không chuyển nhân viên sang chi nhánh khác.
2. **Không xoá nhân viên, chỉ khoá** (`is_active = 0`): giữ lịch sử chấm công và lương. Tài khoản bị khoá không đăng nhập được (đã có sẵn ở `AuthController`).
3. **Mật khẩu** tối thiểu 8 ký tự, có ô nhập lại. Manager đặt mật khẩu ban đầu và có thể đặt lại; không xem được mật khẩu cũ.
4. **Staff/kitchen đăng nhập:** hiện chưa có trang riêng nên `route('staff.dashboard')` gây lỗi 500. Sửa: role nào chưa có dashboard thì về trang chủ kèm thông báo "Khu vực nhân viên đang được xây dựng". (Admin cũng hưởng cách này.)
5. **Ca làm:** tên, giờ bắt đầu, giờ kết thúc (HH:MM). Cho phép ca qua đêm (giờ kết thúc nhỏ hơn giờ bắt đầu), không cho hai giờ bằng nhau. **Không xoá được ca đã có chấm công** (bảng `attendances` xoá dây chuyền theo ca — xoá sẽ mất dữ liệu công).
6. **Chấm công tay:** manager chọn nhân viên + ca + giờ vào (+ giờ ra nếu có), `method = manual`. Một nhân viên không được có hai lượt "đang trong ca" (chưa chấm ra) cùng lúc. Nút "Chấm ra" ghi giờ hiện tại.
7. **Lương (`payrolls`, `salary_configs`), chấm công khuôn mặt:** chưa làm ở giai đoạn này.

## Global Constraints

- PHP: `/e/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe` (gọi tắt `$PHP`).
- Mọi test class: `RefreshDatabase`, trait `Tests\Feature\Manager\CreatesBranchData`, `$this->withoutVite()`.
- Chữ hiển thị tiếng Việt. Nhánh `manager-dashboard`, commit sau mỗi task, **không push**.
- Không sửa `routes/web.php`, `layouts/app.blade.php`.

## Review Focus

1. **Manager sửa / khoá / đổi mật khẩu một user không phải nhân viên chi nhánh mình** (khách hàng, manager khác, nhân viên chi nhánh khác) bằng cách đổi ID trên URL: phải 404, DB không đổi. Test ở Task 2.
2. **Gửi `role=manager` hoặc `role=admin`, hoặc `branch_id` khác** khi tạo/sửa nhân viên: bị từ chối hoặc bị bỏ qua. Test ở Task 2.
3. **Staff/kitchen vừa được tạo đăng nhập**: không lỗi 500. Test ở Task 1.
4. **Chấm công cho nhân viên hoặc ca của chi nhánh khác**, hoặc giờ ra trước giờ vào: lỗi tiếng Việt, không tạo bản ghi. Test ở Task 4.
5. **Xoá ca đã có chấm công**: bị chặn, chấm công còn nguyên. Test ở Task 3.

---

## Cấu trúc file

| File | Loại | Trách nhiệm |
|---|---|---|
| `app/Http/Controllers/Auth/AuthController.php` | Sửa | Role chưa có dashboard → trang chủ |
| `app/Http/Controllers/Manager/ManagerController.php` | Sửa | Thêm `ensureOwnStaff()`, `ensureOwnShift()` |
| `app/Http/Controllers/Manager/StaffController.php` | Tạo | Nhân viên |
| `app/Http/Controllers/Manager/ShiftController.php` | Tạo | Ca làm |
| `app/Http/Controllers/Manager/AttendanceController.php` | Tạo | Chấm công |
| `routes/manager.php` | Sửa | Route `manager.staff.*`, `manager.shifts.*`, `manager.attendances.*` |
| `resources/views/manager/staff/{index,form}.blade.php` | Tạo | Danh sách + form tạo/sửa |
| `resources/views/manager/shifts/index.blade.php` | Tạo | Danh sách + form ca |
| `resources/views/manager/attendances/index.blade.php` | Tạo | Bảng chấm công theo ngày + form chấm |
| `resources/views/layouts/manager.blade.php` | Sửa | 3 mục nav mới |
| `tests/Feature/Auth/LoginRedirectTest.php` | Sửa | Test staff đăng nhập |
| `tests/Feature/Manager/StaffManageTest.php` | Tạo | |
| `tests/Feature/Manager/ShiftManageTest.php` | Tạo | |
| `tests/Feature/Manager/AttendanceManageTest.php` | Tạo | |

`CreatesBranchData` có thêm helper (Task 1): `makeShift(Branch $branch, string $start = '08:00', string $end = '14:00', string $name = 'Ca sáng'): Shift`.

---

### Task 1: Staff/kitchen đăng nhập không lỗi + helper test

**Files:**
- Modify: `app/Http/Controllers/Auth/AuthController.php` (`redirectByRole`)
- Modify: `tests/Feature/Manager/CreatesBranchData.php`
- Test: `tests/Feature/Auth/LoginRedirectTest.php`

**Interfaces:**
- Produces: `CreatesBranchData::makeShift()`; session flash `info` khi role chưa có dashboard.

- [ ] **Step 1: Thêm helper** vào trait `CreatesBranchData` (thêm `use App\Models\Shift;`):

```php
    protected function makeShift(Branch $branch, string $start = '08:00', string $end = '14:00', string $name = 'Ca sáng'): Shift
    {
        return Shift::create(['branch_id' => $branch->id, 'name' => $name, 'start_time' => $start, 'end_time' => $end]);
    }
```

- [ ] **Step 2: Viết test (sẽ fail)** — thêm vào `LoginRedirectTest`:

```php
    /** Review Focus #3 */
    public function test_staff_and_kitchen_login_without_dashboard_go_home(): void
    {
        $branch = $this->makeBranch();

        foreach (['staff', 'kitchen'] as $role) {
            $user = $this->makeUser($role, $branch);
            $this->post('/login', ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect(route('home'))
                ->assertSessionHas('info', 'Khu vực nhân viên đang được xây dựng.');
            $this->post('/logout');
        }
    }
```

- [ ] **Step 3: Chạy, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Auth/LoginRedirectTest.php`
Expected: FAIL — `Route [staff.dashboard] not defined`.

- [ ] **Step 4: Sửa `redirectByRole`** — thay khối `return match (...) {...};` bằng:

```php
        if ($user->role === 'customer' || !in_array($user->role, ['admin', 'manager', 'staff', 'kitchen'], true)) {
            return redirect()->to($customerTarget);
        }

        // Role nội bộ chưa có trang riêng (admin/staff/kitchen) → trang chủ, tránh lỗi route không tồn tại
        $route = "{$user->role}.dashboard";

        return Route::has($route)
            ? redirect()->route($route)
            : redirect()->route('home')->with('info', 'Khu vực nhân viên đang được xây dựng.');
```

Thêm `use Illuminate\Support\Facades\Route;` ở đầu file.

- [ ] **Step 5: Chạy toàn bộ test**

Run: `$PHP artisan test`
Expected: PASS trừ `ExampleTest`.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Auth/AuthController.php tests/Feature/Manager/CreatesBranchData.php tests/Feature/Auth/LoginRedirectTest.php
git commit -m "fix(auth): staff/kitchen/admin chưa có dashboard thì về trang chủ thay vì lỗi 500"
```

---

### Task 2: Quản lý nhân viên

**Files:**
- Modify: `app/Http/Controllers/Manager/ManagerController.php`
- Create: `app/Http/Controllers/Manager/StaffController.php`
- Modify: `routes/manager.php`
- Create: `resources/views/manager/staff/index.blade.php`, `resources/views/manager/staff/form.blade.php`
- Modify: `resources/views/layouts/manager.blade.php`
- Test: `tests/Feature/Manager/StaffManageTest.php`

**Interfaces:**
- Produces: `ManagerController::ensureOwnStaff(User $user): void` (404 nếu không phải staff/kitchen của chi nhánh); `StaffController::ROLES = ['staff' => 'Nhân viên', 'kitchen' => 'Bếp']`; routes `manager.staff.index|create|store|edit|update|lock|password`.

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/StaffManageTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(array $over = []): array
    {
        return array_merge([
            'name' => 'Nguyễn Văn Bếp', 'email' => 'bep1@zomzop.com', 'phone' => '0901111222',
            'role' => 'kitchen', 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123',
        ], $over);
    }

    public function test_lists_only_own_branch_staff(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $mine  = $this->makeUser('staff', $a);
        $other = $this->makeUser('staff', $b);
        $cust  = $this->makeUser('customer');

        $this->actingAs($this->makeUser('manager', $a))
            ->get('/manager/staff')
            ->assertOk()
            ->assertSee($mine->email)
            ->assertDontSee($other->email)
            ->assertDontSee($cust->email);
    }

    public function test_create_staff_account_for_own_branch(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))
            ->post('/manager/staff', $this->validData())
            ->assertRedirect(route('manager.staff.index'))
            ->assertSessionHas('success');

        $user = User::where('email', 'bep1@zomzop.com')->first();
        $this->assertSame('kitchen', $user->role);
        $this->assertSame($branch->id, (int) $user->branch_id);
        $this->assertTrue((bool) $user->is_active);
        $this->assertTrue(Hash::check('matkhau123', $user->password));
    }

    /** Review Focus #2 */
    public function test_cannot_create_manager_or_admin_or_other_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $manager = $this->makeUser('manager', $a);

        foreach (['manager', 'admin', 'customer'] as $role) {
            $this->actingAs($manager)->from('/manager/staff/create')
                ->post('/manager/staff', $this->validData(['role' => $role, 'email' => "$role@x.com"]))
                ->assertSessionHasErrors('role');
        }

        $this->actingAs($manager)->post('/manager/staff', $this->validData(['branch_id' => $b->id]));
        $this->assertSame($a->id, (int) User::where('email', 'bep1@zomzop.com')->value('branch_id'));
    }

    public function test_validation_messages(): void
    {
        $branch = $this->makeBranch();
        $taken  = $this->makeUser('customer');

        $this->actingAs($this->makeUser('manager', $branch))->from('/manager/staff/create')
            ->post('/manager/staff', $this->validData(['email' => $taken->email, 'password_confirmation' => 'khac12345', 'password' => 'ngan']))
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_update_lock_and_reset_password(): void
    {
        $branch  = $this->makeBranch();
        $staff   = $this->makeUser('staff', $branch);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)
            ->put("/manager/staff/{$staff->id}", ['name' => 'Tên Mới', 'email' => $staff->email, 'phone' => '', 'role' => 'kitchen'])
            ->assertRedirect(route('manager.staff.index'));
        $this->assertSame('Tên Mới', $staff->fresh()->name);
        $this->assertSame('kitchen', $staff->fresh()->role);

        $this->actingAs($manager)->patch("/manager/staff/{$staff->id}/lock");
        $this->assertFalse((bool) $staff->fresh()->is_active);
        $this->actingAs($manager)->patch("/manager/staff/{$staff->id}/lock");
        $this->assertTrue((bool) $staff->fresh()->is_active);

        $this->actingAs($manager)
            ->put("/manager/staff/{$staff->id}/password", ['password' => 'moi12345678', 'password_confirmation' => 'moi12345678'])
            ->assertSessionHas('success');
        $this->assertTrue(Hash::check('moi12345678', $staff->fresh()->password));
    }

    /** Review Focus #1 */
    public function test_cannot_touch_users_outside_own_staff(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $manager = $this->makeUser('manager', $a);
        $targets = [$this->makeUser('staff', $b), $this->makeUser('customer'), $this->makeUser('manager', $a)];

        foreach ($targets as $t) {
            $this->actingAs($manager)->get("/manager/staff/{$t->id}/edit")->assertNotFound();
            $this->actingAs($manager)->put("/manager/staff/{$t->id}", ['name' => 'Hack', 'email' => $t->email, 'role' => 'staff'])->assertNotFound();
            $this->actingAs($manager)->patch("/manager/staff/{$t->id}/lock")->assertNotFound();
            $this->actingAs($manager)->put("/manager/staff/{$t->id}/password", ['password' => 'hack12345', 'password_confirmation' => 'hack12345'])->assertNotFound();
            $this->assertNotSame('Hack', $t->fresh()->name);
            $this->assertTrue((bool) $t->fresh()->is_active);
        }
    }
}
```

- [ ] **Step 2: Chạy, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Manager/StaffManageTest.php`
Expected: FAIL — 404 vì chưa có route.

- [ ] **Step 3: Thêm vào `ManagerController`** (thêm `use App\Models\User;`):

```php
    /** Chỉ staff/kitchen của chi nhánh mình; còn lại coi như không tồn tại */
    protected function ensureOwnStaff(User $user): void
    {
        abort_unless(
            in_array($user->role, ['staff', 'kitchen'], true) && (int) $user->branch_id === $this->branchId(),
            404
        );
    }
```

- [ ] **Step 4: Thêm route** (thêm `use App\Http\Controllers\Manager\StaffController;`):

```php
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{user}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
        Route::patch('/staff/{user}/lock', [StaffController::class, 'toggleLock'])->name('staff.lock');
        Route::put('/staff/{user}/password', [StaffController::class, 'resetPassword'])->name('staff.password');
```

- [ ] **Step 5: Tạo `StaffController`**

`app/Http/Controllers/Manager/StaffController.php`:

```php
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
```

- [ ] **Step 6: Tạo view danh sách**

`resources/views/manager/staff/index.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Nhân viên')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Nhân viên chi nhánh</h1>
        <a href="{{ route('manager.staff.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Tạo tài khoản</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Họ tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">SĐT</th>
                    <th class="px-4 py-3">Vai trò</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staff as $u)
                    <tr class="border-b border-slate-50 {{ $u->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">{{ $u->phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $roles[$u->role] ?? $u->role }}</td>
                        <td class="px-4 py-3">{{ $u->is_active ? 'Đang làm' : 'Đã khoá' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('manager.staff.edit', $u) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('manager.staff.lock', $u) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $u->is_active ? 'Khoá' : 'Mở khoá' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Chưa có nhân viên nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 7: Tạo view form (tạo + sửa + đặt lại mật khẩu)**

`resources/views/manager/staff/form.blade.php`:

```blade
@extends('layouts.manager')

@section('title', $user ? 'Sửa nhân viên' : 'Tạo tài khoản')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 text-sm'; @endphp

    <a href="{{ route('manager.staff.index') }}" class="text-sm text-slate-500 hover:text-red-500">← Nhân viên</a>
    <h1 class="text-xl font-bold mt-2 mb-4">{{ $user ? 'Sửa: ' . $user->name : 'Tạo tài khoản nhân viên' }}</h1>

    <div class="grid lg:grid-cols-2 gap-6">
        <form method="POST" action="{{ $user ? route('manager.staff.update', $user) : route('manager.staff.store') }}"
              class="bg-white rounded-2xl p-5 border border-slate-100 space-y-3 text-sm">
            @csrf
            @if ($user) @method('PUT') @endif
            <label class="block">Họ tên <input name="name" value="{{ old('name', $user?->name) }}" class="{{ $input }} mt-1" required></label>
            <label class="block">Email đăng nhập <input type="email" name="email" value="{{ old('email', $user?->email) }}" class="{{ $input }} mt-1" required></label>
            <label class="block">Số điện thoại <input name="phone" value="{{ old('phone', $user?->phone) }}" class="{{ $input }} mt-1"></label>
            <label class="block">Vai trò
                <select name="role" class="{{ $input }} mt-1">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user?->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            @unless ($user)
                <label class="block">Mật khẩu <input type="password" name="password" class="{{ $input }} mt-1" required minlength="8"></label>
                <label class="block">Nhập lại mật khẩu <input type="password" name="password_confirmation" class="{{ $input }} mt-1" required></label>
            @endunless
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">{{ $user ? 'Lưu' : 'Tạo tài khoản' }}</button>
        </form>

        @if ($user)
            <form method="POST" action="{{ route('manager.staff.password', $user) }}" class="bg-white rounded-2xl p-5 border border-slate-100 space-y-3 text-sm h-fit">
                @csrf @method('PUT')
                <h2 class="font-semibold">Đặt lại mật khẩu</h2>
                <input type="password" name="password" placeholder="Mật khẩu mới (tối thiểu 8 ký tự)" class="{{ $input }}" required minlength="8">
                <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu mới" class="{{ $input }}" required>
                <button class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white cursor-pointer">Đặt lại</button>
            </form>
        @endif
    </div>
@endsection
```

- [ ] **Step 8: Thêm mục nav** vào `$_nav` trong layout manager:

```php
            ['route' => 'manager.staff.index',  'match' => 'manager.staff.*',   'label' => 'Nhân viên',  'icon' => '👥'],
```

- [ ] **Step 9: Chạy test, xác nhận pass**

Run: `$PHP artisan test`
Expected: PASS trừ `ExampleTest`.

- [ ] **Step 10: Commit**

```bash
git add app/Http/Controllers/Manager/ManagerController.php app/Http/Controllers/Manager/StaffController.php \
        routes/manager.php resources/views/manager/staff resources/views/layouts/manager.blade.php \
        tests/Feature/Manager/StaffManageTest.php
git commit -m "feat(manager): quản lý tài khoản nhân viên chi nhánh"
```

---

### Task 3: Ca làm

**Files:**
- Modify: `app/Http/Controllers/Manager/ManagerController.php`
- Create: `app/Http/Controllers/Manager/ShiftController.php`
- Modify: `routes/manager.php`
- Create: `resources/views/manager/shifts/index.blade.php`
- Modify: `resources/views/layouts/manager.blade.php`
- Test: `tests/Feature/Manager/ShiftManageTest.php`

**Interfaces:**
- Produces: `ManagerController::ensureOwnShift(Shift $shift): void`; routes `manager.shifts.index|store|update|destroy`.

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/ShiftManageTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_create_list_update_shift(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->post('/manager/shifts', ['name' => 'Ca tối', 'start_time' => '18:00', 'end_time' => '23:00'])
            ->assertSessionHas('success');
        $shift = Shift::where('name', 'Ca tối')->first();
        $this->assertSame($branch->id, (int) $shift->branch_id);

        $this->actingAs($manager)->get('/manager/shifts')->assertSee('Ca tối')->assertSee('18:00');

        $this->actingAs($manager)->put("/manager/shifts/{$shift->id}", ['name' => 'Ca đêm', 'start_time' => '22:00', 'end_time' => '06:00'])
            ->assertSessionHas('success');
        $this->assertSame('Ca đêm', $shift->fresh()->name);
    }

    public function test_invalid_times_rejected(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))->from('/manager/shifts')
            ->post('/manager/shifts', ['name' => 'Lỗi', 'start_time' => '25:00', 'end_time' => '25:00'])
            ->assertSessionHasErrors(['start_time', 'end_time']);
        $this->actingAs($this->makeUser('manager', $branch))->from('/manager/shifts')
            ->post('/manager/shifts', ['name' => 'Lỗi', 'start_time' => '08:00', 'end_time' => '08:00'])
            ->assertSessionHasErrors('end_time');
        $this->assertDatabaseCount('shifts', 0);
    }

    public function test_delete_unused_shift(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch);

        $this->actingAs($this->makeUser('manager', $branch))->delete("/manager/shifts/{$shift->id}")
            ->assertSessionHas('success');
        $this->assertDatabaseCount('shifts', 0);
    }

    /** Review Focus #5 */
    public function test_cannot_delete_shift_with_attendance(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch);
        $staff  = $this->makeUser('staff', $branch);
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id, 'check_in' => now(), 'method' => 'manual']);

        $this->actingAs($this->makeUser('manager', $branch))->from('/manager/shifts')
            ->delete("/manager/shifts/{$shift->id}")
            ->assertSessionHasErrors('shift');
        $this->assertDatabaseCount('shifts', 1);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_other_branch_shift_is_404(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $shift = $this->makeShift($b);
        $manager = $this->makeUser('manager', $a);

        $this->actingAs($manager)->put("/manager/shifts/{$shift->id}", ['name' => 'X', 'start_time' => '08:00', 'end_time' => '09:00'])->assertNotFound();
        $this->actingAs($manager)->delete("/manager/shifts/{$shift->id}")->assertNotFound();
        $this->actingAs($manager)->get('/manager/shifts')->assertDontSee('Ca sáng');
    }
}
```

- [ ] **Step 2: Chạy, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Manager/ShiftManageTest.php`
Expected: FAIL — 404/405, chưa có route.

- [ ] **Step 3: Thêm vào `ManagerController`** (thêm `use App\Models\Shift;`):

```php
    protected function ensureOwnShift(Shift $shift): void
    {
        abort_if((int) $shift->branch_id !== $this->branchId(), 404);
    }
```

- [ ] **Step 4: Thêm route** (thêm `use App\Http\Controllers\Manager\ShiftController;`):

```php
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
```

- [ ] **Step 5: Tạo `ShiftController`**

`app/Http/Controllers/Manager/ShiftController.php`:

```php
<?php

namespace App\Http\Controllers\Manager;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends ManagerController
{
    private function validated(Request $request): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:50'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'different:start_time'],
        ], [
            'name.required'          => 'Vui lòng nhập tên ca.',
            'start_time.date_format' => 'Giờ bắt đầu không hợp lệ (HH:MM).',
            'end_time.date_format'   => 'Giờ kết thúc không hợp lệ (HH:MM).',
            'end_time.different'     => 'Giờ kết thúc phải khác giờ bắt đầu.',
        ]);
    }

    public function index()
    {
        $shifts = Shift::ofBranch($this->branchId())->withCount('attendances')->orderBy('start_time')->get();

        return view('manager.shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Shift::create($data + ['branch_id' => $this->branchId()]);

        return back()->with('success', "Đã thêm {$data['name']}.");
    }

    public function update(Request $request, Shift $shift)
    {
        $this->ensureOwnShift($shift);
        $shift->update($this->validated($request));

        return back()->with('success', "Đã cập nhật {$shift->name}.");
    }

    public function destroy(Shift $shift)
    {
        $this->ensureOwnShift($shift);

        // attendances xoá dây chuyền theo ca → chặn để không mất dữ liệu công
        if ($shift->attendances()->exists()) {
            return back()->withErrors(['shift' => "Không xoá được {$shift->name} vì đã có chấm công. Hãy đổi tên/giờ thay vì xoá."]);
        }

        $shift->delete();

        return back()->with('success', 'Đã xoá ca.');
    }
}
```

- [ ] **Step 6: Tạo view**

`resources/views/manager/shifts/index.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Ca làm')

@section('content')
    @php
        $input = 'px-3 py-1.5 rounded-lg border border-slate-200 text-sm';
        $hm = fn ($t) => substr($t, 0, 5);
    @endphp

    <h1 class="text-xl font-bold mb-4">Ca làm</h1>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto mb-6">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Tên ca · Giờ</th><th class="px-4 py-3">Lượt chấm công</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($shifts as $shift)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('manager.shifts.update', $shift) }}" class="flex flex-wrap items-center gap-2">
                                @csrf @method('PUT')
                                <input name="name" value="{{ $shift->name }}" class="{{ $input }} w-32" required>
                                <input type="time" name="start_time" value="{{ $hm($shift->start_time) }}" class="{{ $input }}" required>
                                <span>→</span>
                                <input type="time" name="end_time" value="{{ $hm($shift->end_time) }}" class="{{ $input }}" required>
                                @if ($shift->end_time < $shift->start_time) <span class="text-xs text-amber-600">qua đêm</span> @endif
                                <button class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white cursor-pointer">Lưu</button>
                            </form>
                        </td>
                        <td class="px-4 py-3">{{ $shift->attendances_count }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($shift->attendances_count === 0)
                                <form method="POST" action="{{ route('manager.shifts.destroy', $shift) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-500 hover:text-red-500 cursor-pointer">Xoá</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Chưa có ca nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('manager.shifts.store') }}" class="bg-white rounded-2xl p-5 border border-slate-100 flex flex-wrap items-end gap-3 text-sm">
        @csrf
        <h2 class="font-semibold w-full">Thêm ca</h2>
        <input name="name" value="{{ old('name') }}" placeholder="VD: Ca sáng" class="{{ $input }}" required>
        <input type="time" name="start_time" value="{{ old('start_time') }}" class="{{ $input }}" required>
        <span>→</span>
        <input type="time" name="end_time" value="{{ old('end_time') }}" class="{{ $input }}" required>
        <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Thêm</button>
    </form>
@endsection
```

- [ ] **Step 7: Thêm mục nav:**

```php
            ['route' => 'manager.shifts.index', 'match' => 'manager.shifts.*',  'label' => 'Ca làm',     'icon' => '🕒'],
```

- [ ] **Step 8: Chạy test** — Run: `$PHP artisan test` — Expected: PASS trừ `ExampleTest`.

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Manager/ManagerController.php app/Http/Controllers/Manager/ShiftController.php \
        routes/manager.php resources/views/manager/shifts resources/views/layouts/manager.blade.php \
        tests/Feature/Manager/ShiftManageTest.php
git commit -m "feat(manager): quản lý ca làm (chặn xoá ca đã có chấm công)"
```

---

### Task 4: Chấm công

**Files:**
- Create: `app/Http/Controllers/Manager/AttendanceController.php`
- Modify: `routes/manager.php`
- Create: `resources/views/manager/attendances/index.blade.php`
- Modify: `resources/views/layouts/manager.blade.php`
- Test: `tests/Feature/Manager/AttendanceManageTest.php`

**Interfaces:**
- Consumes: `ensureOwnStaff()` không dùng (kiểm tra bằng rule `exists` có điều kiện chi nhánh); `CreatesBranchData::makeShift()`.
- Produces: routes `manager.attendances.index` (GET, query `date`), `manager.attendances.store` (POST: `user_id`, `shift_id`, `check_in`, `check_out?`, `note?`; định dạng giờ `Y-m-d\TH:i` của ô `datetime-local`), `manager.attendances.checkout` (PATCH `/manager/attendances/{attendance}/checkout`).

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/AttendanceManageTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-05 15:00'));
    }

    public function test_manual_check_in_and_check_out(): void
    {
        $branch  = $this->makeBranch();
        $shift   = $this->makeShift($branch);
        $staff   = $this->makeUser('staff', $branch);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)
            ->post('/manager/attendances', ['user_id' => $staff->id, 'shift_id' => $shift->id, 'check_in' => '2026-10-05T08:05'])
            ->assertSessionHas('success');

        $att = Attendance::first();
        $this->assertSame('manual', $att->method);
        $this->assertSame($branch->id, (int) $att->branch_id);
        $this->assertNull($att->check_out);

        $this->actingAs($manager)->patch("/manager/attendances/{$att->id}/checkout")->assertSessionHas('success');
        $this->assertSame('2026-10-05 15:00', $att->fresh()->check_out->format('Y-m-d H:i'));

        $this->actingAs($manager)->get('/manager/attendances?date=2026-10-05')
            ->assertSee($staff->name)->assertSee('08:05')->assertSee('15:00')->assertSee('6,92');
    }

    public function test_check_in_with_check_out_at_once(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch);
        $staff  = $this->makeUser('kitchen', $branch);

        $this->actingAs($this->makeUser('manager', $branch))
            ->post('/manager/attendances', ['user_id' => $staff->id, 'shift_id' => $shift->id,
                'check_in' => '2026-10-04T08:00', 'check_out' => '2026-10-04T14:00', 'note' => 'Quên chấm']);

        $this->assertSame(6.0, Attendance::first()->working_hours);
    }

    /** Review Focus #4 */
    public function test_rejects_other_branch_staff_or_shift_and_bad_times(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $myShift    = $this->makeShift($a);
        $otherShift = $this->makeShift($b);
        $myStaff    = $this->makeUser('staff', $a);
        $otherStaff = $this->makeUser('staff', $b);
        $customer   = $this->makeUser('customer');
        $manager    = $this->makeUser('manager', $a);

        $cases = [
            [['user_id' => $otherStaff->id, 'shift_id' => $myShift->id, 'check_in' => '2026-10-05T08:00'], 'user_id'],
            [['user_id' => $customer->id,   'shift_id' => $myShift->id, 'check_in' => '2026-10-05T08:00'], 'user_id'],
            [['user_id' => $myStaff->id, 'shift_id' => $otherShift->id, 'check_in' => '2026-10-05T08:00'], 'shift_id'],
            [['user_id' => $myStaff->id, 'shift_id' => $myShift->id, 'check_in' => '2026-10-05T08:00', 'check_out' => '2026-10-05T07:00'], 'check_out'],
            [['user_id' => $myStaff->id, 'shift_id' => $myShift->id, 'check_in' => 'hôm qua'], 'check_in'],
        ];
        foreach ($cases as [$data, $field]) {
            $this->actingAs($manager)->from('/manager/attendances')->post('/manager/attendances', $data)->assertSessionHasErrors($field);
        }
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_cannot_check_in_twice_while_on_shift(): void
    {
        $branch  = $this->makeBranch();
        $shift   = $this->makeShift($branch);
        $staff   = $this->makeUser('staff', $branch);
        $manager = $this->makeUser('manager', $branch);
        $data    = ['user_id' => $staff->id, 'shift_id' => $shift->id, 'check_in' => '2026-10-05T08:00'];

        $this->actingAs($manager)->post('/manager/attendances', $data);
        $this->actingAs($manager)->from('/manager/attendances')->post('/manager/attendances', $data)
            ->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_checkout_twice_or_other_branch_rejected(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $staffB = $this->makeUser('staff', $b);
        $attB = Attendance::create(['user_id' => $staffB->id, 'branch_id' => $b->id, 'shift_id' => $this->makeShift($b)->id, 'check_in' => now()->subHours(2), 'method' => 'manual']);
        $manager = $this->makeUser('manager', $a);

        $this->actingAs($manager)->patch("/manager/attendances/{$attB->id}/checkout")->assertNotFound();
        $this->assertNull($attB->fresh()->check_out);

        $staffA = $this->makeUser('staff', $a);
        $attA = Attendance::create(['user_id' => $staffA->id, 'branch_id' => $a->id, 'shift_id' => $this->makeShift($a)->id,
            'check_in' => now()->subHours(2), 'check_out' => now()->subHour(), 'method' => 'manual']);
        $this->actingAs($manager)->from('/manager/attendances')->patch("/manager/attendances/{$attA->id}/checkout")
            ->assertSessionHasErrors('attendance');
    }
}
```

- [ ] **Step 2: Chạy, xác nhận fail** — Run: `$PHP artisan test tests/Feature/Manager/AttendanceManageTest.php` — Expected: FAIL, chưa có route.

- [ ] **Step 3: Thêm route** (thêm `use App\Http\Controllers\Manager\AttendanceController;`):

```php
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
        Route::patch('/attendances/{attendance}/checkout', [AttendanceController::class, 'checkout'])->name('attendances.checkout');
```

- [ ] **Step 4: Tạo `AttendanceController`**

`app/Http/Controllers/Manager/AttendanceController.php`:

```php
<?php

namespace App\Http\Controllers\Manager;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttendanceController extends ManagerController
{
    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $date = Validator::make($request->only('date'), ['date' => ['nullable', 'date_format:Y-m-d']])->valid()['date']
            ?? today()->toDateString();

        $attendances = Attendance::ofBranch($branchId)
            ->with(['user', 'shift'])
            ->whereDate('check_in', $date)
            ->orderBy('check_in')
            ->get();

        return view('manager.attendances.index', [
            'date'        => $date,
            'attendances' => $attendances,
            'staff'       => User::where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])->where('is_active', true)->orderBy('name')->get(),
            'shifts'      => Shift::ofBranch($branchId)->orderBy('start_time')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $branchId = $this->branchId();

        $data = $request->validate([
            'user_id'   => ['required', Rule::exists('users', 'id')->where('branch_id', $branchId)->whereIn('role', ['staff', 'kitchen'])],
            'shift_id'  => ['required', Rule::exists('shifts', 'id')->where('branch_id', $branchId)],
            'check_in'  => ['required', 'date_format:Y-m-d\TH:i'],
            'check_out' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:check_in'],
            'note'      => ['nullable', 'string', 'max:255'],
        ], [
            'user_id.exists'        => 'Nhân viên không thuộc chi nhánh của bạn.',
            'shift_id.exists'       => 'Ca làm không thuộc chi nhánh của bạn.',
            'check_in.date_format'  => 'Giờ vào không hợp lệ.',
            'check_out.date_format' => 'Giờ ra không hợp lệ.',
            'check_out.after'       => 'Giờ ra phải sau giờ vào.',
        ]);

        $onShift = Attendance::where('user_id', $data['user_id'])->whereNull('check_out')->exists();
        if ($onShift && empty($data['check_out'])) {
            return back()->withErrors(['user_id' => 'Nhân viên này đang trong ca (chưa chấm ra).'])->withInput();
        }

        Attendance::create([
            'user_id'   => $data['user_id'],
            'branch_id' => $branchId,
            'shift_id'  => $data['shift_id'],
            'check_in'  => Carbon::createFromFormat('Y-m-d\TH:i', $data['check_in']),
            'check_out' => !empty($data['check_out']) ? Carbon::createFromFormat('Y-m-d\TH:i', $data['check_out']) : null,
            'method'    => 'manual',
            'note'      => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Đã chấm công.');
    }

    public function checkout(Attendance $attendance)
    {
        abort_if((int) $attendance->branch_id !== $this->branchId(), 404);

        if ($attendance->check_out) {
            return back()->withErrors(['attendance' => 'Lượt này đã chấm ra rồi.']);
        }

        $attendance->update(['check_out' => now()]);

        return back()->with('success', 'Đã chấm ra lúc ' . now()->format('H:i') . '.');
    }
}
```

- [ ] **Step 5: Tạo view**

`resources/views/manager/attendances/index.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Chấm công')

@section('content')
    @php $input = 'px-3 py-2 rounded-lg border border-slate-200 text-sm'; @endphp

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl font-bold">Chấm công</h1>
        <form method="GET" class="flex items-center gap-2 text-sm">
            <input type="date" name="date" value="{{ $date }}" class="{{ $input }}">
            <button class="px-3 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto mb-6">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Ca</th><th class="px-4 py-3">Vào</th>
                    <th class="px-4 py-3">Ra</th><th class="px-4 py-3">Số giờ</th><th class="px-4 py-3">Cách chấm</th></tr>
            </thead>
            <tbody>
                @forelse ($attendances as $a)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold">{{ $a->user?->name }}</td>
                        <td class="px-4 py-3">{{ $a->shift?->name }}</td>
                        <td class="px-4 py-3">{{ $a->check_in->format('H:i') }}</td>
                        <td class="px-4 py-3">
                            @if ($a->check_out)
                                {{ $a->check_out->format('H:i') }}
                            @else
                                <form method="POST" action="{{ route('manager.attendances.checkout', $a) }}">
                                    @csrf @method('PATCH')
                                    <button class="px-2 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-xs cursor-pointer">Chấm ra</button>
                                </form>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $a->check_out ? number_format($a->working_hours, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $a->method === 'face' ? 'Khuôn mặt' : 'Thủ công' }}@if ($a->note) · {{ $a->note }} @endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Chưa có chấm công ngày này.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('manager.attendances.store') }}" class="bg-white rounded-2xl p-5 border border-slate-100 grid sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
        @csrf
        <h2 class="font-semibold sm:col-span-2 lg:col-span-3">Chấm công thủ công</h2>
        <label class="flex flex-col gap-1">Nhân viên
            <select name="user_id" class="{{ $input }}" required>
                @foreach ($staff as $s) <option value="{{ $s->id }}" @selected(old('user_id') == $s->id)>{{ $s->name }}</option> @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1">Ca
            <select name="shift_id" class="{{ $input }}" required>
                @foreach ($shifts as $sh) <option value="{{ $sh->id }}" @selected(old('shift_id') == $sh->id)>{{ $sh->name }} ({{ substr($sh->start_time, 0, 5) }}–{{ substr($sh->end_time, 0, 5) }})</option> @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1">Giờ vào
            <input type="datetime-local" name="check_in" value="{{ old('check_in', now()->format('Y-m-d\TH:i')) }}" class="{{ $input }}" required>
        </label>
        <label class="flex flex-col gap-1">Giờ ra (để trống nếu đang làm)
            <input type="datetime-local" name="check_out" value="{{ old('check_out') }}" class="{{ $input }}">
        </label>
        <label class="flex flex-col gap-1 sm:col-span-2">Ghi chú
            <input name="note" value="{{ old('note') }}" maxlength="255" class="{{ $input }}">
        </label>
        <div class="flex items-end"><button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer w-full">Chấm công</button></div>
    </form>
@endsection
```

- [ ] **Step 6: Thêm mục nav:**

```php
            ['route' => 'manager.attendances.index', 'match' => 'manager.attendances.*', 'label' => 'Chấm công', 'icon' => '✅'],
```

- [ ] **Step 7: Chạy test** — Run: `$PHP artisan test` — Expected: PASS trừ `ExampleTest`.

- [ ] **Step 8: Kiểm tra tay trên `zomzop.test`:** tạo 1 tài khoản bếp → đăng xuất, đăng nhập bằng tài khoản đó (về trang chủ, có thông báo) → manager khoá tài khoản → đăng nhập lại bị báo khoá → thêm ca → chấm vào, chấm ra.

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Manager/AttendanceController.php routes/manager.php \
        resources/views/manager/attendances resources/views/layouts/manager.blade.php \
        tests/Feature/Manager/AttendanceManageTest.php
git commit -m "feat(manager): chấm công thủ công và bảng chấm công theo ngày"
```
