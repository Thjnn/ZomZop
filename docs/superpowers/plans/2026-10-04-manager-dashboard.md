# Dashboard Quản Lý Chi Nhánh (Manager) — Kế Hoạch Triển Khai

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Mục tiêu:** Manager đăng nhập vào được khu vực `/manager` riêng của chi nhánh mình: xem số liệu trong ngày, xem danh sách đơn và xác nhận / huỷ / chuyển trạng thái đơn.

**Kiến trúc:** Khu vực manager tách khỏi trang khách hàng. Route nằm trong file riêng `routes/manager.php`, được nạp qua `bootstrap/app.php`. Controller đặt trong namespace `App\Http\Controllers\Manager`, layout riêng là `layouts/manager.blade.php`. Quy tắc chuyển trạng thái đơn gom vào một service duy nhất (`OrderStatusService`), service này đồng thời ghi `order_histories`. Số liệu dashboard gom vào `BranchStats`. Mọi truy vấn đều lọc theo `auth()->user()->branch_id`.

**Tech Stack:** Laravel 13 (PHP 8.3), Blade, Tailwind CSS 4 (Vite), MySQL (dev), SQLite in-memory (test, PHPUnit).

**Spec:** Chưa có spec riêng. Các quyết định thiết kế được ghi ngay trong mục "Quyết định thiết kế" bên dưới. Bạn đọc và sửa mục đó trước khi bắt đầu code.

---

## Lộ trình tổng thể

Dashboard manager được chia thành 4 giai đoạn. Mỗi giai đoạn tự chạy được và có plan riêng. **File này chỉ viết chi tiết Giai đoạn 1.**

| Giai đoạn | Nội dung | Bảng dữ liệu dùng | Trạng thái |
|---|---|---|---|
| **1. Khung + Tổng quan + Đơn hàng** | Phân quyền, layout, trang tổng quan, danh sách và chi tiết đơn, đổi trạng thái đơn | `orders`, `order_items`, `order_histories` | **Plan này** |
| 2. Menu & giá chi nhánh | Bật/tắt món, chỉnh giá riêng, tồn kho theo chi nhánh | `branch_menu_items`, `menu_items` | Plan sau |
| 3. Nhân sự | Danh sách nhân viên chi nhánh, ca làm, chấm công (xem + chấm tay) | `users`, `shifts`, `attendances` | Plan sau |
| 4. Báo cáo | Doanh thu theo khoảng ngày, món bán chạy, đánh giá của khách | `orders`, `daily_sales_summary`, `reviews` | Plan sau |

## Quyết định thiết kế (đọc kỹ, sửa nếu không đồng ý)

1. **URL và tên route:** tiền tố `/manager`, tên route bắt đầu bằng `manager.`. Trang chính là `manager.dashboard`, đúng với tên mà `AuthController::redirectByRole()` đang gọi, nên làm xong Task 1 là hết lỗi đăng nhập manager.
2. **Phân quyền:** middleware chung `role:<tên role>`, sau này admin, staff, kitchen dùng lại được. Manager có `branch_id = null` thì bị chặn 403.
3. **Đơn của chi nhánh khác:** trả về **404**, không phải 403, để không lộ việc đơn đó tồn tại.
4. **Luồng trạng thái đơn** (manager được làm tất cả các bước, vì chưa có dashboard bếp):

   ```
   pending ──► confirmed ──► cooking ──► ready ──► completed
      │            │
      └──► cancelled ◄┘
   ```
   - Chỉ huỷ được khi đơn đang `pending` hoặc `confirmed`. Huỷ **bắt buộc nhập lý do**, lý do lưu vào `order_histories.note`.
   - `completed` và `cancelled` là trạng thái cuối, không đổi được nữa.
   - Đơn trả tiền mặt (`cash`) khi chuyển sang `completed` thì `payment_status` tự đổi thành `paid`.
   - Mỗi lần đổi trạng thái ghi một dòng `order_histories` (`from_status`, `to_status`, `changed_by`, `note`).
5. **Doanh thu** = tổng `total` của các đơn **`completed`**, nhóm theo ngày tạo đơn (`created_at`).
6. **Múi giờ:** đổi `config/app.php` từ `UTC` sang `Asia/Ho_Chi_Minh`. Nếu không đổi, khoảng 0h–7h sáng giờ Việt Nam sẽ bị tính là "hôm qua". Thay đổi này ảnh hưởng toàn site: timestamp lưu từ nay sẽ theo giờ Việt Nam. Dữ liệu mẫu cũ lệch tối đa 7 tiếng, chấp nhận được.
7. **Không dùng thư viện biểu đồ.** Biểu đồ doanh thu 7 ngày vẽ bằng thanh `div` Tailwind (YAGNI). Muốn đẹp hơn thì Giai đoạn 4 mới thêm Chart.js.
8. **Tránh xung đột với PR #1 của Tính:** plan này **không sửa** `routes/web.php` và `layouts/app.blade.php`.

## Global Constraints

- PHP chạy bằng: `E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`. Trong Git Bash là `/e/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe`, plan này gọi tắt là `$PHP`.
- Test chạy trên SQLite in-memory (`phpunit.xml`), không đụng database MySQL dev. Mọi test class dùng `RefreshDatabase` và gọi `$this->withoutVite()` trong `setUp()`.
- Toàn bộ chữ hiển thị và thông báo lỗi bằng **tiếng Việt**.
- Tiền định dạng `number_format($v, 0, ',', '.') . 'đ'`, ví dụ `125.000đ`.
- Làm trên nhánh `manager-dashboard`. Commit sau mỗi task.
- Không sửa `routes/web.php` và `resources/views/layouts/app.blade.php`.

## Review Focus

Những tình huống dễ gây lỗi cho người dùng thật. Mỗi dòng đã có test ở task tương ứng:

1. **Manager mở hoặc đổi trạng thái đơn của chi nhánh khác** (sửa ID trên URL): phải 404, dữ liệu không đổi. Test ở Task 4 và Task 5.
2. **Bấm nút hai lần, hoặc hai người cùng xử lý một đơn**, khiến đơn bị chuyển sai bước (ví dụ `confirmed` → `confirmed`): service khoá dòng và kiểm tra lại trạng thái hiện tại, nên lần thứ hai bị từ chối kèm thông báo. Test ở Task 2.
3. **Gửi trạng thái bậy** (`status=abc`, hoặc nhảy cóc `pending` → `completed`): báo lỗi tiếng Việt, đơn không đổi. Test ở Task 2 và Task 5.
4. **Đơn tạo lúc 0h–7h sáng giờ Việt Nam** phải tính vào doanh thu "hôm nay". Test ở Task 3.
5. **Ô tìm kiếm hoặc bộ lọc nhận giá trị lạ** (`status=xyz`, ngày sai định dạng): không lỗi 500, chỉ hiện thông báo lỗi. Test ở Task 4.

---

## Cấu trúc file

| File | Loại | Trách nhiệm |
|---|---|---|
| `app/Http/Middleware/EnsureRole.php` | Tạo | Chặn người dùng không đúng role (403) |
| `bootstrap/app.php` | Sửa | Đăng ký alias `role`, nạp `routes/manager.php` |
| `config/app.php` | Sửa | Múi giờ `Asia/Ho_Chi_Minh` |
| `routes/manager.php` | Tạo | Toàn bộ route `/manager/*` |
| `app/Http/Controllers/Manager/ManagerController.php` | Tạo | Controller cha: `branchId()`, `ensureSameBranch()` |
| `app/Http/Controllers/Manager/DashboardController.php` | Tạo | Trang tổng quan |
| `app/Http/Controllers/Manager/OrderController.php` | Tạo | Danh sách, chi tiết, đổi trạng thái đơn |
| `app/Services/OrderStatusService.php` | Tạo | Luồng trạng thái + ghi lịch sử (nguồn sự thật duy nhất) |
| `app/Services/BranchStats.php` | Tạo | Truy vấn số liệu dashboard |
| `resources/views/layouts/manager.blade.php` | Tạo | Sidebar + topbar khu vực manager |
| `resources/views/manager/dashboard.blade.php` | Tạo | Giao diện tổng quan |
| `resources/views/manager/orders/index.blade.php` | Tạo | Bảng đơn + bộ lọc |
| `resources/views/manager/orders/show.blade.php` | Tạo | Chi tiết đơn + nút đổi trạng thái |
| `resources/views/manager/partials/status-badge.blade.php` | Tạo | Nhãn màu trạng thái (dùng chung) |
| `tests/Feature/Manager/CreatesBranchData.php` | Tạo | Trait tạo dữ liệu test |
| `tests/Feature/Manager/AccessTest.php` | Tạo | Test phân quyền |
| `tests/Feature/Manager/OrderStatusServiceTest.php` | Tạo | Test luồng trạng thái |
| `tests/Feature/Manager/DashboardTest.php` | Tạo | Test số liệu |
| `tests/Feature/Manager/OrderListTest.php` | Tạo | Test danh sách + lọc |
| `tests/Feature/Manager/OrderDetailTest.php` | Tạo | Test chi tiết + đổi trạng thái qua HTTP |

---

### Task 0: Chuẩn bị nhánh

Hiện máy đang có 3 file migration/seeder của bảng `addresses` chưa commit. Các file này thuộc phần việc của Tính, nên commit riêng lên `main` trước để nhánh dashboard sạch.

- [ ] **Step 1: Commit migration addresses lên `main` rồi đưa vào nhánh dashboard**

File chưa commit sẽ tự đi theo khi đổi nhánh, nên chỉ cần:

```bash
git switch main
git add database/migrations/2026_10_04_000001_add_birthday_gender_to_users_table.php \
        database/migrations/2026_10_04_000002_create_addresses_table.php \
        database/seeders/AddressSeeder.php database/seeders/DatabaseSeeder.php
git commit -m "feat: thêm migration addresses, birthday, gender và AddressSeeder"
git switch manager-dashboard
git merge main
```

Lưu ý: không `git add` thư mục `public/uploads/`. Đó là ảnh avatar sinh ra lúc chạy thử.

- [ ] **Step 2: Kiểm tra test chạy được**

Run: `$PHP artisan test`
Expected: chỉ có `ExampleTest` fail với lỗi "no such table: categories". Lỗi này có từ trước, không liên quan dashboard.

---

### Task 1: Phân quyền + route + layout + trang dashboard rỗng

**Files:**
- Create: `app/Http/Middleware/EnsureRole.php`
- Modify: `bootstrap/app.php`
- Modify: `config/app.php:68`
- Create: `routes/manager.php`
- Create: `app/Http/Controllers/Manager/ManagerController.php`
- Create: `app/Http/Controllers/Manager/DashboardController.php` (bản tạm, Task 3 hoàn thiện)
- Create: `resources/views/layouts/manager.blade.php`
- Create: `resources/views/manager/dashboard.blade.php` (bản tạm)
- Create: `tests/Feature/Manager/CreatesBranchData.php`
- Test: `tests/Feature/Manager/AccessTest.php`

**Interfaces:**
- Produces:
  - Middleware alias `role`, dùng dạng `role:manager` hoặc `role:admin,manager`.
  - `ManagerController::branchId(): int`: lấy `branch_id` của manager đang đăng nhập, 403 nếu null.
  - `ManagerController::ensureSameBranch(Order $order): void`: 404 nếu đơn thuộc chi nhánh khác.
  - Route `manager.dashboard` (GET `/manager`).
  - Layout `layouts.manager` với `@yield('title')`, `@yield('content')`. Biến `session('success')` và `$errors` được hiện sẵn.
  - Trait `Tests\Feature\Manager\CreatesBranchData` có `makeBranch()`, `makeUser()`, `makeOrder()`, `makeMenuItem()`, `addItem()`.

- [ ] **Step 1: Tạo trait dữ liệu test**

`tests/Feature/Manager/CreatesBranchData.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\Branch;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesBranchData
{
    protected function makeBranch(string $name = 'Chi nhánh A'): Branch
    {
        return Branch::create(['name' => $name, 'address' => '1 Đường Test']);
    }

    protected function makeUser(string $role, ?Branch $branch = null): User
    {
        return User::factory()->create([
            'role'      => $role,
            'branch_id' => $branch?->id,
            'is_active' => true,
        ]);
    }

    protected function makeOrder(Branch $branch, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_code'     => 'ZZ' . strtoupper(Str::random(8)),
            'user_id'        => $this->makeUser('customer')->id,
            'branch_id'      => $branch->id,
            'type'           => 'takeaway',
            'status'         => 'pending',
            'subtotal'       => 100000,
            'discount'       => 0,
            'total'          => 100000,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'pickup_code'    => 'A01',
        ], $attrs));
    }

    protected function makeMenuItem(string $name = 'Burger Bò'): MenuItem
    {
        $category = Category::firstOrCreate(['slug' => 'test'], ['name' => 'Test']);

        return MenuItem::create([
            'category_id' => $category->id,
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . Str::random(5),
            'base_price'  => 50000,
        ]);
    }

    protected function addItem(Order $order, MenuItem $item, int $qty, int $price = 50000): OrderItem
    {
        return OrderItem::create([
            'order_id'       => $order->id,
            'menu_item_id'   => $item->id,
            'name_snapshot'  => $item->name,
            'price_snapshot' => $price,
            'quantity'       => $qty,
            'subtotal'       => $price * $qty,
        ]);
    }
}
```

- [ ] **Step 2: Viết test phân quyền (sẽ fail)**

`tests/Feature/Manager/AccessTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->get('/manager')->assertRedirect(route('login'));
    }

    public function test_customer_gets_403(): void
    {
        $this->actingAs($this->makeUser('customer'))->get('/manager')->assertForbidden();
    }

    public function test_staff_gets_403(): void
    {
        $branch = $this->makeBranch();
        $this->actingAs($this->makeUser('staff', $branch))->get('/manager')->assertForbidden();
    }

    public function test_manager_without_branch_gets_403(): void
    {
        $this->actingAs($this->makeUser('manager'))->get('/manager')->assertForbidden();
    }

    public function test_manager_with_branch_sees_dashboard(): void
    {
        $branch = $this->makeBranch('Chi nhánh Quận 7');

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager')
            ->assertOk()
            ->assertSee('Chi nhánh Quận 7');
    }

    public function test_manager_login_redirects_to_dashboard(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $this->post('/login', ['email' => $manager->email, 'password' => 'password'])
            ->assertRedirect(route('manager.dashboard'));
    }
}
```

- [ ] **Step 3: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Manager/AccessTest.php`
Expected: FAIL. Các test trả 404 vì chưa có route `/manager`. Test login báo `RouteNotFoundException: Route [manager.dashboard] not defined`.

- [ ] **Step 4: Tạo middleware `EnsureRole`**

`app/Http/Middleware/EnsureRole.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** Dùng: ->middleware('role:manager') hoặc 'role:admin,manager' */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, $roles, true), 403, 'Bạn không có quyền truy cập trang này.');

        return $next($request);
    }
}
```

- [ ] **Step 5: Đăng ký middleware và nạp route manager trong `bootstrap/app.php`**

Thêm `use Illuminate\Support\Facades\Route;` ở đầu file. Thêm tham số `then:` vào `withRouting(...)` và dòng `alias` vào `withMiddleware(...)`. **Giữ nguyên dòng `trustProxies` đang có.**

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Khu vực quản lý chi nhánh, tách file để không đụng routes/web.php
            Route::middleware('web')->group(base_path('routes/manager.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tunnel (cloudflared) chạy ngay trên máy này và chuyển tiếp HTTPS -> HTTP,
        // nên chỉ tin header X-Forwarded-* đến từ loopback.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

- [ ] **Step 6: Đổi múi giờ trong `config/app.php`**

Dòng 68: đổi `'timezone' => 'UTC',` thành:

```php
    'timezone' => env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'),
```

- [ ] **Step 7: Tạo `routes/manager.php`**

Ở task này chỉ có route dashboard. Task 4 và 5 thêm route đơn hàng vào cùng group.

```php
<?php

use App\Http\Controllers\Manager\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });
```

- [ ] **Step 8: Tạo controller cha `ManagerController`**

`app/Http/Controllers/Manager/ManagerController.php`:

```php
<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;

abstract class ManagerController extends Controller
{
    /** Chi nhánh của manager đang đăng nhập */
    protected function branchId(): int
    {
        $branchId = auth()->user()->branch_id;

        abort_if(!$branchId, 403, 'Tài khoản quản lý chưa được gán chi nhánh.');

        return (int) $branchId;
    }

    /** Đơn của chi nhánh khác coi như không tồn tại */
    protected function ensureSameBranch(Order $order): void
    {
        abort_if((int) $order->branch_id !== $this->branchId(), 404);
    }
}
```

- [ ] **Step 9: Tạo `DashboardController` bản tạm**

`app/Http/Controllers/Manager/DashboardController.php`:

```php
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
```

- [ ] **Step 10: Tạo layout `layouts/manager.blade.php`**

```blade
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản lý') · ZomZop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @php
        $_branch = auth()->user()->branch;
        $_nav = [
            ['route' => 'manager.dashboard',    'match' => 'manager.dashboard', 'label' => 'Tổng quan', 'icon' => '📊'],
            ['route' => 'manager.orders.index', 'match' => 'manager.orders.*',  'label' => 'Đơn hàng',  'icon' => '🧾'],
        ];
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar (ẩn trên điện thoại, thay bằng thanh ngang phía trên) --}}
        <aside class="hidden md:flex w-60 flex-col bg-white border-r border-slate-100">
            <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-2 px-5 h-16 border-b border-slate-100">
                <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop" class="h-9 w-auto">
                <span class="font-bold text-red-500">Quản lý</span>
            </a>
            <nav class="flex-1 p-3 space-y-1 text-sm font-medium">
                @foreach ($_nav as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs($item['match']) ? 'bg-red-50 text-red-600' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 md:px-6">
                <div class="min-w-0">
                    <p class="text-[11px] text-slate-400">Chi nhánh</p>
                    <p class="font-semibold truncate">{{ $_branch?->name }}</p>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="hidden sm:inline text-slate-500">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 cursor-pointer">Đăng xuất</button>
                    </form>
                </div>
            </header>

            {{-- Menu ngang cho điện thoại --}}
            <nav class="md:hidden flex gap-2 overflow-x-auto px-4 py-2 bg-white border-b border-slate-100 text-sm">
                @foreach ($_nav as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="whitespace-nowrap px-3 py-1.5 rounded-full {{ request()->routeIs($item['match']) ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $item['icon'] }} {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <main class="flex-1 p-4 md:p-6">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
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

`Route::has(...)` giúp layout chạy được ngay cả khi route `manager.orders.index` chưa tồn tại (trước Task 4).

- [ ] **Step 11: Tạo view dashboard bản tạm**

`resources/views/manager/dashboard.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Tổng quan')

@section('content')
    <h1 class="text-xl font-bold mb-1">Tổng quan</h1>
    <p class="text-sm text-slate-500">{{ $branch->name }} · {{ now()->format('d/m/Y') }}</p>
@endsection
```

- [ ] **Step 12: Chạy test, xác nhận pass**

Run: `$PHP artisan test tests/Feature/Manager/AccessTest.php`
Expected: PASS (6 tests).

- [ ] **Step 13: Kiểm tra bằng tay**

Mở `http://zomzop.test/login`, đăng nhập `manager@zomzop.com` / `12345678`. Trang phải chuyển tới `/manager`, thấy tên chi nhánh ở topbar. Đăng nhập `customer@zomzop.com` rồi mở `/manager`: phải báo 403.

- [ ] **Step 14: Commit**

```bash
git add app/Http/Middleware app/Http/Controllers/Manager bootstrap/app.php config/app.php \
        routes/manager.php resources/views/layouts/manager.blade.php resources/views/manager \
        tests/Feature/Manager
git commit -m "feat(manager): phân quyền role, route /manager và layout dashboard"
```

---

### Task 2: Service luồng trạng thái đơn `OrderStatusService`

**Files:**
- Create: `app/Services/OrderStatusService.php`
- Test: `tests/Feature/Manager/OrderStatusServiceTest.php`

**Interfaces:**
- Consumes: trait `CreatesBranchData` (Task 1).
- Produces:
  - `OrderStatusService::LABELS`: `array<string,string>`, mã trạng thái → nhãn tiếng Việt.
  - `OrderStatusService::TRANSITIONS`: `array<string,string[]>`.
  - `allowedNext(Order $order): string[]`
  - `transition(Order $order, string $to, User $by, ?string $note = null): Order`: trả đơn đã cập nhật. Ném `\InvalidArgumentException` (thông báo tiếng Việt) nếu không được phép chuyển.

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/OrderStatusServiceTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class OrderStatusServiceTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private OrderStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderStatusService();
    }

    public function test_allowed_next_follows_the_flow(): void
    {
        $branch = $this->makeBranch();

        $this->assertSame(['confirmed', 'cancelled'], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'pending'])));
        $this->assertSame(['cooking', 'cancelled'], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'confirmed'])));
        $this->assertSame(['ready'], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'cooking'])));
        $this->assertSame(['completed'], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'ready'])));
        $this->assertSame([], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'completed'])));
        $this->assertSame([], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'cancelled'])));
    }

    public function test_transition_updates_status_and_writes_history(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $order   = $this->makeOrder($branch);

        $updated = $this->service->transition($order, 'confirmed', $manager, 'OK');

        $this->assertSame('confirmed', $updated->status);
        $this->assertDatabaseHas('order_histories', [
            'order_id'    => $order->id,
            'from_status' => 'pending',
            'to_status'   => 'confirmed',
            'changed_by'  => $manager->id,
            'note'        => 'OK',
        ]);
    }

    public function test_skipping_steps_is_rejected(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);

        try {
            $this->service->transition($order, 'completed', $this->makeUser('manager', $branch));
            $this->fail('Phải ném InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Không thể chuyển đơn', $e->getMessage());
        }

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseCount('order_histories', 0);
    }

    public function test_unknown_status_is_rejected(): void
    {
        $branch = $this->makeBranch();

        $this->expectException(InvalidArgumentException::class);
        $this->service->transition($this->makeOrder($branch), 'abc', $this->makeUser('manager', $branch));
    }

    /** Review Focus #2: bấm 2 lần với object cũ trong bộ nhớ */
    public function test_double_submit_with_stale_object_is_rejected(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $order   = $this->makeOrder($branch);  // object vẫn giữ status = pending

        $this->service->transition($order, 'confirmed', $manager);

        $this->expectException(InvalidArgumentException::class);
        $this->service->transition($order, 'confirmed', $manager); // DB đã là confirmed
    }

    public function test_completing_cash_order_marks_it_paid(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['status' => 'ready', 'payment_method' => 'cash']);

        $updated = $this->service->transition($order, 'completed', $this->makeUser('manager', $branch));

        $this->assertSame('paid', $updated->payment_status);
    }

    public function test_completing_momo_order_keeps_payment_status(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['status' => 'ready', 'payment_method' => 'momo']);

        $updated = $this->service->transition($order, 'completed', $this->makeUser('manager', $branch));

        $this->assertSame('unpaid', $updated->payment_status);
    }
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Manager/OrderStatusServiceTest.php`
Expected: FAIL với `Class "App\Services\OrderStatusService" not found`.

- [ ] **Step 3: Viết service**

`app/Services/OrderStatusService.php`:

```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderStatusService
{
    public const LABELS = [
        'pending'   => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'cooking'   => 'Đang nấu',
        'ready'     => 'Sẵn sàng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã huỷ',
    ];

    /** Trạng thái hiện tại => các trạng thái được phép chuyển tới */
    public const TRANSITIONS = [
        'pending'   => ['confirmed', 'cancelled'],
        'confirmed' => ['cooking', 'cancelled'],
        'cooking'   => ['ready'],
        'ready'     => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function allowedNext(Order $order): array
    {
        return self::TRANSITIONS[$order->status] ?? [];
    }

    public function transition(Order $order, string $to, User $by, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $to, $by, $note) {
            // Đọc lại và khoá dòng: chống bấm 2 lần / 2 người xử lý cùng lúc
            $fresh = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from  = $fresh->status;

            if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
                throw new InvalidArgumentException(sprintf(
                    'Không thể chuyển đơn từ "%s" sang "%s".',
                    self::LABELS[$from] ?? $from,
                    self::LABELS[$to] ?? $to
                ));
            }

            $fresh->status = $to;
            if ($to === 'completed' && $fresh->payment_method === 'cash') {
                $fresh->payment_status = 'paid';
            }
            $fresh->save();

            OrderHistory::create([
                'order_id'    => $fresh->id,
                'from_status' => $from,
                'to_status'   => $to,
                'changed_by'  => $by->id,
                'note'        => $note,
            ]);

            return $fresh;
        });
    }
}
```

- [ ] **Step 4: Chạy test, xác nhận pass**

Run: `$PHP artisan test tests/Feature/Manager/OrderStatusServiceTest.php`
Expected: PASS (7 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Services/OrderStatusService.php tests/Feature/Manager/OrderStatusServiceTest.php
git commit -m "feat(manager): service luồng trạng thái đơn + ghi lịch sử"
```

---

### Task 3: Trang tổng quan, số liệu thật `BranchStats`

**Files:**
- Create: `app/Services/BranchStats.php`
- Modify: `app/Http/Controllers/Manager/DashboardController.php`
- Modify: `resources/views/manager/dashboard.blade.php`
- Create: `resources/views/manager/partials/status-badge.blade.php`
- Test: `tests/Feature/Manager/DashboardTest.php`

**Interfaces:**
- Consumes: `ManagerController::branchId()`, `OrderStatusService::LABELS`, trait `CreatesBranchData`.
- Produces:
  - `BranchStats::today(int $branchId): array{revenue:int, orders:int, pending:int, cancelled:int}`. `orders` là mọi đơn tạo hôm nay; `revenue` chỉ tính đơn `completed`.
  - `BranchStats::revenueLastDays(int $branchId, int $days = 7): array<string,int>`, key `Y-m-d` từ cũ đến mới, đủ `$days` ngày (ngày không có đơn = 0).
  - `BranchStats::topItemsToday(int $branchId, int $limit = 5): Collection` gồm các object `{name, qty, revenue}`, chỉ tính đơn `completed`.
  - Partial `manager.partials.status-badge`, nhận biến `$status`.

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/DashboardTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Services\BranchStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_today_counts_only_own_branch_and_completed_revenue(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');

        $this->makeOrder($mine, ['status' => 'completed', 'total' => 120000]);
        $this->makeOrder($mine, ['status' => 'completed', 'total' => 80000]);
        $this->makeOrder($mine, ['status' => 'pending',   'total' => 999000]);
        $this->makeOrder($mine, ['status' => 'cancelled', 'total' => 50000]);
        $this->makeOrder($other, ['status' => 'completed', 'total' => 777000]);

        $this->assertSame(
            ['revenue' => 200000, 'orders' => 4, 'pending' => 1, 'cancelled' => 1],
            (new BranchStats())->today($mine->id)
        );
    }

    /** Review Focus #4: đơn lúc 6h sáng giờ VN vẫn là "hôm nay" */
    public function test_early_morning_vietnam_time_counts_as_today(): void
    {
        $branch = $this->makeBranch();

        $this->travelTo(Carbon::parse('2026-10-04 06:30', 'Asia/Ho_Chi_Minh'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 100000]);

        $this->travelTo(Carbon::parse('2026-10-04 20:00', 'Asia/Ho_Chi_Minh'));
        $this->assertSame(100000, (new BranchStats())->today($branch->id)['revenue']);
    }

    public function test_revenue_last_days_fills_missing_days_with_zero(): void
    {
        $branch = $this->makeBranch();

        $this->travelTo(Carbon::parse('2026-10-02 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 50000]);
        $this->travelTo(Carbon::parse('2026-10-04 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 70000]);

        $this->assertSame([
            '2026-10-02' => 50000,
            '2026-10-03' => 0,
            '2026-10-04' => 70000,
        ], (new BranchStats())->revenueLastDays($branch->id, 3));
    }

    public function test_top_items_today_sums_quantity(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Bò');
        $coke   = $this->makeMenuItem('Coca');

        $o1 = $this->makeOrder($branch, ['status' => 'completed']);
        $this->addItem($o1, $burger, 2);
        $this->addItem($o1, $coke, 1, 15000);
        $o2 = $this->makeOrder($branch, ['status' => 'completed']);
        $this->addItem($o2, $burger, 3);
        $cancelled = $this->makeOrder($branch, ['status' => 'cancelled']);
        $this->addItem($cancelled, $coke, 10, 15000);

        $top = (new BranchStats())->topItemsToday($branch->id);

        $this->assertSame('Burger Bò', $top[0]->name);
        $this->assertSame(5, (int) $top[0]->qty);
        $this->assertSame(250000, (int) $top[0]->revenue);
        $this->assertSame(1, (int) $top[1]->qty); // đơn huỷ không tính
    }

    public function test_dashboard_page_shows_numbers(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 125000]);
        $this->makeOrder($branch, ['status' => 'pending', 'order_code' => 'ZZPENDING1']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager')
            ->assertOk()
            ->assertSee('125.000đ')
            ->assertSee('ZZPENDING1');
    }
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Manager/DashboardTest.php`
Expected: FAIL với `Class "App\Services\BranchStats" not found`.

- [ ] **Step 3: Viết `BranchStats`**

`app/Services/BranchStats.php`:

```php
<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BranchStats
{
    public function today(int $branchId): array
    {
        $row = Order::ofBranch($branchId)
            ->whereDate('created_at', today())
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->first();

        return [
            'revenue'   => (int) $row->revenue,
            'orders'    => (int) $row->orders,
            'pending'   => (int) $row->pending,
            'cancelled' => (int) $row->cancelled,
        ];
    }

    public function revenueLastDays(int $branchId, int $days = 7): array
    {
        $from = today()->subDays($days - 1);

        $rows = Order::ofBranch($branchId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $result[$day] = (int) ($rows[$day] ?? 0);
        }

        return $result;
    }

    public function topItemsToday(int $branchId, int $limit = 5): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', today())
            ->groupBy('order_items.menu_item_id', 'order_items.name_snapshot')
            ->selectRaw('order_items.name_snapshot as name, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as revenue')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }
}
```

- [ ] **Step 4: Cập nhật `DashboardController`**

```php
<?php

namespace App\Http\Controllers\Manager;

use App\Models\Branch;
use App\Models\Order;
use App\Services\BranchStats;

class DashboardController extends ManagerController
{
    public function index(BranchStats $stats)
    {
        $branchId = $this->branchId();

        return view('manager.dashboard', [
            'branch'        => Branch::findOrFail($branchId),
            'today'         => $stats->today($branchId),
            'revenue7'      => $stats->revenueLastDays($branchId, 7),
            'topItems'      => $stats->topItemsToday($branchId),
            'pendingOrders' => Order::ofBranch($branchId)->pending()->with('user')->oldest()->limit(10)->get(),
        ]);
    }
}
```

- [ ] **Step 5: Tạo partial nhãn trạng thái**

`resources/views/manager/partials/status-badge.blade.php`:

```blade
@php
    $colors = [
        'pending'   => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'cooking'   => 'bg-orange-100 text-orange-700',
        'ready'     => 'bg-purple-100 text-purple-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-slate-200 text-slate-600',
    ];
@endphp
<span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $colors[$status] ?? 'bg-slate-100' }}">
    {{ \App\Services\OrderStatusService::LABELS[$status] ?? $status }}
</span>
```

- [ ] **Step 6: Viết view dashboard hoàn chỉnh**

`resources/views/manager/dashboard.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Tổng quan')

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp

    <h1 class="text-xl font-bold mb-1">Tổng quan</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $branch->name }} · {{ now()->format('d/m/Y') }}</p>

    {{-- 4 ô số liệu hôm nay --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Doanh thu hôm nay', $money($today['revenue']), 'text-green-600'],
            ['Tổng đơn hôm nay', $today['orders'], 'text-slate-800'],
            ['Chờ xác nhận', $today['pending'], 'text-amber-600'],
            ['Đã huỷ', $today['cancelled'], 'text-slate-500'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-2xl p-4 border border-slate-100">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1 {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Doanh thu 7 ngày: thanh div, cao theo % ngày lớn nhất --}}
        <section class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100">
            <h2 class="font-semibold mb-4">Doanh thu 7 ngày gần nhất</h2>
            @php $max = max(1, max($revenue7)); @endphp
            <div class="flex items-end gap-2 h-48">
                @foreach ($revenue7 as $day => $value)
                    <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ $money($value) }}">
                        <div class="w-full rounded-t-md bg-red-400" style="height: {{ round($value / $max * 100) }}%"></div>
                        <span class="text-[10px] text-slate-400 mt-1">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Món bán chạy hôm nay --}}
        <section class="bg-white rounded-2xl p-5 border border-slate-100">
            <h2 class="font-semibold mb-4">Món bán chạy hôm nay</h2>
            @forelse ($topItems as $i => $item)
                <div class="flex items-center justify-between py-2 text-sm {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <span class="truncate">{{ $i + 1 }}. {{ $item->name }}</span>
                    <span class="text-slate-500 whitespace-nowrap">{{ $item->qty }} phần</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Chưa có đơn hoàn thành hôm nay.</p>
            @endforelse
        </section>
    </div>

    {{-- Đơn đang chờ xác nhận --}}
    <section class="bg-white rounded-2xl p-5 border border-slate-100 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Đơn chờ xác nhận</h2>
            @if (Route::has('manager.orders.index'))
                <a href="{{ route('manager.orders.index', ['status' => 'pending']) }}" class="text-sm text-red-500 hover:underline">Xem tất cả →</a>
            @endif
        </div>
        @forelse ($pendingOrders as $order)
            <div class="flex items-center justify-between py-2 text-sm {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                <div class="min-w-0">
                    <p class="font-semibold">{{ $order->order_code }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $order->user?->name }} · {{ $order->created_at->format('H:i') }} · {{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}</p>
                </div>
                <span class="font-semibold whitespace-nowrap">{{ $money($order->total) }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">Không có đơn nào đang chờ. 🎉</p>
        @endforelse
    </section>
@endsection
```

- [ ] **Step 7: Chạy test, xác nhận pass**

Run: `$PHP artisan test tests/Feature/Manager`
Expected: PASS (Task 1 + 2 + 3: 18 tests).

- [ ] **Step 8: Commit**

```bash
git add app/Services/BranchStats.php app/Http/Controllers/Manager/DashboardController.php \
        resources/views/manager tests/Feature/Manager/DashboardTest.php
git commit -m "feat(manager): trang tổng quan với doanh thu, đơn chờ, món bán chạy"
```

---

### Task 4: Danh sách đơn hàng + bộ lọc

**Files:**
- Create: `app/Http/Controllers/Manager/OrderController.php` (hàm `index`)
- Modify: `routes/manager.php`
- Create: `resources/views/manager/orders/index.blade.php`
- Test: `tests/Feature/Manager/OrderListTest.php`

**Interfaces:**
- Consumes: `ManagerController::branchId()`, `OrderStatusService::LABELS`, partial `manager.partials.status-badge`.
- Produces: route `manager.orders.index` (GET `/manager/orders`), nhận query `status`, `date` (Y-m-d), `q` (mã đơn hoặc mã lấy hàng). Phân trang 15 đơn/trang.

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/OrderListTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderListTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_only_own_branch_orders(): void
    {
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');
        $this->makeOrder($mine, ['order_code' => 'ZZMINE0001']);
        $this->makeOrder($other, ['order_code' => 'ZZOTHER001']);

        $this->actingAs($this->makeUser('manager', $mine))
            ->get('/manager/orders')
            ->assertOk()
            ->assertSee('ZZMINE0001')
            ->assertDontSee('ZZOTHER001');
    }

    public function test_filters_by_status(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['order_code' => 'ZZPEND0001', 'status' => 'pending']);
        $this->makeOrder($branch, ['order_code' => 'ZZDONE0001', 'status' => 'completed']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/orders?status=completed')
            ->assertSee('ZZDONE0001')
            ->assertDontSee('ZZPEND0001');
    }

    public function test_filters_by_date(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-10-01 10:00'));
        $this->makeOrder($branch, ['order_code' => 'ZZOLD00001']);
        $this->travelTo(Carbon::parse('2026-10-04 10:00'));
        $this->makeOrder($branch, ['order_code' => 'ZZNEW00001']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/orders?date=2026-10-01')
            ->assertSee('ZZOLD00001')
            ->assertDontSee('ZZNEW00001');
    }

    public function test_searches_by_order_code_or_pickup_code(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['order_code' => 'ZZFIND0001', 'pickup_code' => 'B07']);
        $this->makeOrder($branch, ['order_code' => 'ZZSKIP0001', 'pickup_code' => 'C01']);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->get('/manager/orders?q=FIND')
            ->assertSee('ZZFIND0001')->assertDontSee('ZZSKIP0001');

        $this->actingAs($manager)->get('/manager/orders?q=B07')
            ->assertSee('ZZFIND0001')->assertDontSee('ZZSKIP0001');
    }

    /** Review Focus #5: tham số lọc bậy không gây lỗi 500 */
    public function test_invalid_filters_show_validation_error_not_500(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))
            ->from('/manager/orders')
            ->get('/manager/orders?status=xyz&date=khong-phai-ngay')
            ->assertRedirect('/manager/orders')
            ->assertSessionHasErrors(['status', 'date']);
    }
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Manager/OrderListTest.php`
Expected: FAIL. Response 404 vì chưa có route `/manager/orders`.

- [ ] **Step 3: Thêm route**

Sửa `routes/manager.php`: thêm `use App\Http\Controllers\Manager\OrderController;` ở đầu file, và thêm dòng này vào trong group, sau route dashboard:

```php
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
```

- [ ] **Step 4: Tạo `OrderController@index`**

`app/Http/Controllers/Manager/OrderController.php`:

```php
<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends ManagerController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(OrderStatusService::LABELS))],
            'date'   => ['nullable', 'date_format:Y-m-d'],
            'q'      => ['nullable', 'string', 'max:50'],
        ], [
            'status.in'          => 'Trạng thái lọc không hợp lệ.',
            'date.date_format'   => 'Ngày lọc không hợp lệ.',
        ]);

        $orders = Order::ofBranch($this->branchId())
            ->with('user')
            ->withCount('items')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->ofStatus($status))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->when(trim($filters['q'] ?? ''), function ($q, $search) {
                $q->where(fn ($w) => $w->where('order_code', 'like', "%{$search}%")
                                      ->orWhere('pickup_code', $search));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('manager.orders.index', [
            'orders'  => $orders,
            'filters' => $filters,
            'labels'  => OrderStatusService::LABELS,
        ]);
    }
}
```

- [ ] **Step 5: Tạo view danh sách**

`resources/views/manager/orders/index.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Đơn hàng')

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp

    <h1 class="text-xl font-bold mb-4">Đơn hàng</h1>

    {{-- Bộ lọc --}}
    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <label class="flex flex-col gap-1">
            <span class="text-xs text-slate-400">Trạng thái</span>
            <select name="status" class="px-3 py-2 rounded-lg border border-slate-200">
                <option value="">Tất cả</option>
                @foreach ($labels as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1">
            <span class="text-xs text-slate-400">Ngày</span>
            <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="px-3 py-2 rounded-lg border border-slate-200">
        </label>
        <label class="flex flex-col gap-1 flex-1 min-w-40">
            <span class="text-xs text-slate-400">Mã đơn / mã lấy hàng</span>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="VD: ZZ8F3K hoặc B07" class="px-3 py-2 rounded-lg border border-slate-200">
        </label>
        <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lọc</button>
        <a href="{{ route('manager.orders.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200">Xoá lọc</a>
    </form>

    {{-- Bảng đơn (cuộn ngang trên điện thoại) --}}
    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Mã đơn</th>
                    <th class="px-4 py-3">Khách</th>
                    <th class="px-4 py-3">Loại</th>
                    <th class="px-4 py-3">Số món</th>
                    <th class="px-4 py-3">Tổng</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Thời gian</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b border-slate-50 hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold">
                            @if (Route::has('manager.orders.show'))
                                <a href="{{ route('manager.orders.show', $order) }}" class="text-red-500 hover:underline">{{ $order->order_code }}</a>
                            @else
                                {{ $order->order_code }}
                            @endif
                            <span class="block text-xs text-slate-400 font-normal">Lấy hàng: {{ $order->pickup_code }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $order->user?->name }}</td>
                        <td class="px-4 py-3">{{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}</td>
                        <td class="px-4 py-3">{{ $order->items_count }}</td>
                        <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $money($order->total) }}</td>
                        <td class="px-4 py-3">@include('manager.partials.status-badge', ['status' => $order->status])</td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $order->created_at->format('H:i d/m') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Không có đơn nào phù hợp.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
```

- [ ] **Step 6: Chạy test, xác nhận pass**

Run: `$PHP artisan test tests/Feature/Manager`
Expected: PASS (23 tests).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Manager/OrderController.php routes/manager.php \
        resources/views/manager/orders/index.blade.php tests/Feature/Manager/OrderListTest.php
git commit -m "feat(manager): danh sách đơn hàng có lọc trạng thái, ngày, tìm mã"
```

---

### Task 5: Chi tiết đơn + đổi trạng thái qua giao diện

**Files:**
- Modify: `app/Http/Controllers/Manager/OrderController.php` (thêm `show`, `updateStatus`)
- Modify: `routes/manager.php`
- Create: `resources/views/manager/orders/show.blade.php`
- Test: `tests/Feature/Manager/OrderDetailTest.php`

**Interfaces:**
- Consumes: `ManagerController::ensureSameBranch()`, `OrderStatusService::transition()`, `allowedNext()`, `LABELS`.
- Produces:
  - Route `manager.orders.show` (GET `/manager/orders/{order}`).
  - Route `manager.orders.status` (PATCH `/manager/orders/{order}/status`), body gồm `status` và `note` (`note` bắt buộc khi `status=cancelled`).

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/OrderDetailTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDetailTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_shows_order_items_and_allowed_actions(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['order_code' => 'ZZSHOW0001']);
        $this->addItem($order, $this->makeMenuItem('Burger Gà Giòn'), 2);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get("/manager/orders/{$order->id}")
            ->assertOk()
            ->assertSee('ZZSHOW0001')
            ->assertSee('Burger Gà Giòn')
            ->assertSee('Xác nhận đơn')
            ->assertSee('Huỷ đơn');
    }

    /** Review Focus #1 */
    public function test_other_branch_order_returns_404(): void
    {
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');
        $order = $this->makeOrder($other);
        $manager = $this->makeUser('manager', $mine);

        $this->actingAs($manager)->get("/manager/orders/{$order->id}")->assertNotFound();
        $this->actingAs($manager)->patch("/manager/orders/{$order->id}/status", ['status' => 'confirmed'])->assertNotFound();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_confirm_order(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);

        $this->actingAs($this->makeUser('manager', $branch))
            ->from("/manager/orders/{$order->id}")
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertRedirect("/manager/orders/{$order->id}")
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_cancel_requires_reason(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);

        $this->actingAs($this->makeUser('manager', $branch))
            ->from("/manager/orders/{$order->id}")
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertSessionHasErrors('note');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancel_with_reason_saves_history_note(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);

        $this->actingAs($this->makeUser('manager', $branch))
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'cancelled', 'note' => 'Hết nguyên liệu']);

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertDatabaseHas('order_histories', ['order_id' => $order->id, 'to_status' => 'cancelled', 'note' => 'Hết nguyên liệu']);
    }

    /** Review Focus #3 */
    public function test_invalid_transition_shows_error_and_keeps_status(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)
            ->from("/manager/orders/{$order->id}")
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->actingAs($manager)
            ->from("/manager/orders/{$order->id}")
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'abc'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->fresh()->status);
    }
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Manager/OrderDetailTest.php`
Expected: FAIL, response 404 hoặc 405 vì chưa có route.

- [ ] **Step 3: Thêm route**

Trong group của `routes/manager.php`, ngay dưới route `orders.index`:

```php
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
```

- [ ] **Step 4: Thêm `show` và `updateStatus` vào `OrderController`**

Thêm `use InvalidArgumentException;` ở đầu file, rồi thêm 2 hàm sau vào class:

```php
    public function show(Order $order, OrderStatusService $service)
    {
        $this->ensureSameBranch($order);

        $order->load(['user', 'items', 'histories.changedBy']);

        return view('manager.orders.show', [
            'order'   => $order,
            'next'    => $service->allowedNext($order),
            'labels'  => OrderStatusService::LABELS,
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderStatusService $service)
    {
        $this->ensureSameBranch($order);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(OrderStatusService::LABELS))],
            'note'   => ['nullable', 'string', 'max:255', 'required_if:status,cancelled'],
        ], [
            'status.required'  => 'Thiếu trạng thái mới.',
            'status.in'        => 'Trạng thái không hợp lệ.',
            'note.required_if' => 'Vui lòng nhập lý do huỷ đơn.',
            'note.max'         => 'Ghi chú tối đa 255 ký tự.',
        ]);

        try {
            $service->transition($order, $data['status'], $request->user(), $data['note'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Đơn {$order->order_code}: " . OrderStatusService::LABELS[$data['status']] . '.');
    }
```

- [ ] **Step 5: Tạo view chi tiết đơn**

`resources/views/manager/orders/show.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Đơn ' . $order->order_code)

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        // Chữ trên nút cho từng bước tiếp theo (trừ huỷ, huỷ có form riêng)
        $actionText = [
            'confirmed' => 'Xác nhận đơn',
            'cooking'   => 'Bắt đầu nấu',
            'ready'     => 'Đã nấu xong',
            'completed' => 'Hoàn thành / đã giao',
        ];
    @endphp

    <a href="{{ route('manager.orders.index') }}" class="text-sm text-slate-500 hover:text-red-500">← Danh sách đơn</a>

    <div class="flex flex-wrap items-center gap-3 mt-2 mb-6">
        <h1 class="text-xl font-bold">{{ $order->order_code }}</h1>
        @include('manager.partials.status-badge', ['status' => $order->status])
        <span class="text-sm text-slate-500">Mã lấy hàng: <b>{{ $order->pickup_code }}</b></span>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Món trong đơn --}}
            <section class="bg-white rounded-2xl p-5 border border-slate-100">
                <h2 class="font-semibold mb-3">Món ({{ $order->items->sum('quantity') }})</h2>
                @foreach ($order->items as $item)
                    <div class="flex justify-between py-2 text-sm border-b border-slate-50">
                        <div>
                            <p>{{ $item->quantity }} × {{ $item->name_snapshot }}</p>
                            @if ($item->note) <p class="text-xs text-amber-600">Ghi chú: {{ $item->note }}</p> @endif
                        </div>
                        <span class="whitespace-nowrap">{{ $money($item->subtotal) }}</span>
                    </div>
                @endforeach
                <div class="text-sm mt-3 space-y-1">
                    <div class="flex justify-between"><span class="text-slate-500">Tạm tính</span><span>{{ $money($order->subtotal) }}</span></div>
                    @if ($order->discount > 0)
                        <div class="flex justify-between"><span class="text-slate-500">Giảm giá</span><span>-{{ $money($order->discount) }}</span></div>
                    @endif
                    <div class="flex justify-between font-bold text-base"><span>Tổng</span><span class="text-red-500">{{ $money($order->total) }}</span></div>
                </div>
            </section>

            {{-- Lịch sử trạng thái --}}
            <section class="bg-white rounded-2xl p-5 border border-slate-100">
                <h2 class="font-semibold mb-3">Lịch sử</h2>
                <p class="text-sm text-slate-500">{{ $order->created_at->format('H:i d/m/Y') }} · Khách đặt đơn</p>
                @foreach ($order->histories as $h)
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $h->created_at->format('H:i d/m/Y') }} · {{ $labels[$h->to_status] ?? $h->to_status }}
                        bởi {{ $h->changedBy?->name }}
                        @if ($h->note) — <i>{{ $h->note }}</i> @endif
                    </p>
                @endforeach
            </section>
        </div>

        <div class="space-y-6">
            {{-- Thao tác --}}
            <section class="bg-white rounded-2xl p-5 border border-slate-100">
                <h2 class="font-semibold mb-3">Thao tác</h2>
                @forelse (array_diff($next, ['cancelled']) as $to)
                    <form method="POST" action="{{ route('manager.orders.status', $order) }}" class="mb-2">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $to }}">
                        <button class="w-full px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">
                            {{ $actionText[$to] ?? $labels[$to] }}
                        </button>
                    </form>
                @empty
                    @if (!in_array('cancelled', $next))
                        <p class="text-sm text-slate-400">Đơn đã kết thúc, không còn thao tác.</p>
                    @endif
                @endforelse

                @if (in_array('cancelled', $next))
                    <form method="POST" action="{{ route('manager.orders.status', $order) }}" class="mt-4 pt-4 border-t border-slate-100">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <input type="text" name="note" value="{{ old('note') }}" placeholder="Lý do huỷ (bắt buộc)" maxlength="255"
                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm mb-2">
                        <button class="w-full px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm cursor-pointer">Huỷ đơn</button>
                    </form>
                @endif
            </section>

            {{-- Khách hàng --}}
            <section class="bg-white rounded-2xl p-5 border border-slate-100 text-sm space-y-1">
                <h2 class="font-semibold mb-2">Khách hàng</h2>
                <p>{{ $order->user?->name }}</p>
                <p class="text-slate-500">{{ $order->user?->phone ?? 'Chưa có SĐT' }}</p>
                <p class="text-slate-500">{{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}</p>
                @if ($order->delivery_address) <p class="text-slate-500">📍 {{ $order->delivery_address }}</p> @endif
                <p class="text-slate-500">Thanh toán: {{ strtoupper($order->payment_method) }} · {{ $order->payment_status === 'paid' ? 'Đã trả' : 'Chưa trả' }}</p>
                @if ($order->note) <p class="text-amber-600">Ghi chú: {{ $order->note }}</p> @endif
            </section>
        </div>
    </div>
@endsection
```

- [ ] **Step 6: Chạy toàn bộ test manager, xác nhận pass**

Run: `$PHP artisan test tests/Feature/Manager`
Expected: PASS (29 tests).

- [ ] **Step 7: Kiểm tra bằng tay trên `zomzop.test`**

1. Đăng nhập `manager@zomzop.com`, vào **Đơn hàng**, lọc "Chờ xác nhận".
2. Mở một đơn, bấm lần lượt **Xác nhận đơn → Bắt đầu nấu → Đã nấu xong → Hoàn thành**. Mỗi bước thấy thông báo xanh, mục Lịch sử thêm một dòng.
3. Mở một đơn `pending` khác, bấm **Huỷ đơn** khi để trống lý do: phải báo "Vui lòng nhập lý do huỷ đơn."
4. Quay lại **Tổng quan**: doanh thu hôm nay tăng đúng bằng tổng đơn vừa hoàn thành.
5. Thu nhỏ trình duyệt về khổ điện thoại: sidebar ẩn, menu ngang hiện ra, bảng đơn cuộn ngang được.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Manager/OrderController.php routes/manager.php \
        resources/views/manager/orders/show.blade.php tests/Feature/Manager/OrderDetailTest.php
git commit -m "feat(manager): chi tiết đơn và đổi trạng thái / huỷ đơn có lý do"
```

---

### Task 6: Cập nhật README và mở PR

**Files:**
- Modify: `README.md` (mục "Trạng thái hiện tại")

- [ ] **Step 1: Cập nhật README**

Trong mục **✅ Đã hoàn thành**, thêm:

```markdown
- **Dashboard Manager (giai đoạn 1):** `/manager` — tổng quan doanh thu/đơn hôm nay của chi nhánh, danh sách đơn có lọc, chi tiết đơn, xác nhận / chuyển trạng thái / huỷ đơn (ghi `order_histories`)
```

Trong mục **🚧 Đang phát triển**, sửa dòng Dashboard thành:

```markdown
- Dashboard cho **Admin / Staff / Kitchen**; Manager còn: menu & giá chi nhánh, nhân sự, báo cáo
```

- [ ] **Step 2: Chạy toàn bộ test lần cuối**

Run: `$PHP artisan test`
Expected: 29 test manager PASS. Chỉ `ExampleTest` fail như trước (lỗi có sẵn).

- [ ] **Step 3: Commit và đẩy nhánh**

```bash
git add README.md
git commit -m "docs: cập nhật trạng thái dashboard manager"
git push -u origin manager-dashboard
```

Sau đó tạo PR `manager-dashboard` → `main` trên GitHub.

---

## Ghi chú khi merge với PR #1 của Tính

- Plan này không đụng `routes/web.php` và `layouts/app.blade.php`, nên **không có xung đột** với PR #1.
- `bootstrap/app.php` và `config/app.php` chỉ nhánh này sửa.
- Thứ tự merge nào cũng được.
