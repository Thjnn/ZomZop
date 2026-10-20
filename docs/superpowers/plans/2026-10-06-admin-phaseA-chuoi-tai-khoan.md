# Admin — Phase A: Khung admin, chi nhánh, tài khoản, khách hàng — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
>
> **Cho thành viên nhóm code tay:** làm lần lượt từng Task, mỗi Step tick `[x]`. Viết test trước → chạy thấy FAIL → viết code → chạy thấy PASS → commit. Không push khi chưa hỏi nhóm trưởng.

**Goal:** Tạo khu `/admin` (layout, phân quyền, nhật ký thao tác) và các trang quản lý chi nhánh, tài khoản quản lý, nhân viên toàn chuỗi (chỉ xem), khách hàng.

**Architecture:** `routes/admin.php` + `App\Http\Controllers\Admin\*` kế thừa `AdminController` (có hàm `log()`), view trong `resources/views/admin/*` dùng `layouts/admin.blade.php`. Mọi action ghi gọi `AdminLog::record()`.

**Tech Stack:** Laravel 13, PHP 8.3, Blade + Tailwind (Vite), PHPUnit (SQLite in-memory).

**Spec:** `docs/superpowers/specs/2026-10-06-admin-design.md`

## Global Constraints

- Chạy lệnh PHP bằng `php` của Laragon (trên máy nhóm trưởng: `E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`). Test: `php artisan test --filter=<Tên>`.
- Mọi route admin: middleware `['auth', 'role:admin']`, prefix `admin`, tên `admin.*`.
- Thông báo lỗi validate bằng tiếng Việt, cùng giọng với `Manager\StaffController::MESSAGES`.
- Không xoá cứng chi nhánh/tài khoản — chỉ `is_active`.
- Không ghi mật khẩu vào `admin_logs`.
- Không sửa file migration đã có trên `main`; muốn đổi bảng thì tạo migration mới.
- Test đặt ở `tests/Feature/Admin/`, dùng `RefreshDatabase` + trait `Tests\Feature\Manager\CreatesBranchData`, `setUp()` gọi `$this->withoutVite()`.
- User factory có mật khẩu mặc định `password`.

## Review Focus

1. Manager hoặc khách gọi thẳng URL `/admin/...` (kể cả POST/PUT) → phải 403, không chỉ ẩn menu. (Task 1, test cho cả route ghi ở Task 2–4.)
2. Admin sửa/khoá một user không phải manager qua URL `/admin/managers/{id}` (vd. id của khách) → 404. (Task 3)
3. Khách đang có giỏ hàng ở chi nhánh vừa bị đóng bấm Đặt hàng → không tạo đơn, được đưa về chọn chi nhánh. (Task 2)
4. Chuyển manager sang chi nhánh khác → lần tải trang kế tiếp manager thấy chi nhánh mới (không cần đăng nhập lại) và nhật ký có before/after `branch_id`. (Task 3)
5. Admin bị khoá giữa chừng (session cũ còn) → 403 ở `/admin`. (Task 1)

---

## File Structure

| File | Trách nhiệm |
|------|-------------|
| `database/migrations/2026_10_07_000001_create_admin_logs_table.php` | Bảng nhật ký |
| `app/Models/AdminLog.php` | Model + `record()` + `diff()` |
| `routes/admin.php` | Toàn bộ route admin (các phase sau thêm vào đây) |
| `bootstrap/app.php` | Đăng ký `routes/admin.php` |
| `app/Http/Controllers/Admin/AdminController.php` | Lớp cha, hàm `log()` |
| `app/Http/Controllers/Admin/DashboardController.php` | Trang tổng quan (Phase C sẽ làm đầy đủ) |
| `resources/views/layouts/admin.blade.php` | Layout + sidebar admin |
| `resources/views/admin/dashboard.blade.php` | Trang tổng quan tạm |
| `app/Http/Controllers/Admin/BranchController.php` + `resources/views/admin/branches/{index,form}.blade.php` | Chi nhánh |
| `app/Http/Controllers/Admin/ManagerAccountController.php` + `resources/views/admin/managers/{index,form}.blade.php` | Tài khoản quản lý |
| `app/Http/Controllers/Admin/StaffController.php` + `resources/views/admin/staff/index.blade.php` | Nhân viên toàn chuỗi (chỉ xem) |
| `app/Http/Controllers/Admin/CustomerController.php` + `resources/views/admin/customers/{index,show}.blade.php` | Khách hàng |
| `app/Http/Controllers/BranchController.php`, `app/Http/Controllers/CheckoutController.php` | Chặn chi nhánh đã đóng phía khách |

---

### Task 1: Khung admin + nhật ký thao tác

**Files:**
- Create: `database/migrations/2026_10_07_000001_create_admin_logs_table.php`
- Create: `app/Models/AdminLog.php`
- Create: `routes/admin.php`
- Modify: `bootstrap/app.php` (khối `then:`)
- Create: `app/Http/Controllers/Admin/AdminController.php`
- Create: `app/Http/Controllers/Admin/DashboardController.php`
- Create: `resources/views/layouts/admin.blade.php`
- Create: `resources/views/admin/dashboard.blade.php`
- Test: `tests/Feature/Admin/AccessTest.php`, `tests/Feature/Admin/AdminLogTest.php`

**Interfaces:**
- Produces:
  - `AdminLog::record(string $action, ?Model $subject = null, array $changes = []): AdminLog`
  - `AdminLog::diff(Model $model): array` — gọi **sau `fill()` và trước `save()`**, trả `['before' => [...], 'after' => [...]]` chỉ gồm cột bị đổi (bỏ `password`, `remember_token`, `updated_at`).
  - `Admin\AdminController::log(string $action, ?Model $subject = null, array $changes = []): void`
  - Route `admin.dashboard` (`GET /admin`).
  - Layout `layouts.admin` với `@yield('title')`, `@yield('content')`, `@stack('scripts')`.

- [ ] **Step 1: Viết test phân quyền (FAIL)**

`tests/Feature/Admin/AccessTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_customer_and_manager_get_403(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('customer'))->get('/admin')->assertForbidden();
        $this->actingAs($this->makeUser('manager', $branch))->get('/admin')->assertForbidden();
        $this->actingAs($this->makeUser('staff', $branch))->get('/admin')->assertForbidden();
    }

    public function test_locked_admin_with_existing_session_gets_403(): void
    {
        $admin = $this->makeUser('admin');
        $admin->forceFill(['is_active' => false])->save();

        $this->actingAs($admin)->get('/admin')->assertForbidden();
    }

    public function test_admin_sees_dashboard(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get('/admin')
            ->assertOk()
            ->assertSee('Quản trị toàn chuỗi');
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = $this->makeUser('admin');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_cannot_use_manager_area(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/manager')->assertForbidden();
    }
}
```

`tests/Feature/Admin/AdminLogTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class AdminLogTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    public function test_record_saves_actor_subject_and_changes(): void
    {
        $admin  = $this->makeUser('admin');
        $branch = $this->makeBranch('Cũ');
        $this->actingAs($admin);

        $branch->fill(['name' => 'Mới']);
        $changes = AdminLog::diff($branch);
        $branch->save();
        AdminLog::record('branch.update', $branch, $changes);

        $log = AdminLog::sole();
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('branch.update', $log->action);
        $this->assertSame('Branch', $log->subject_type);
        $this->assertSame($branch->id, $log->subject_id);
        $this->assertSame(['before' => ['name' => 'Cũ'], 'after' => ['name' => 'Mới']], $log->changes);
    }

    public function test_diff_never_contains_password(): void
    {
        $user = $this->makeUser('manager');
        $user->fill(['password' => 'matkhaumoi123', 'name' => 'Tên mới']);

        $changes = AdminLog::diff($user);

        $this->assertArrayNotHasKey('password', $changes['after']);
        $this->assertArrayNotHasKey('password', $changes['before']);
        $this->assertSame('Tên mới', $changes['after']['name']);
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter="Tests\\Feature\\Admin"`
Expected: FAIL (route `/admin` không tồn tại → 404; class `AdminLog` không tồn tại).

- [ ] **Step 3: Migration + model nhật ký**

`database/migrations/2026_10_07_000001_create_admin_logs_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50)->comment('VD: branch.update, coupon.create');
            $table->string('subject_type', 50)->nullable()->comment('Tên model, VD: Branch');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('changes')->nullable()->comment('{before: {...}, after: {...}}');
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
```

`app/Models/AdminLog.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

/** Nhật ký thao tác của admin. Chỉ thêm, không sửa/xoá. */
class AdminLog extends Model
{
    public const UPDATED_AT = null;

    private const HIDDEN = ['password', 'remember_token', 'updated_at'];

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'changes', 'ip'];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?Model $subject = null, array $changes = []): self
    {
        return static::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'changes'      => $changes ?: null,
            'ip'           => request()->ip(),
        ]);
    }

    /** Gọi sau fill(), trước save(): chỉ các cột bị đổi, không bao giờ có mật khẩu */
    public static function diff(Model $model): array
    {
        $after = Arr::except($model->getDirty(), self::HIDDEN);

        return [
            'before' => Arr::only($model->getOriginal(), array_keys($after)),
            'after'  => $after,
        ];
    }
}
```

> Lưu ý: `getOriginal()` trả giá trị đã cast (vd. `is_active` là `true/false`), `getDirty()` trả giá trị thô. Khi xem nhật ký có thể thấy `true` → `0`; chấp nhận được.

- [ ] **Step 4: Route, controller, đăng ký file route**

`routes/admin.php`:

```php
<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// Khu vực quản trị toàn chuỗi. Các phase sau chỉ THÊM route vào nhóm này.
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });
```

`bootstrap/app.php` — trong `then: function () { ... }`, thêm dòng ngay sau dòng `manager.php`:

```php
            // Khu vực quản trị toàn chuỗi
            Route::middleware('web')->group(base_path('routes/admin.php'));
```

`app/Http/Controllers/Admin/AdminController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Database\Eloquent\Model;

abstract class AdminController extends Controller
{
    protected function log(string $action, ?Model $subject = null, array $changes = []): void
    {
        AdminLog::record($action, $subject, $changes);
    }
}
```

`app/Http/Controllers/Admin/DashboardController.php` (bản tạm — Phase C thay bằng bản đầy đủ):

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;

class DashboardController extends AdminController
{
    public function index()
    {
        return view('admin.dashboard', [
            'branches'     => Branch::where('is_active', true)->count(),
            'managers'     => User::where('role', 'manager')->where('is_active', true)->count(),
            'customers'    => User::where('role', 'customer')->count(),
            'ordersToday'  => Order::whereDate('created_at', today())->count(),
        ]);
    }
}
```

- [ ] **Step 5: Layout admin**

`resources/views/layouts/admin.blade.php` (cùng khung với `layouts/manager.blade.php`, khác phần menu và header):

```blade
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản trị') · ZomZop Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @php
        $_user  = auth()->user();
        $_icons = [
            'home'   => '<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/>',
            'branch' => '<path d="M3 21h18M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/>',
            'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'staff'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
            'orders' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/>',
            'report' => '<path d="M4 19V5M4 19h16"/><path d="M8 16v-4M12 16V8M16 16v-6"/>',
            'money'  => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
            'check'  => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8 12 3 3 5-6"/>',
            'image'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 16-5-5-9 8"/>',
            'ticket' => '<path d="M3 8a2 2 0 0 0 0 4v4h18v-4a2 2 0 0 1 0-4V4H3z"/><path d="M13 4v16"/>',
            'tag'    => '<path d="M3 12V3h9l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
            'menu'   => '<path d="M4 11h16a8 8 0 0 1-16 0Z"/><path d="M12 4v3M8 5l1 2M16 5l-1 2"/>',
            'gear'   => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1 7 17M17 7l2.1-2.1"/>',
            'list'   => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        ];
        // Mục nào chưa có route (phase chưa làm) sẽ tự ẩn nhờ Route::has
        $_nav = [
            'Tổng quan' => [
                ['route' => 'admin.dashboard',         'match' => 'admin.dashboard',     'label' => 'Tổng quan',          'icon' => 'home'],
            ],
            'Chuỗi' => [
                ['route' => 'admin.branches.index',    'match' => 'admin.branches.*',    'label' => 'Chi nhánh',          'icon' => 'branch'],
                ['route' => 'admin.managers.index',    'match' => 'admin.managers.*',    'label' => 'Tài khoản quản lý',  'icon' => 'user'],
                ['route' => 'admin.staff.index',       'match' => 'admin.staff.*',       'label' => 'Nhân viên',          'icon' => 'staff'],
                ['route' => 'admin.customers.index',   'match' => 'admin.customers.*',   'label' => 'Khách hàng',         'icon' => 'user'],
            ],
            'Bán hàng' => [
                ['route' => 'admin.orders.index',      'match' => 'admin.orders.*',      'label' => 'Đơn hàng',           'icon' => 'orders'],
                ['route' => 'admin.reports.index',     'match' => 'admin.reports.*',     'label' => 'Báo cáo',            'icon' => 'report'],
            ],
            'Nhân sự' => [
                ['route' => 'admin.attendances.index', 'match' => 'admin.attendances.*', 'label' => 'Chấm công',          'icon' => 'check'],
                ['route' => 'admin.payrolls.index',    'match' => 'admin.payrolls.*',    'label' => 'Bảng lương',         'icon' => 'money'],
            ],
            'Marketing' => [
                ['route' => 'admin.banners.index',     'match' => 'admin.banners.*',     'label' => 'Banner',             'icon' => 'image'],
                ['route' => 'admin.coupons.index',     'match' => 'admin.coupons.*',     'label' => 'Mã giảm giá',        'icon' => 'ticket'],
            ],
            'Thực đơn' => [
                ['route' => 'admin.categories.index',  'match' => 'admin.categories.*',  'label' => 'Danh mục',           'icon' => 'tag'],
                ['route' => 'admin.menu-items.index',  'match' => 'admin.menu-items.*',  'label' => 'Món ăn',             'icon' => 'menu'],
            ],
            'Hệ thống' => [
                ['route' => 'admin.settings.edit',     'match' => 'admin.settings.*',    'label' => 'Cài đặt chung',      'icon' => 'gear'],
                ['route' => 'admin.logs.index',        'match' => 'admin.logs.*',        'label' => 'Nhật ký thao tác',   'icon' => 'list'],
            ],
        ];
        $_svg = fn ($name, $class = 'w-[18px] h-[18px]') => '<svg class="' . $class . ' shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' . $_icons[$name] . '</svg>';
    @endphp

    <div class="flex min-h-screen">
        <aside class="hidden md:flex w-64 flex-col bg-white border-r border-slate-100 sticky top-0 h-screen">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-5 h-16 shrink-0">
                <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop" class="h-9 w-auto">
                <span class="font-bold text-red-500 text-lg">ZomZop <span class="text-xs text-slate-400 font-semibold">ADMIN</span></span>
            </a>
            <nav class="flex-1 overflow-y-auto px-3 pb-4 text-sm">
                @foreach ($_nav as $group => $items)
                    @php $items = array_filter($items, fn ($i) => Route::has($i['route'])); @endphp
                    @if ($items)
                        <p class="px-3 pt-4 pb-1.5 text-[11px] font-semibold tracking-wider uppercase text-slate-400">{{ $group }}</p>
                        @foreach ($items as $item)
                            @php $_active = request()->routeIs($item['match']); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $_active ? 'bg-red-50 text-red-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                {!! $_svg($item['icon']) !!} <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between gap-4 px-4 md:px-6 sticky top-0 z-20">
                <p class="font-semibold">Quản trị toàn chuỗi</p>
                <details class="relative">
                    <summary class="flex items-center gap-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                        <div class="text-right leading-tight hidden sm:block">
                            <p class="text-sm font-semibold">{{ $_user->name }}</p>
                            <p class="text-xs text-slate-400">Admin</p>
                        </div>
                        <span class="w-10 h-10 rounded-full bg-slate-800 text-white font-bold grid place-items-center">
                            {{ mb_strtoupper(mb_substr($_user->name, 0, 1)) }}
                        </span>
                    </summary>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-slate-100 shadow-lg py-1 text-sm">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="w-full text-left px-4 py-2 hover:bg-slate-50 text-red-600 cursor-pointer">Đăng xuất</button>
                        </form>
                    </div>
                </details>
            </header>

            {{-- Menu ngang cho điện thoại --}}
            <nav class="md:hidden flex gap-2 overflow-x-auto px-4 py-2 bg-white border-b border-slate-100 text-sm">
                @foreach (collect($_nav)->flatten(1) as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="whitespace-nowrap px-3 py-1.5 rounded-full {{ request()->routeIs($item['match']) ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <main class="flex-1 p-4 md:p-6">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-amber-50 text-amber-700 text-sm">{{ session('warning') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm">
                        @foreach ($errors->all() as $e) <p>• {{ $e }}</p> @endforeach
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
```

`resources/views/admin/dashboard.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    <h1 class="text-xl font-bold mb-4">Tổng quan</h1>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([['Chi nhánh đang mở', $branches], ['Quản lý đang hoạt động', $managers], ['Khách hàng', $customers], ['Đơn hôm nay', $ordersToday]] as [$label, $value])
            <div class="bg-white rounded-2xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1">{{ number_format($value, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>
@endsection
```

- [ ] **Step 6: Chạy test, thấy PASS**

Run: `php artisan migrate` (DB dev) rồi `php artisan test --filter="Tests\\Feature\\Admin"`
Expected: PASS 8 test.

Kiểm tra tay: đăng nhập `admin@zomzop.com` / mật khẩu trong `UserSeeder` → vào `/admin`, thấy 4 ô số liệu và menu chỉ có "Tổng quan".

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_10_07_000001_create_admin_logs_table.php app/Models/AdminLog.php routes/admin.php bootstrap/app.php app/Http/Controllers/Admin resources/views/layouts/admin.blade.php resources/views/admin tests/Feature/Admin
git commit -m "feat(admin): khung trang admin, phân quyền và nhật ký thao tác"
```

---

### Task 2: Quản lý chi nhánh

**Files:**
- Create: `app/Http/Controllers/Admin/BranchController.php`
- Create: `resources/views/admin/branches/index.blade.php`, `resources/views/admin/branches/form.blade.php`
- Modify: `routes/admin.php`
- Modify: `app/Http/Controllers/BranchController.php` (hàm `confirm`)
- Modify: `app/Http/Controllers/CheckoutController.php` (hàm `store`)
- Test: `tests/Feature/Admin/BranchManageTest.php`

**Interfaces:**
- Consumes: `Admin\AdminController::log()`, `AdminLog::diff()` (Task 1).
- Produces: route `admin.branches.index|create|store|edit|update|toggle`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/BranchManageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class BranchManageTest extends TestCase
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
            'name' => 'ZomZop Mỹ Tho', 'address' => '12 Ấp Bắc, Mỹ Tho', 'phone' => '0273123456',
            'open_time' => '08:00', 'close_time' => '22:00',
        ], $over);
    }

    public function test_manager_cannot_create_branch(): void
    {
        $branch = $this->makeBranch();
        $this->actingAs($this->makeUser('manager', $branch))
            ->post('/admin/branches', $this->validData())
            ->assertForbidden();
    }

    public function test_admin_lists_branches(): void
    {
        $this->makeBranch('ZomZop Bến Tre');
        $this->actingAs($this->makeUser('admin'))
            ->get('/admin/branches')->assertOk()->assertSee('ZomZop Bến Tre');
    }

    public function test_admin_creates_branch_and_it_is_logged(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/branches', $this->validData())
            ->assertRedirect(route('admin.branches.index'));

        $branch = Branch::where('name', 'ZomZop Mỹ Tho')->sole();
        $this->assertTrue((bool) $branch->is_active);
        $this->assertSame('branch.create', AdminLog::sole()->action);
    }

    public function test_validation_rejects_duplicate_name_and_same_hours(): void
    {
        $this->makeBranch('ZomZop Mỹ Tho');

        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/branches', $this->validData(['close_time' => '08:00']))
            ->assertSessionHasErrors(['name', 'close_time']);
    }

    public function test_update_logs_before_and_after(): void
    {
        $branch = $this->makeBranch('Tên cũ');

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/branches/{$branch->id}", $this->validData(['name' => 'Tên mới']))
            ->assertRedirect(route('admin.branches.index'));

        $this->assertSame('Tên mới', $branch->fresh()->name);
        $log = AdminLog::where('action', 'branch.update')->sole();
        $this->assertSame('Tên cũ', $log->changes['before']['name']);
        $this->assertSame('Tên mới', $log->changes['after']['name']);
    }

    public function test_toggle_closes_and_reopens_branch(): void
    {
        $branch = $this->makeBranch();
        $admin  = $this->makeUser('admin');

        $this->actingAs($admin)->patch("/admin/branches/{$branch->id}/toggle")->assertRedirect();
        $this->assertFalse((bool) $branch->fresh()->is_active);

        $this->actingAs($admin)->patch("/admin/branches/{$branch->id}/toggle")->assertRedirect();
        $this->assertTrue((bool) $branch->fresh()->is_active);
    }

    public function test_closing_branch_warns_about_open_orders(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['status' => 'cooking']);

        $this->actingAs($this->makeUser('admin'))
            ->patch("/admin/branches/{$branch->id}/toggle")
            ->assertSessionHas('warning');
    }

    public function test_customer_cannot_select_closed_branch(): void
    {
        $branch = $this->makeBranch();
        $branch->update(['is_active' => false]);

        $this->post('/branches/confirm', ['branch_id' => $branch->id])->assertSessionHasErrors('branch_id');
        $this->assertNull(session('selected_branch_id'));
    }

    public function test_checkout_blocked_when_cart_branch_closed(): void
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $branch->update(['is_active' => false]);

        $this->actingAs($this->makeUser('customer'))
            ->withSession(['cart' => ['branch_id' => $branch->id, 'items' => [
                ['id' => $item->id, 'name' => $item->name, 'price' => 50000, 'quantity' => 1],
            ]]])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash'])
            ->assertRedirect(route('branches.select'));

        $this->assertDatabaseCount('orders', 0);
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=BranchManageTest`
Expected: FAIL (404 ở các route `/admin/branches`, khách vẫn chọn được chi nhánh đóng).

- [ ] **Step 3: Route**

Trong `routes/admin.php`, thêm `use App\Http\Controllers\Admin\BranchController;` ở đầu file và trong group:

```php
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::patch('/branches/{branch}/toggle', [BranchController::class, 'toggle'])->name('branches.toggle');
```

- [ ] **Step 4: Controller**

`app/Http/Controllers/Admin/BranchController.php`:

```php
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
```

`Branch` chưa có quan hệ `users`. Thêm vào `app/Models/Branch.php` (trong class, sau `$fillable`):

```php
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }
```

và thêm `protected $casts = ['is_active' => 'boolean'];` ngay dưới `$fillable`.

- [ ] **Step 5: View danh sách và form**

`resources/views/admin/branches/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Chi nhánh')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Chi nhánh</h1>
        <a href="{{ route('admin.branches.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Thêm chi nhánh</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Tên</th><th class="px-4 py-3">Địa chỉ</th><th class="px-4 py-3">SĐT</th>
                    <th class="px-4 py-3">Giờ mở</th><th class="px-4 py-3">Quản lý</th><th class="px-4 py-3">Nhân viên</th>
                    <th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($branches as $b)
                    <tr class="border-b border-slate-50 {{ $b->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold">{{ $b->name }}</td>
                        <td class="px-4 py-3">{{ $b->address }}</td>
                        <td class="px-4 py-3">{{ $b->phone ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ substr($b->open_time, 0, 5) }} – {{ substr($b->close_time, 0, 5) }}</td>
                        <td class="px-4 py-3">{{ $b->managers_count }}</td>
                        <td class="px-4 py-3">{{ $b->staff_count }}</td>
                        <td class="px-4 py-3">{{ $b->is_active ? 'Đang mở' : 'Đã đóng' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.branches.edit', $b) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.branches.toggle', $b) }}" class="inline"
                                  onsubmit="return {{ $b->is_active ? 'confirm(\'Đóng chi nhánh này? Khách sẽ không đặt được đơn.\')' : 'true' }}">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $b->is_active ? 'Đóng' : 'Mở lại' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Chưa có chi nhánh nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
```

`resources/views/admin/branches/form.blade.php`:

```blade
@extends('layouts.admin')

@section('title', $branch ? 'Sửa chi nhánh' : 'Thêm chi nhánh')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $branch ? 'Sửa chi nhánh' : 'Thêm chi nhánh' }}</h1>

    <form method="POST" action="{{ $branch ? route('admin.branches.update', $branch) : route('admin.branches.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($branch) @method('PUT') @endif

        <label class="grid gap-1">Tên chi nhánh
            <input name="name" value="{{ old('name', $branch?->name) }}" required maxlength="100" class="{{ $input }}">
        </label>
        <label class="grid gap-1">Địa chỉ
            <input name="address" value="{{ old('address', $branch?->address) }}" required maxlength="255" class="{{ $input }}">
        </label>
        <label class="grid gap-1">Số điện thoại
            <input name="phone" value="{{ old('phone', $branch?->phone) }}" class="{{ $input }}">
        </label>
        <div class="grid grid-cols-2 gap-4">
            <label class="grid gap-1">Giờ mở cửa
                <input type="time" name="open_time" value="{{ old('open_time', $branch ? substr($branch->open_time, 0, 5) : '08:00') }}" required class="{{ $input }}">
            </label>
            <label class="grid gap-1">Giờ đóng cửa
                <input type="time" name="close_time" value="{{ old('close_time', $branch ? substr($branch->close_time, 0, 5) : '22:00') }}" required class="{{ $input }}">
            </label>
        </div>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.branches.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
```

- [ ] **Step 6: Chặn chi nhánh đã đóng phía khách**

`app/Http/Controllers/BranchController.php`, hàm `confirm` — thay validate và `findOrFail`:

```php
        $request->validate([
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('is_active', true)],
        ], [
            'branch_id.exists' => 'Chi nhánh này đang tạm đóng, vui lòng chọn chi nhánh khác.',
        ]);

        $branch = Branch::findOrFail($request->branch_id);
```

(thêm `use Illuminate\Validation\Rule;` ở đầu file).

`app/Http/Controllers/CheckoutController.php`, hàm `store` — ngay sau khối `if (empty($cart['items']) || !$cart['branch_id']) { ... }` thêm:

```php
        // Chi nhánh bị admin đóng sau khi khách bỏ món vào giỏ
        if (!Branch::whereKey($cart['branch_id'])->where('is_active', true)->exists()) {
            session()->forget(['cart', 'selected_branch_id', 'selected_branch_name']);

            return redirect()->route('branches.select')
                ->with('error', 'Chi nhánh bạn chọn đã tạm đóng, vui lòng chọn chi nhánh khác.');
        }
```

(thêm `use App\Models\Branch;` ở đầu file).

- [ ] **Step 7: Chạy test, thấy PASS**

Run: `php artisan test --filter=BranchManageTest`
Expected: PASS 9 test. Chạy thêm `php artisan test --filter="Tests\\Feature\\Menu"` để chắc không làm hỏng luồng đặt hàng cũ.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Admin/BranchController.php app/Models/Branch.php resources/views/admin/branches routes/admin.php app/Http/Controllers/BranchController.php app/Http/Controllers/CheckoutController.php tests/Feature/Admin/BranchManageTest.php
git commit -m "feat(admin): quản lý chi nhánh (thêm, sửa, đóng/mở), chặn đặt đơn ở chi nhánh đã đóng"
```

---

### Task 3: Tài khoản quản lý + nhân viên toàn chuỗi (chỉ xem)

**Files:**
- Create: `app/Http/Controllers/Admin/ManagerAccountController.php`
- Create: `resources/views/admin/managers/index.blade.php`, `resources/views/admin/managers/form.blade.php`
- Create: `app/Http/Controllers/Admin/StaffController.php`, `resources/views/admin/staff/index.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/ManagerAccountTest.php`, `tests/Feature/Admin/StaffViewTest.php`

**Interfaces:**
- Consumes: `AdminController::log()`, `AdminLog::diff()`.
- Produces: route `admin.managers.index|create|store|edit|update|lock|password`, `admin.staff.index`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/ManagerAccountTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ManagerAccountTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(int $branchId, array $over = []): array
    {
        return array_merge([
            'name' => 'Trần Quản Lý', 'email' => 'ql.mytho@zomzop.com', 'phone' => '0901234567',
            'branch_id' => $branchId, 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123',
        ], $over);
    }

    public function test_admin_creates_manager(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/managers', $this->validData($branch->id))
            ->assertRedirect(route('admin.managers.index'));

        $user = User::where('email', 'ql.mytho@zomzop.com')->sole();
        $this->assertSame('manager', $user->role);
        $this->assertSame($branch->id, (int) $user->branch_id);
        $this->assertTrue(Hash::check('matkhau123', $user->password));
        $log = AdminLog::where('action', 'manager.create')->sole();
        $this->assertStringNotContainsString('matkhau123', json_encode($log->changes));
    }

    public function test_role_sent_by_client_is_ignored(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/managers', $this->validData($branch->id, ['role' => 'admin']));

        $this->assertSame('manager', User::where('email', 'ql.mytho@zomzop.com')->value('role'));
    }

    public function test_validation_errors(): void
    {
        $this->makeUser('customer')->update(['email' => 'trung@zomzop.com']);

        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/managers', $this->validData(999, ['email' => 'trung@zomzop.com', 'password_confirmation' => 'khac']))
            ->assertSessionHasErrors(['email', 'branch_id', 'password']);
    }

    public function test_transfer_manager_to_other_branch_is_logged(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $manager = $this->makeUser('manager', $a);

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/managers/{$manager->id}", [
                'name' => $manager->name, 'email' => $manager->email, 'phone' => null, 'branch_id' => $b->id,
            ])
            ->assertRedirect(route('admin.managers.index'));

        $this->assertSame($b->id, (int) $manager->fresh()->branch_id);
        $log = AdminLog::where('action', 'manager.update')->sole();
        $this->assertEquals($a->id, $log->changes['before']['branch_id']);
        $this->assertEquals($b->id, $log->changes['after']['branch_id']);

        // Manager thấy chi nhánh mới ngay ở lần tải trang sau
        $this->actingAs($manager->fresh())->get('/manager')->assertOk()->assertSee('Chi nhánh Beta');
    }

    public function test_lock_blocks_manager_immediately(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($this->makeUser('admin'))->patch("/admin/managers/{$manager->id}/lock")->assertRedirect();

        $this->assertFalse((bool) $manager->fresh()->is_active);
        $this->actingAs($manager->fresh())->get('/manager')->assertForbidden();
    }

    public function test_reset_password(): void
    {
        $manager = $this->makeUser('manager', $this->makeBranch());

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/managers/{$manager->id}/password", ['password' => 'moi12345', 'password_confirmation' => 'moi12345'])
            ->assertRedirect();

        $this->assertTrue(Hash::check('moi12345', $manager->fresh()->password));
    }

    public function test_cannot_touch_non_manager_accounts(): void
    {
        $customer = $this->makeUser('customer');
        $admin    = $this->makeUser('admin');

        $this->actingAs($admin)->get("/admin/managers/{$customer->id}/edit")->assertNotFound();
        $this->actingAs($admin)->patch("/admin/managers/{$customer->id}/lock")->assertNotFound();
        $this->actingAs($admin)->patch("/admin/managers/{$admin->id}/lock")->assertNotFound();
    }

    public function test_list_filters_by_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $inA = $this->makeUser('manager', $a);
        $inB = $this->makeUser('manager', $b);

        $this->actingAs($this->makeUser('admin'))
            ->get("/admin/managers?branch_id={$a->id}")
            ->assertOk()->assertSee($inA->email)->assertDontSee($inB->email);
    }
}
```

`tests/Feature/Admin/StaffViewTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class StaffViewTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_staff_of_all_branches_with_filter(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $s1 = $this->makeUser('staff', $a);
        $s2 = $this->makeUser('kitchen', $b);
        $customer = $this->makeUser('customer');
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/staff')
            ->assertOk()->assertSee($s1->email)->assertSee($s2->email)->assertDontSee($customer->email);

        $this->actingAs($admin)->get("/admin/staff?branch_id={$b->id}")
            ->assertOk()->assertDontSee($s1->email)->assertSee($s2->email);
    }

    public function test_page_has_no_edit_actions(): void
    {
        $this->makeUser('staff', $this->makeBranch());

        $this->actingAs($this->makeUser('admin'))->get('/admin/staff')
            ->assertOk()->assertDontSee('Sửa')->assertDontSee('Khoá');
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter="ManagerAccountTest|StaffViewTest"`
Expected: FAIL (404).

- [ ] **Step 3: Route**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\ManagerAccountController;` và `use App\Http\Controllers\Admin\StaffController;`, trong group:

```php
        Route::get('/managers', [ManagerAccountController::class, 'index'])->name('managers.index');
        Route::get('/managers/create', [ManagerAccountController::class, 'create'])->name('managers.create');
        Route::post('/managers', [ManagerAccountController::class, 'store'])->name('managers.store');
        Route::get('/managers/{user}/edit', [ManagerAccountController::class, 'edit'])->name('managers.edit');
        Route::put('/managers/{user}', [ManagerAccountController::class, 'update'])->name('managers.update');
        Route::patch('/managers/{user}/lock', [ManagerAccountController::class, 'toggleLock'])->name('managers.lock');
        Route::put('/managers/{user}/password', [ManagerAccountController::class, 'resetPassword'])->name('managers.password');

        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
```

- [ ] **Step 4: Controller tài khoản quản lý**

`app/Http/Controllers/Admin/ManagerAccountController.php`:

```php
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
```

- [ ] **Step 5: View tài khoản quản lý**

`resources/views/admin/managers/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Tài khoản quản lý')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Tài khoản quản lý</h1>
        <a href="{{ route('admin.managers.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Tạo tài khoản</a>
    </div>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Tất cả chi nhánh</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}" @selected(($filters['branch_id'] ?? null) == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên hoặc email" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Họ tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">SĐT</th><th class="px-4 py-3">Chi nhánh</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($managers as $u)
                    <tr class="border-b border-slate-50 {{ $u->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">{{ $u->phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $u->branch?->name ?? 'Chưa gán' }}</td>
                        <td class="px-4 py-3">{{ $u->is_active ? 'Hoạt động' : 'Đã khoá' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.managers.edit', $u) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.managers.lock', $u) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $u->is_active ? 'Khoá' : 'Mở khoá' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Không có tài khoản nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $managers->links() }}</div>
@endsection
```

`resources/views/admin/managers/form.blade.php`:

```blade
@extends('layouts.admin')

@section('title', $user ? 'Sửa tài khoản quản lý' : 'Tạo tài khoản quản lý')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $user ? 'Sửa tài khoản quản lý' : 'Tạo tài khoản quản lý' }}</h1>

    <form method="POST" action="{{ $user ? route('admin.managers.update', $user) : route('admin.managers.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($user) @method('PUT') @endif

        <label class="grid gap-1">Họ tên <input name="name" value="{{ old('name', $user?->name) }}" required class="{{ $input }}"></label>
        <label class="grid gap-1">Email đăng nhập <input type="email" name="email" value="{{ old('email', $user?->email) }}" required class="{{ $input }}"></label>
        <label class="grid gap-1">Số điện thoại <input name="phone" value="{{ old('phone', $user?->phone) }}" class="{{ $input }}"></label>
        <label class="grid gap-1">Chi nhánh
            <select name="branch_id" required class="{{ $input }}">
                <option value="">— Chọn chi nhánh —</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected(old('branch_id', $user?->branch_id) == $b->id)>{{ $b->name }}{{ $b->is_active ? '' : ' (đã đóng)' }}</option>
                @endforeach
            </select>
        </label>
        @unless ($user)
            <div class="grid grid-cols-2 gap-4">
                <label class="grid gap-1">Mật khẩu <input type="password" name="password" required minlength="8" class="{{ $input }}"></label>
                <label class="grid gap-1">Nhập lại mật khẩu <input type="password" name="password_confirmation" required class="{{ $input }}"></label>
            </div>
        @endunless

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.managers.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>

    @if ($user)
        <form method="POST" action="{{ route('admin.managers.password', $user) }}" class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm mt-6">
            @csrf @method('PUT')
            <h2 class="font-semibold">Đặt lại mật khẩu</h2>
            <div class="grid grid-cols-2 gap-4">
                <input type="password" name="password" placeholder="Mật khẩu mới" required minlength="8" class="{{ $input }}">
                <input type="password" name="password_confirmation" placeholder="Nhập lại" required class="{{ $input }}">
            </div>
            <button class="justify-self-start px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Đặt lại</button>
        </form>
    @endif
@endsection
```

- [ ] **Step 6: Nhân viên toàn chuỗi (chỉ xem)**

`app/Http/Controllers/Admin/StaffController.php`:

```php
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
```

`resources/views/admin/staff/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Nhân viên')

@section('content')
    <h1 class="text-xl font-bold mb-1">Nhân viên toàn chuỗi</h1>
    <p class="text-sm text-slate-500 mb-4">Chỉ xem. Thêm, sửa, khoá nhân viên do quản lý từng chi nhánh thực hiện.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Tất cả chi nhánh</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}" @selected(($filters['branch_id'] ?? null) == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên hoặc email" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Họ tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Chi nhánh</th><th class="px-4 py-3">Vai trò</th><th class="px-4 py-3">Lương/giờ</th><th class="px-4 py-3">Trạng thái</th></tr>
            </thead>
            <tbody>
                @forelse ($staff as $u)
                    <tr class="border-b border-slate-50 {{ $u->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">{{ $u->branch?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $roles[$u->role] ?? $u->role }}</td>
                        <td class="px-4 py-3">{{ $u->latestSalary ? number_format($u->latestSalary->rate, 0, ',', '.') . 'đ' : '—' }}</td>
                        <td class="px-4 py-3">{{ $u->is_active ? 'Đang làm' : 'Đã nghỉ' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Không có nhân viên nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $staff->links() }}</div>
@endsection
```

- [ ] **Step 7: Chạy test, thấy PASS**

Run: `php artisan test --filter="ManagerAccountTest|StaffViewTest"`
Expected: PASS 10 test.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Admin/ManagerAccountController.php app/Http/Controllers/Admin/StaffController.php resources/views/admin/managers resources/views/admin/staff routes/admin.php tests/Feature/Admin/ManagerAccountTest.php tests/Feature/Admin/StaffViewTest.php
git commit -m "feat(admin): tài khoản quản lý (tạo, sửa, chuyển chi nhánh, khoá, đặt lại mật khẩu) và xem nhân viên toàn chuỗi"
```

---

### Task 4: Khách hàng

**Files:**
- Create: `app/Http/Controllers/Admin/CustomerController.php`
- Create: `resources/views/admin/customers/index.blade.php`, `resources/views/admin/customers/show.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/CustomerManageTest.php`

**Interfaces:**
- Consumes: `AdminController::log()`, `AdminLog::diff()`; partial `manager.partials.status-badge` (có sẵn, nhận biến `$status`).
- Produces: route `admin.customers.index|show|lock`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/CustomerManageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CustomerManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_only_customers_with_spending(): void
    {
        $branch   = $this->makeBranch();
        $customer = $this->makeUser('customer');
        $this->makeOrder($branch, ['user_id' => $customer->id, 'status' => 'completed', 'total' => 120000]);
        $this->makeOrder($branch, ['user_id' => $customer->id, 'status' => 'cancelled', 'total' => 999000]);
        $staff = $this->makeUser('staff', $branch);

        $this->actingAs($this->makeUser('admin'))->get('/admin/customers')
            ->assertOk()
            ->assertSee($customer->email)
            ->assertSee('120.000')          // chỉ cộng đơn hoàn thành
            ->assertDontSee('999.000')
            ->assertDontSee($staff->email);
    }

    public function test_search_by_phone(): void
    {
        $a = $this->makeUser('customer');
        $a->update(['phone' => '0909111222']);
        $b = $this->makeUser('customer');

        $this->actingAs($this->makeUser('admin'))->get('/admin/customers?q=0909111222')
            ->assertOk()->assertSee($a->email)->assertDontSee($b->email);
    }

    public function test_show_lists_customer_orders(): void
    {
        $branch   = $this->makeBranch();
        $customer = $this->makeUser('customer');
        $order    = $this->makeOrder($branch, ['user_id' => $customer->id]);

        $this->actingAs($this->makeUser('admin'))->get("/admin/customers/{$customer->id}")
            ->assertOk()->assertSee($order->order_code);
    }

    public function test_lock_customer_blocks_login(): void
    {
        $customer = $this->makeUser('customer');

        $this->actingAs($this->makeUser('admin'))->patch("/admin/customers/{$customer->id}/lock")->assertRedirect();
        $this->assertFalse((bool) $customer->fresh()->is_active);

        auth()->logout();
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])->assertSessionHasErrors('email');
    }

    public function test_cannot_lock_non_customer(): void
    {
        $manager = $this->makeUser('manager', $this->makeBranch());

        $this->actingAs($this->makeUser('admin'))->patch("/admin/customers/{$manager->id}/lock")->assertNotFound();
        $this->actingAs($this->makeUser('admin'))->get("/admin/customers/{$manager->id}")->assertNotFound();
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=CustomerManageTest`
Expected: FAIL (404).

- [ ] **Step 3: Route**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\CustomerController;`, trong group:

```php
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
        Route::patch('/customers/{user}/lock', [CustomerController::class, 'toggleLock'])->name('customers.lock');
```

- [ ] **Step 4: Controller**

`app/Http/Controllers/Admin/CustomerController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends AdminController
{
    private function ensureCustomer(User $user): void
    {
        abort_unless($user->role === 'customer', 404);
    }

    public function index(Request $request)
    {
        $filters = Validator::make($request->only(['q', 'status']), [
            'q'      => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,locked'],
        ])->valid();

        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum(['orders as spent' => fn ($q) => $q->where('status', 'completed')], 'total')
            ->when(trim($filters['q'] ?? ''), fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")))
            ->when(($filters['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'locked', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers', 'filters'));
    }

    public function show(User $user)
    {
        $this->ensureCustomer($user);

        $orders = $user->orders()->with('branch')->latest()->paginate(15);
        $spent  = (int) $user->orders()->where('status', 'completed')->sum('total');

        return view('admin.customers.show', ['customer' => $user, 'orders' => $orders, 'spent' => $spent]);
    }

    public function toggleLock(User $user)
    {
        $this->ensureCustomer($user);

        $user->is_active = !$user->is_active;
        $changes = AdminLog::diff($user);
        $user->save();
        $this->log($user->is_active ? 'customer.unlock' : 'customer.lock', $user, $changes);

        return back()->with('success', $user->is_active ? "Đã mở khoá {$user->name}." : "Đã khoá {$user->name}.");
    }
}
```

- [ ] **Step 5: View**

`resources/views/admin/customers/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Khách hàng')

@section('content')
    <h1 class="text-xl font-bold mb-4">Khách hàng</h1>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên, email hoặc SĐT" class="px-3 py-2 rounded-lg border border-slate-200">
        <select name="status" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Tất cả</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Đang hoạt động</option>
            <option value="locked" @selected(($filters['status'] ?? '') === 'locked')>Đã khoá</option>
        </select>
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Họ tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">SĐT</th><th class="px-4 py-3">Số đơn</th><th class="px-4 py-3">Đã chi</th><th class="px-4 py-3">Ngày đăng ký</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr class="border-b border-slate-50 {{ $c->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold"><a href="{{ route('admin.customers.show', $c) }}" class="hover:text-red-500">{{ $c->name }}</a></td>
                        <td class="px-4 py-3">{{ $c->email }}</td>
                        <td class="px-4 py-3">{{ $c->phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $c->orders_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ number_format((int) $c->spent, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3">{{ $c->created_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.customers.lock', $c) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $c->is_active ? 'Khoá' : 'Mở khoá' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Không có khách hàng nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
```

`resources/views/admin/customers/show.blade.php`:

```blade
@extends('layouts.admin')

@section('title', $customer->name)

@section('content')
    <a href="{{ route('admin.customers.index') }}" class="text-sm text-slate-500 hover:underline">← Khách hàng</a>
    <div class="bg-white rounded-2xl border border-slate-100 p-6 my-4 grid sm:grid-cols-4 gap-4 text-sm">
        <div><p class="text-xs text-slate-400">Họ tên</p><p class="font-semibold">{{ $customer->name }}</p></div>
        <div><p class="text-xs text-slate-400">Email / SĐT</p><p>{{ $customer->email }}<br>{{ $customer->phone ?? '—' }}</p></div>
        <div><p class="text-xs text-slate-400">Đã chi (đơn hoàn thành)</p><p class="font-semibold">{{ number_format($spent, 0, ',', '.') }}đ</p></div>
        <div><p class="text-xs text-slate-400">Trạng thái</p><p>{{ $customer->is_active ? 'Đang hoạt động' : 'Đã khoá' }}</p></div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Mã đơn</th><th class="px-4 py-3">Chi nhánh</th><th class="px-4 py-3">Ngày</th><th class="px-4 py-3">Tổng</th><th class="px-4 py-3">Trạng thái</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold">
                            @if (Route::has('admin.orders.show'))
                                <a href="{{ route('admin.orders.show', $o) }}" class="hover:text-red-500">{{ $o->order_code }}</a>
                            @else
                                {{ $o->order_code }}
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $o->branch?->name }}</td>
                        <td class="px-4 py-3">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ number_format($o->total, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3">@include('manager.partials.status-badge', ['status' => $o->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Khách chưa có đơn nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
```

- [ ] **Step 6: Chạy test, thấy PASS**

Run: `php artisan test --filter=CustomerManageTest`
Expected: PASS 5 test.

Rồi chạy toàn bộ: `php artisan test` — mọi test cũ vẫn PASS (riêng `Tests\Feature\ExampleTest` đã fail từ trước, không liên quan).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/CustomerController.php resources/views/admin/customers routes/admin.php tests/Feature/Admin/CustomerManageTest.php
git commit -m "feat(admin): quản lý khách hàng (tìm kiếm, chi tiêu, đơn của khách, khoá/mở)"
```

---

## Sau khi xong Phase A

- Cập nhật `routes/console.php`: comment của lệnh `zomzop:manager` sửa thành "Có thể dùng trang Admin → Tài khoản quản lý; lệnh này để dự phòng".
- README: thêm dòng "Admin: `/admin` (tài khoản `admin@zomzop.com`) — chi nhánh, tài khoản quản lý, khách hàng".
