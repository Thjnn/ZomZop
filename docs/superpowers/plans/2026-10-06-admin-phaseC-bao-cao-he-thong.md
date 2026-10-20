# Admin — Phase C: Báo cáo toàn chuỗi, xem đơn/lương/chấm công, cài đặt, nhật ký — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
>
> **Cho thành viên nhóm code tay:** làm lần lượt từng Task. Viết test trước → FAIL → code → PASS → commit. Không push khi chưa hỏi nhóm trưởng.

**Goal:** Admin xem dashboard và báo cáo doanh thu toàn chuỗi (so sánh chi nhánh, xuất Excel), xem mọi đơn, lương, chấm công (chỉ xem), chỉnh cài đặt chung (áp vào footer, trang hỗ trợ, phương thức thanh toán) và xem nhật ký thao tác.

**Architecture:** `BranchReport`/`BranchStats` nhận `?int $branchId` (null = toàn chuỗi) nên admin dùng lại logic của manager. Hàm đọc khoảng ngày tách ra trait `ParsesDateRange` dùng chung cho cả hai. Các trang "chỉ xem" chỉ có route GET. Cài đặt lưu bằng `Setting::set()` có sẵn.

**Tech Stack:** Laravel 13, Blade + Tailwind, OpenSpout (`App\Support\XlsxExport` có sẵn), PHPUnit.

**Spec:** `docs/superpowers/specs/2026-10-06-admin-design.md`

**Phụ thuộc:** Phase A Task 1 trên `main`. Task 5 sửa `checkout/index.blade.php` — nếu Phase B Task 3 đã merge thì sửa trên bản mới (hai task sửa hai khối khác nhau của file).

## Global Constraints

- Route admin thêm vào group trong `routes/admin.php` (đã có `auth`, `role:admin`, prefix `admin`, tên `admin.`).
- Trang "chỉ xem" (đơn, lương, chấm công): **chỉ route GET**, view không có nút sửa/chuyển trạng thái.
- Mọi action ghi của admin gọi `$this->log(...)`.
- Không đổi chữ ký hàm manager đang gọi ngoài việc `int` → `?int`; test manager cũ phải PASS.
- Lọc sai (ngày, chi nhánh) **không redirect**: báo lỗi và dùng giá trị mặc định (giống `Manager\OrderController::filtered`).
- Test ở `tests/Feature/Admin/`, `RefreshDatabase` + `Tests\Feature\Manager\CreatesBranchData`, `withoutVite()`.

## Review Focus

1. Đơn bị huỷ không được cộng vào doanh thu ở cả dashboard, báo cáo, bảng so sánh chi nhánh. (Task 1)
2. Admin tắt hết phương thức thanh toán → bị chặn, phải còn ít nhất một. (Task 5)
3. Admin tắt MoMo nhưng khách vẫn gửi `payment_method=momo` bằng form cũ → không tạo đơn. (Task 5)
4. Chọn khoảng ngày ngược (đến < từ) ở báo cáo → báo lỗi, hiện khoảng mặc định, không lỗi 500. (Task 2)
5. Admin gửi `PATCH /manager/orders/{id}/status` → 403 (admin không chuyển trạng thái đơn). (Task 3)

---

## File Structure

| File | Trách nhiệm |
|------|-------------|
| `app/Services/BranchReport.php`, `app/Services/BranchStats.php` | `?int $branchId`; thêm `BranchReport::byBranch()` |
| `app/Http/Controllers/Concerns/ParsesDateRange.php` | Đọc + kiểm tra `from`/`to` (tách từ `Manager\ReportController::range`) |
| `app/Http/Controllers/Manager/ReportController.php` | Dùng trait |
| `app/Http/Controllers/Admin/DashboardController.php`, `resources/views/admin/dashboard.blade.php` | Dashboard đầy đủ (thay bản tạm ở Phase A) |
| `app/Http/Controllers/Admin/ReportController.php`, `resources/views/admin/reports/index.blade.php` | Báo cáo + xuất Excel |
| `app/Http/Controllers/Admin/OrderController.php`, `resources/views/admin/orders/{index,show}.blade.php` | Đơn (chỉ xem) |
| `app/Http/Controllers/Admin/PayrollController.php`, `app/Http/Controllers/Admin/AttendanceController.php`, view tương ứng | Lương, chấm công (chỉ xem) |
| `app/Support/PaymentMethods.php` | Danh sách phương thức thanh toán đang bật |
| `app/Http/Controllers/Admin/SettingController.php`, `resources/views/admin/settings/edit.blade.php` | Cài đặt chung |
| `resources/views/layouts/app.blade.php`, `resources/views/pages/support.blade.php`, `app/Http/Controllers/CheckoutController.php`, `resources/views/checkout/index.blade.php` | Đọc cài đặt |
| `app/Http/Controllers/Admin/LogController.php`, `resources/views/admin/logs/index.blade.php` | Nhật ký thao tác |

---

### Task 1: Báo cáo toàn chuỗi trong service + dashboard admin

**Files:**
- Modify: `app/Services/BranchReport.php`, `app/Services/BranchStats.php`
- Modify: `app/Http/Controllers/Admin/DashboardController.php`, `resources/views/admin/dashboard.blade.php`
- Test: `tests/Feature/Admin/ChainReportTest.php`

**Interfaces:**
- Produces:
  - Mọi hàm public của `BranchReport` (`summary`, `daily`, `byType`, `byPayment`, `topItems`) và `BranchStats` (`statusCounts`, `series`) nhận `?int $branchId`; `null` = toàn chuỗi.
  - `BranchReport::byBranch(Carbon $from, Carbon $to): Collection` — mỗi phần tử `['branch' => Branch, 'orders' => int, 'revenue' => int, 'cancelled' => int]`, sắp theo doanh thu giảm dần, có cả chi nhánh 0 đơn.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/ChainReportTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Services\BranchReport;
use App\Services\BranchStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ChainReportTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function seedOrders(): array
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $this->makeBranch('Chi nhánh Rỗng');
        $this->makeOrder($a, ['status' => 'completed', 'total' => 100000]);
        $this->makeOrder($a, ['status' => 'cancelled', 'total' => 500000]);
        $this->makeOrder($b, ['status' => 'completed', 'total' => 300000]);

        return [$a, $b];
    }

    public function test_summary_null_branch_sums_whole_chain_excluding_cancelled(): void
    {
        [$a] = $this->seedOrders();
        $report = app(BranchReport::class);

        $chain = $report->summary(null, today(), today());
        $this->assertSame(400000, $chain['revenue']);
        $this->assertSame(3, $chain['orders']);
        $this->assertSame(1, $chain['cancelled']);

        $this->assertSame(100000, $report->summary($a->id, today(), today())['revenue']); // manager vẫn như cũ
    }

    public function test_by_branch_sorted_by_revenue_and_includes_empty_branch(): void
    {
        $this->seedOrders();

        $rows = app(BranchReport::class)->byBranch(today(), today());

        $this->assertSame(['Chi nhánh Beta', 'Chi nhánh Alpha', 'Chi nhánh Rỗng'], $rows->pluck('branch.name')->all());
        $this->assertSame([300000, 100000, 0], $rows->pluck('revenue')->all());
        $this->assertSame(1, $rows[1]['cancelled']);
    }

    public function test_stats_null_branch(): void
    {
        $this->seedOrders();

        $counts = app(BranchStats::class)->statusCounts(null, 'today');
        $this->assertSame(3, $counts['total']);
        $this->assertSame(400000, $counts['revenue']);
    }

    public function test_dashboard_shows_chain_numbers_and_branch_ranking(): void
    {
        $this->seedOrders();

        $this->actingAs($this->makeUser('admin'))->get('/admin')
            ->assertOk()
            ->assertSee('400.000')
            ->assertSeeInOrder(['Chi nhánh Beta', 'Chi nhánh Alpha']);
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=ChainReportTest`
Expected: FAIL (`TypeError`: không truyền được `null` vào `int $branchId`; `byBranch` chưa có).

- [ ] **Step 3: Sửa service**

`app/Services/BranchReport.php`:

1. Đổi **mọi** `int $branchId` thành `?int $branchId` (hàm `orders`, `summary`, `daily`, `completedGroupedBy`, `byType`, `byPayment`, `topItems`).
2. Hàm `orders()` thay `Order::ofBranch($branchId)` bằng:

```php
        return Order::query()
            ->when($branchId, fn ($q, $id) => $q->ofBranch($id))
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString());
```

3. Hàm `topItems()` thay dòng `->where('orders.branch_id', $branchId)` bằng `->when($branchId, fn ($q, $id) => $q->where('orders.branch_id', $id))`.
4. Thêm `use App\Models\Branch;` và hàm mới:

```php
    /** So sánh chi nhánh trong khoảng ngày: doanh thu (đơn hoàn thành), số đơn, số đơn huỷ */
    public function byBranch(Carbon $from, Carbon $to): Collection
    {
        $rows = $this->orders(null, $from, $to)
            ->selectRaw('branch_id, COUNT(*) as orders')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->groupBy('branch_id')
            ->get()->keyBy('branch_id');

        return Branch::orderBy('name')->get()
            ->map(fn (Branch $b) => [
                'branch'    => $b,
                'orders'    => (int) ($rows[$b->id]->orders ?? 0),
                'revenue'   => (int) ($rows[$b->id]->revenue ?? 0),
                'cancelled' => (int) ($rows[$b->id]->cancelled ?? 0),
            ])
            ->sortByDesc('revenue')
            ->values();
    }
```

`app/Services/BranchStats.php`:

1. `statusCounts(int $branchId, ...)` → `statusCounts(?int $branchId, ...)`; thay `$query = Order::ofBranch($branchId);` bằng `$query = Order::query()->when($branchId, fn ($q, $id) => $q->ofBranch($id));`.
2. `series(int $branchId, ...)` → `series(?int $branchId, ...)`; thay `$days = Order::ofBranch($branchId)` bằng `$days = Order::query()->when($branchId, fn ($q, $id) => $q->ofBranch($id))`.

- [ ] **Step 4: Dashboard admin**

`app/Http/Controllers/Admin/DashboardController.php` (thay toàn bộ bản tạm):

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\User;
use App\Services\BranchReport;
use App\Services\BranchStats;

class DashboardController extends AdminController
{
    public function index(BranchStats $stats, BranchReport $report)
    {
        return view('admin.dashboard', [
            'today'        => $stats->statusCounts(null, 'today'),
            'month'        => $stats->statusCounts(null, 'month'),
            'ranking'      => $report->byBranch(today()->startOfMonth(), today()),
            'branches'     => Branch::where('is_active', true)->count(),
            'newCustomers' => User::where('role', 'customer')->where('created_at', '>=', today()->startOfMonth())->count(),
            'series'       => $stats->series(null, 'week'),
        ]);
    }
}
```

`resources/views/admin/dashboard.blade.php` (thay toàn bộ):

```blade
@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp
    <h1 class="text-xl font-bold mb-4">Tổng quan toàn chuỗi</h1>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Doanh thu hôm nay', $money($today['revenue'])],
            ['Đơn hôm nay', $today['total']],
            ['Doanh thu tháng này', $money($month['revenue'])],
            ['Đơn tháng này', $month['total']],
            ['Chi nhánh đang mở', $branches],
            ['Khách mới tháng này', $newCustomers],
            ['Đơn chờ xác nhận (hôm nay)', $today['pending']],
            ['Đơn huỷ tháng này', $month['cancelled']],
        ] as [$label, $value])
            <div class="bg-white rounded-2xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-xl font-bold mt-1">{{ is_int($value) ? number_format($value, 0, ',', '.') : $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h2 class="font-semibold mb-3">Doanh thu theo chi nhánh — tháng này</h2>
            @php $max = max(1, $ranking->max('revenue')); @endphp
            <div class="space-y-3 text-sm">
                @foreach ($ranking as $row)
                    <div>
                        <div class="flex justify-between gap-2">
                            <span class="{{ $row['branch']->is_active ? '' : 'text-slate-400' }}">{{ $row['branch']->name }}</span>
                            <span class="font-semibold">{{ $money($row['revenue']) }} <span class="text-xs text-slate-400 font-normal">· {{ $row['orders'] }} đơn</span></span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 mt-1"><div class="h-2 rounded-full bg-red-400" style="width: {{ round($row['revenue'] / $max * 100) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h2 class="font-semibold mb-3">7 ngày gần nhất</h2>
            @php $maxDay = max(1, max($series['revenue'])); @endphp
            <div class="flex items-end gap-2 h-40">
                @foreach ($series['labels'] as $i => $label)
                    <div class="flex-1 flex flex-col items-center gap-1" title="{{ $label }}: {{ $money($series['revenue'][$i]) }} · {{ $series['orders'][$i] }} đơn">
                        <div class="w-full rounded-t bg-red-400" style="height: {{ max(2, round($series['revenue'][$i] / $maxDay * 130)) }}px"></div>
                        <span class="text-[10px] text-slate-400">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
```

> Test `AccessTest::test_admin_sees_dashboard` (Phase A) tìm chữ "Quản trị toàn chuỗi" — chữ này nằm ở header layout nên vẫn PASS.

- [ ] **Step 5: Chạy test, thấy PASS**

Run: `php artisan test --filter="ChainReportTest|BranchReportTest|DashboardTest|ReportPageTest|Tests\\Feature\\Admin\\AccessTest"`
Expected: PASS hết (test manager cũ không đổi).

- [ ] **Step 6: Commit**

```bash
git add app/Services/BranchReport.php app/Services/BranchStats.php app/Http/Controllers/Admin/DashboardController.php resources/views/admin/dashboard.blade.php tests/Feature/Admin/ChainReportTest.php
git commit -m "feat(admin): báo cáo toàn chuỗi trong service, dashboard admin có xếp hạng chi nhánh"
```

---

### Task 2: Trang báo cáo admin + xuất Excel

**Files:**
- Create: `app/Http/Controllers/Concerns/ParsesDateRange.php`
- Modify: `app/Http/Controllers/Manager/ReportController.php`
- Create: `app/Http/Controllers/Admin/ReportController.php`, `resources/views/admin/reports/index.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/ReportPageTest.php`

**Interfaces:**
- Consumes: `BranchReport` với `?int` + `byBranch()` (Task 1); `Manager\ReportController::TYPE_LABELS`, `PAY_LABELS` (có sẵn).
- Produces: trait `ParsesDateRange::dateRange(Request $request): array{0: Carbon, 1: Carbon, 2: Validator}`; route `admin.reports.index`, `admin.reports.export`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/ReportPageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_chain_report_with_branch_comparison(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $this->makeOrder($a, ['status' => 'completed', 'total' => 100000]);
        $this->makeOrder($b, ['status' => 'completed', 'total' => 250000]);

        $this->actingAs($this->makeUser('admin'))->get('/admin/reports')
            ->assertOk()
            ->assertSee('350.000')
            ->assertSeeInOrder(['So sánh chi nhánh', 'Chi nhánh Beta', 'Chi nhánh Alpha']);
    }

    public function test_filter_one_branch(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $this->makeOrder($a, ['status' => 'completed', 'total' => 100000]);
        $this->makeOrder($b, ['status' => 'completed', 'total' => 250000]);

        $this->actingAs($this->makeUser('admin'))->get("/admin/reports?branch_id={$a->id}")
            ->assertOk()->assertSee('100.000')->assertDontSee('350.000')->assertDontSee('So sánh chi nhánh');
    }

    public function test_reversed_range_shows_error_and_default_range(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/admin/reports?from=2026-10-10&to=2026-10-01')
            ->assertOk()
            ->assertSee('Ngày kết thúc phải từ ngày bắt đầu trở đi.');
    }

    public function test_export_downloads_xlsx(): void
    {
        $this->makeOrder($this->makeBranch(), ['status' => 'completed']);

        $this->actingAs($this->makeUser('admin'))->get('/admin/reports/export')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_manager_report_still_works(): void
    {
        $branch = $this->makeBranch();
        $this->actingAs($this->makeUser('manager', $branch))->get('/manager/reports?from=2026-10-10&to=2026-10-01')
            ->assertOk()->assertSee('Ngày kết thúc phải từ ngày bắt đầu trở đi.');
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter="Tests\\Feature\\Admin\\ReportPageTest"`
Expected: FAIL (404).

- [ ] **Step 3: Tách trait khoảng ngày**

`app/Http/Controllers/Concerns/ParsesDateRange.php` — **cắt** nguyên hàm `range()` trong `app/Http/Controllers/Manager/ReportController.php` dán vào đây, đổi tên thành `dateRange` và `private` → `protected`:

```php
<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

trait ParsesDateRange
{
    /** @return array{0: Carbon, 1: Carbon, 2: \Illuminate\Validation\Validator} khoảng sai → khoảng mặc định (7 ngày gần nhất) */
    protected function dateRange(Request $request): array
    {
        $defaultFrom = today()->subDays(6);
        $defaultTo   = today();

        $validator = Validator::make($request->only(['from', 'to']), [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to'   => ['nullable', 'date_format:Y-m-d'],
        ], [
            'from.date_format' => 'Ngày bắt đầu không hợp lệ.',
            'to.date_format'   => 'Ngày kết thúc không hợp lệ.',
        ]);

        $valid = $validator->valid();
        $from  = isset($valid['from']) ? Carbon::parse($valid['from']) : $defaultFrom;
        $to    = isset($valid['to']) ? Carbon::parse($valid['to']) : $defaultTo;

        $validator->after(function ($v) use ($from, $to) {
            if ($to->lt($from)) {
                $v->errors()->add('to', 'Ngày kết thúc phải từ ngày bắt đầu trở đi.');
            } elseif ($from->diffInDays($to) > 366) {
                $v->errors()->add('to', 'Khoảng ngày tối đa 366 ngày.');
            }
        });

        if ($validator->fails()) {
            if ($to->lt($from) || $from->diffInDays($to) > 366) {
                [$from, $to] = [$defaultFrom, $defaultTo];
            }
        }

        return [$from, $to, $validator];
    }
}
```

`app/Http/Controllers/Manager/ReportController.php`:
- Xoá hàm `range()`; thêm `use App\Http\Controllers\Concerns\ParsesDateRange;` và trong class `use ParsesDateRange;`.
- Đổi 2 chỗ `$this->range($request)` thành `$this->dateRange($request)`.
- Bỏ `use Illuminate\Support\Facades\Validator;` nếu không còn dùng.

- [ ] **Step 4: Controller + route admin**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\ReportController;`, trong group:

```php
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
```

`app/Http/Controllers/Admin/ReportController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ParsesDateRange;
use App\Http\Controllers\Manager\ReportController as ManagerReport;
use App\Models\Branch;
use App\Services\BranchReport;
use App\Support\XlsxExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends AdminController
{
    use ParsesDateRange;

    /** Chi nhánh đang lọc; id không tồn tại → coi như toàn chuỗi */
    private function branch(Request $request): ?Branch
    {
        return ctype_digit((string) $request->query('branch_id')) ? Branch::find($request->query('branch_id')) : null;
    }

    public function index(Request $request, BranchReport $report)
    {
        [$from, $to, $validator] = $this->dateRange($request);
        $branch = $this->branch($request);
        $id     = $branch?->id;

        return view('admin.reports.index', [
            'from'       => $from,
            'to'         => $to,
            'branch'     => $branch,
            'branches'   => Branch::orderBy('name')->get(),
            'summary'    => $report->summary($id, $from, $to),
            'daily'      => $report->daily($id, $from, $to),
            'byType'     => $report->byType($id, $from, $to),
            'byPay'      => $report->byPayment($id, $from, $to),
            'topItems'   => $report->topItems($id, $from, $to),
            'byBranch'   => $branch ? null : $report->byBranch($from, $to),
            'typeLabels' => ManagerReport::TYPE_LABELS,
            'payLabels'  => ManagerReport::PAY_LABELS,
        ])->withErrors($validator);
    }

    public function export(Request $request, BranchReport $report)
    {
        [$from, $to] = $this->dateRange($request);
        $branch = $this->branch($request);
        $id     = $branch?->id;
        $s      = $report->summary($id, $from, $to);

        $rows = [
            ['Phạm vi', $branch?->name ?? 'Toàn chuỗi'],
            ['Doanh thu', $s['revenue']], ['Số đơn', $s['orders']], ['Hoàn thành', $s['completed']],
            ['Đã huỷ', $s['cancelled']], ['Giá trị TB/đơn', $s['avg_order']], ['Tỉ lệ huỷ (%)', $s['cancel_rate']],
        ];
        if (!$branch) {
            $rows[] = [];
            $rows[] = XlsxExport::bold(['Chi nhánh', 'Số đơn', 'Doanh thu', 'Đơn huỷ']);
            foreach ($report->byBranch($from, $to) as $r) {
                $rows[] = [$r['branch']->name, $r['orders'], $r['revenue'], $r['cancelled']];
            }
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Ngày', 'Số đơn', 'Doanh thu']);
        foreach ($report->daily($id, $from, $to) as $day => $d) {
            $rows[] = [Carbon::parse($day)->format('d/m/Y'), $d['orders'], $d['revenue']];
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Món bán chạy', 'Số lượng', 'Doanh thu']);
        foreach ($report->topItems($id, $from, $to) as $item) {
            $rows[] = [$item->name, (int) $item->qty, (int) $item->revenue];
        }

        return XlsxExport::download("bao-cao-chuoi-{$from->toDateString()}-{$to->toDateString()}.xlsx",
            ['Báo cáo doanh thu', $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y')], $rows);
    }
}
```

- [ ] **Step 5: View**

`resources/views/admin/reports/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Báo cáo')

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $query = ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'branch_id' => $branch?->id];
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl font-bold">Báo cáo doanh thu — {{ $branch?->name ?? 'Toàn chuỗi' }}</h1>
        <a href="{{ route('admin.reports.export', $query) }}" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">Xuất Excel</a>
    </div>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <label class="grid gap-1">Từ ngày <input type="date" name="from" value="{{ $from->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200"></label>
        <label class="grid gap-1">Đến ngày <input type="date" name="to" value="{{ $to->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200"></label>
        <label class="grid gap-1">Chi nhánh
            <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
                <option value="">Toàn chuỗi</option>
                @foreach ($branches as $b) <option value="{{ $b->id }}" @selected($branch?->id === $b->id)>{{ $b->name }}</option> @endforeach
            </select>
        </label>
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        @foreach ([['Doanh thu', $money($summary['revenue'])], ['Số đơn', $summary['orders']], ['Hoàn thành', $summary['completed']], ['Đã huỷ', $summary['cancelled']], ['TB/đơn', $money($summary['avg_order'])], ['Tỉ lệ huỷ', $summary['cancel_rate'] . '%']] as [$label, $value])
            <div class="bg-white rounded-2xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-lg font-bold mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    @if ($byBranch)
        <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto mb-6">
            <h2 class="font-semibold px-4 pt-4">So sánh chi nhánh</h2>
            <table class="w-full text-sm mt-2">
                <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                    <tr><th class="px-4 py-2">Chi nhánh</th><th class="px-4 py-2">Số đơn</th><th class="px-4 py-2">Doanh thu</th><th class="px-4 py-2">Đơn huỷ</th><th class="px-4 py-2">% doanh thu chuỗi</th></tr>
                </thead>
                <tbody>
                    @foreach ($byBranch as $r)
                        <tr class="border-b border-slate-50">
                            <td class="px-4 py-2"><a href="{{ route('admin.reports.index', ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'branch_id' => $r['branch']->id]) }}" class="hover:text-red-500">{{ $r['branch']->name }}</a></td>
                            <td class="px-4 py-2">{{ $r['orders'] }}</td>
                            <td class="px-4 py-2 font-semibold">{{ $money($r['revenue']) }}</td>
                            <td class="px-4 py-2">{{ $r['cancelled'] }}</td>
                            <td class="px-4 py-2">{{ $summary['revenue'] ? round($r['revenue'] / $summary['revenue'] * 100, 1) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-4 text-sm">
            <h2 class="font-semibold mb-2">Theo ngày</h2>
            <table class="w-full">
                @foreach ($daily as $day => $d)
                    <tr class="border-b border-slate-50"><td class="py-1.5">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</td><td>{{ $d['orders'] }} đơn</td><td class="text-right font-semibold">{{ $money($d['revenue']) }}</td></tr>
                @endforeach
            </table>
        </div>
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 text-sm">
                <h2 class="font-semibold mb-2">Hình thức & thanh toán (đơn hoàn thành)</h2>
                @foreach ($byType as $k => $d) <p class="flex justify-between py-1"><span>{{ $typeLabels[$k] }}</span><span>{{ $d['orders'] }} đơn · {{ $money($d['revenue']) }}</span></p> @endforeach
                <hr class="my-2 border-slate-100">
                @foreach ($byPay as $k => $d) <p class="flex justify-between py-1"><span>{{ $payLabels[$k] }}</span><span>{{ $d['orders'] }} đơn · {{ $money($d['revenue']) }}</span></p> @endforeach
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 p-4 text-sm">
                <h2 class="font-semibold mb-2">Món bán chạy</h2>
                @forelse ($topItems as $item)
                    <p class="flex justify-between py-1"><span>{{ $item->name }}</span><span>{{ (int) $item->qty }} phần · {{ $money($item->revenue) }}</span></p>
                @empty
                    <p class="text-slate-400">Chưa có đơn hoàn thành.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
```

- [ ] **Step 6: Chạy test, thấy PASS**

Run: `php artisan test --filter="ReportPageTest|BranchReportTest|ExportTest"`
Expected: PASS (cả `Tests\Feature\Manager\ReportPageTest` lẫn `Tests\Feature\Admin\ReportPageTest`).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Concerns/ParsesDateRange.php app/Http/Controllers/Manager/ReportController.php app/Http/Controllers/Admin/ReportController.php resources/views/admin/reports routes/admin.php tests/Feature/Admin/ReportPageTest.php
git commit -m "feat(admin): báo cáo doanh thu toàn chuỗi, so sánh chi nhánh, xuất Excel"
```

---

### Task 3: Đơn hàng toàn chuỗi (chỉ xem)

**Files:**
- Create: `app/Http/Controllers/Admin/OrderController.php`
- Create: `resources/views/admin/orders/index.blade.php`, `resources/views/admin/orders/show.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/OrderViewTest.php`

**Interfaces:**
- Consumes: `OrderStatusService::LABELS`, partial `manager.partials.status-badge`; quan hệ `Order::items|histories|user|branch|coupon`, `OrderHistory::changedBy`.
- Produces: route `admin.orders.index`, `admin.orders.show` (Phase A `customers/show` tự gắn link khi route này có).

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/OrderViewTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class OrderViewTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_orders_of_all_branches_and_filters(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $oa = $this->makeOrder($a, ['status' => 'pending']);
        $ob = $this->makeOrder($b, ['status' => 'completed']);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee($oa->order_code)->assertSee($ob->order_code);
        $this->actingAs($admin)->get("/admin/orders?branch_id={$a->id}")->assertSee($oa->order_code)->assertDontSee($ob->order_code);
        $this->actingAs($admin)->get('/admin/orders?status=completed')->assertDontSee($oa->order_code)->assertSee($ob->order_code);
        $this->actingAs($admin)->get("/admin/orders?q={$ob->order_code}")->assertDontSee($oa->order_code)->assertSee($ob->order_code);
    }

    public function test_invalid_filter_shows_error_not_redirect(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/admin/orders?status=abc')
            ->assertOk()->assertSee('Trạng thái lọc không hợp lệ.');
    }

    public function test_show_has_items_and_history_but_no_actions(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);
        $this->addItem($order, $this->makeMenuItem('Burger Bò Phô Mai'), 2);
        app(OrderStatusService::class)->transition($order, 'confirmed', $this->makeUser('manager', $branch));

        $this->actingAs($this->makeUser('admin'))->get("/admin/orders/{$order->id}")
            ->assertOk()
            ->assertSee('Burger Bò Phô Mai')
            ->assertSee('Đã xác nhận')
            ->assertDontSee('name="status"', false);
    }

    public function test_admin_cannot_change_order_status(): void
    {
        $order = $this->makeOrder($this->makeBranch());

        $this->actingAs($this->makeUser('admin'))
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=OrderViewTest`
Expected: FAIL (404).

- [ ] **Step 3: Route + controller**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\OrderController;`, trong group:

```php
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
```

`app/Http/Controllers/Admin/OrderController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Chỉ xem — chuyển trạng thái đơn là việc của manager chi nhánh */
class OrderController extends AdminController
{
    public function index(Request $request)
    {
        // Lọc sai thì ở lại trang, báo lỗi, chỉ áp các bộ lọc hợp lệ
        $validator = Validator::make($request->only(['status', 'date', 'q', 'branch_id']), [
            'status'    => ['nullable', Rule::in(array_keys(OrderStatusService::LABELS))],
            'date'      => ['nullable', 'date_format:Y-m-d'],
            'q'         => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'integer'],
        ], [
            'status.in'        => 'Trạng thái lọc không hợp lệ.',
            'date.date_format' => 'Ngày lọc không hợp lệ.',
            'q.max'            => 'Từ khoá tìm kiếm tối đa 50 ký tự.',
        ]);
        $filters = $validator->valid();

        $orders = Order::query()
            ->with(['user', 'branch'])
            ->when($filters['branch_id'] ?? null, fn ($q, $id) => $q->ofBranch((int) $id))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->ofStatus($s))
            ->when($filters['date'] ?? null, fn ($q, $d) => $q->whereDate('created_at', $d))
            ->when(trim($filters['q'] ?? ''), fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('order_code', 'like', "%{$s}%")->orWhere('pickup_code', $s)))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('admin.orders.index', [
            'orders'   => $orders,
            'filters'  => $filters,
            'labels'   => OrderStatusService::LABELS,
            'branches' => Branch::orderBy('name')->get(),
        ])->withErrors($validator);
    }

    public function show(Order $order)
    {
        $order->load(['items', 'user', 'branch', 'coupon', 'histories' => fn ($q) => $q->with('changedBy')->oldest()]);

        return view('admin.orders.show', ['order' => $order, 'labels' => OrderStatusService::LABELS]);
    }
}
```

- [ ] **Step 4: View**

`resources/views/admin/orders/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Đơn hàng')

@section('content')
    <h1 class="text-xl font-bold mb-1">Đơn hàng toàn chuỗi</h1>
    <p class="text-sm text-slate-500 mb-4">Chỉ xem. Xử lý đơn do quản lý chi nhánh thực hiện.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Tất cả chi nhánh</option>
            @foreach ($branches as $b) <option value="{{ $b->id }}" @selected(($filters['branch_id'] ?? null) == $b->id)>{{ $b->name }}</option> @endforeach
        </select>
        <select name="status" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Mọi trạng thái</option>
            @foreach ($labels as $k => $label) <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $label }}</option> @endforeach
        </select>
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="px-3 py-2 rounded-lg border border-slate-200">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Mã đơn / mã nhận" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Mã đơn</th><th class="px-4 py-3">Chi nhánh</th><th class="px-4 py-3">Khách</th><th class="px-4 py-3">Thời gian</th><th class="px-4 py-3">Tổng</th><th class="px-4 py-3">Trạng thái</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold"><a href="{{ route('admin.orders.show', $o) }}" class="hover:text-red-500">{{ $o->order_code }}</a></td>
                        <td class="px-4 py-3">{{ $o->branch?->name }}</td>
                        <td class="px-4 py-3">{{ $o->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ number_format($o->total, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3">@include('manager.partials.status-badge', ['status' => $o->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Không có đơn nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
```

`resources/views/admin/orders/show.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Đơn ' . $order->order_code)

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-slate-500 hover:underline">← Đơn hàng</a>
    <div class="flex items-center gap-3 my-3">
        <h1 class="text-xl font-bold">Đơn {{ $order->order_code }}</h1>
        @include('manager.partials.status-badge', ['status' => $order->status])
    </div>

    <div class="grid lg:grid-cols-3 gap-6 text-sm">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-5">
            <table class="w-full">
                <thead class="text-left text-xs text-slate-400 border-b border-slate-100"><tr><th class="py-2">Món</th><th>SL</th><th class="text-right">Thành tiền</th></tr></thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-slate-50"><td class="py-2">{{ $item->name_snapshot }} @if ($item->note)<span class="text-xs text-slate-400">({{ $item->note }})</span>@endif</td><td>{{ $item->quantity }}</td><td class="text-right">{{ $money($item->subtotal) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3 space-y-1 text-right">
                <p>Tạm tính: {{ $money($order->subtotal) }}</p>
                <p>Giảm giá: -{{ $money($order->discount) }} @if ($order->coupon) <span class="font-mono text-xs">({{ $order->coupon->code }})</span> @endif</p>
                <p class="font-bold text-base">Tổng: {{ $money($order->total) }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-100 p-5 space-y-1">
                <p><span class="text-slate-400">Chi nhánh:</span> {{ $order->branch?->name }}</p>
                <p><span class="text-slate-400">Khách:</span> {{ $order->user?->name }} · {{ $order->user?->phone ?? $order->user?->email }}</p>
                <p><span class="text-slate-400">Hình thức:</span> {{ $order->type === 'delivery' ? 'Giao hàng' : 'Mang đi' }}</p>
                @if ($order->delivery_address) <p><span class="text-slate-400">Địa chỉ:</span> {{ $order->delivery_address }}</p> @endif
                <p><span class="text-slate-400">Thanh toán:</span> {{ strtoupper($order->payment_method) }} · {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}</p>
                @if ($order->note) <p><span class="text-slate-400">Ghi chú:</span> {{ $order->note }}</p> @endif
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 p-5">
                <h2 class="font-semibold mb-2">Lịch sử trạng thái</h2>
                <p class="text-xs text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }} — Khách đặt đơn</p>
                @foreach ($order->histories as $h)
                    <p class="text-xs mt-1"><span class="text-slate-400">{{ $h->created_at->format('d/m/Y H:i') }}</span> — {{ $labels[$h->to_status] ?? $h->to_status }} <span class="text-slate-400">bởi {{ $h->changedBy?->name ?? '—' }}</span>@if ($h->note): {{ $h->note }}@endif</p>
                @endforeach
            </div>
        </div>
    </div>
@endsection
```

- [ ] **Step 5: Chạy test, thấy PASS**

Run: `php artisan test --filter=OrderViewTest`
Expected: PASS 4 test.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/OrderController.php resources/views/admin/orders routes/admin.php tests/Feature/Admin/OrderViewTest.php
git commit -m "feat(admin): xem đơn hàng toàn chuỗi (lọc theo chi nhánh, trạng thái, ngày)"
```

---

### Task 4: Bảng lương và chấm công (chỉ xem)

**Files:**
- Create: `app/Http/Controllers/Admin/PayrollController.php`, `resources/views/admin/payrolls/index.blade.php`
- Create: `app/Http/Controllers/Admin/AttendanceController.php`, `resources/views/admin/attendances/index.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/HrViewTest.php`

**Interfaces:**
- Consumes: `Payroll::ofBranch()->ofMonth()`, `Attendance::ofBranch()`, `Attendance::lateMinutes()|earlyMinutes()`, cột `late_reason`, `early_reason`.
- Produces: route `admin.payrolls.index`, `admin.attendances.index`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/HrViewTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Payroll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class HrViewTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_payrolls_of_selected_branch_and_month(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $sa = $this->makeUser('staff', $a);
        $sb = $this->makeUser('staff', $b);
        Payroll::create(['user_id' => $sa->id, 'branch_id' => $a->id, 'month' => 10, 'year' => 2026, 'total' => 4321000, 'status' => 'confirmed']);
        Payroll::create(['user_id' => $sb->id, 'branch_id' => $b->id, 'month' => 10, 'year' => 2026, 'total' => 9876000]);

        $this->actingAs($this->makeUser('admin'))->get("/admin/payrolls?branch_id={$a->id}&month=2026-10")
            ->assertOk()->assertSee('4.321.000')->assertDontSee('9.876.000')
            ->assertDontSee('Tính lại')->assertDontSee('Xác nhận');
    }

    public function test_attendances_of_selected_branch_and_day(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $shift  = $this->makeShift($branch, '08:00', '14:00');
        Attendance::create([
            'user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id,
            'check_in' => '2026-10-05 08:20:00', 'method' => 'face', 'late_reason' => 'Kẹt xe cầu Rạch Miễu',
        ]);

        $this->actingAs($this->makeUser('admin'))->get("/admin/attendances?branch_id={$branch->id}&date=2026-10-05")
            ->assertOk()->assertSee($staff->name)->assertSee('Kẹt xe cầu Rạch Miễu');
    }

    public function test_no_write_routes_for_admin(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/admin/payrolls/recalculate')->assertNotFound();
        $this->actingAs($admin)->post('/admin/attendances')->assertStatus(405); // chỉ có GET
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=HrViewTest`
Expected: FAIL (404 ở các trang GET).

- [ ] **Step 3: Route + controller**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\PayrollController;`, `use App\Http\Controllers\Admin\AttendanceController;`, trong group:

```php
        Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
```

`app/Http/Controllers/Admin/PayrollController.php`:

```php
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
```

`app/Http/Controllers/Admin/AttendanceController.php`:

```php
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
```

- [ ] **Step 4: View**

`resources/views/admin/payrolls/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Bảng lương')

@section('content')
    @php
        $money  = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $status = ['draft' => 'Nháp', 'confirmed' => 'Đã xác nhận', 'paid' => 'Đã trả'];
    @endphp
    <h1 class="text-xl font-bold mb-1">Bảng lương</h1>
    <p class="text-sm text-slate-500 mb-4">Chỉ xem. Tính, sửa thưởng/khấu trừ, xác nhận và trả lương do quản lý chi nhánh thực hiện.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            @foreach ($branches as $b) <option value="{{ $b->id }}" @selected($branch?->id === $b->id)>{{ $b->name }}</option> @endforeach
        </select>
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Giờ công</th><th class="px-4 py-3">Ngày công</th><th class="px-4 py-3">Lương cơ bản</th><th class="px-4 py-3">Thưởng</th><th class="px-4 py-3">Khấu trừ</th><th class="px-4 py-3">Tổng</th><th class="px-4 py-3">Trạng thái</th></tr>
            </thead>
            <tbody>
                @forelse ($payrolls as $p)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold">{{ $p->user?->name }}</td>
                        <td class="px-4 py-3">{{ $p->total_hours }}</td>
                        <td class="px-4 py-3">{{ $p->total_days }}</td>
                        <td class="px-4 py-3">{{ $money($p->base_salary) }}</td>
                        <td class="px-4 py-3">{{ $money($p->bonus) }}</td>
                        <td class="px-4 py-3">{{ $money($p->deduction) }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $money($p->total) }}</td>
                        <td class="px-4 py-3">{{ $status[$p->status] ?? $p->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Chi nhánh chưa có bảng lương tháng này.</td></tr>
                @endforelse
            </tbody>
            @if ($payrolls->isNotEmpty())
                <tfoot><tr class="font-bold"><td class="px-4 py-3" colspan="6">Tổng quỹ lương</td><td class="px-4 py-3">{{ $money($payrolls->sum('total')) }}</td><td></td></tr></tfoot>
            @endif
        </table>
    </div>
@endsection
```

`resources/views/admin/attendances/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Chấm công')

@section('content')
    <h1 class="text-xl font-bold mb-1">Chấm công</h1>
    <p class="text-sm text-slate-500 mb-4">Chỉ xem. Chấm công tay và chấm ra hộ do quản lý chi nhánh thực hiện.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            @foreach ($branches as $b) <option value="{{ $b->id }}" @selected($branch?->id === $b->id)>{{ $b->name }}</option> @endforeach
        </select>
        <input type="date" name="date" value="{{ $date }}" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Ca</th><th class="px-4 py-3">Vào</th><th class="px-4 py-3">Ra</th><th class="px-4 py-3">Cách chấm</th><th class="px-4 py-3">Trễ / sớm</th></tr>
            </thead>
            <tbody>
                @forelse ($attendances as $a)
                    <tr class="border-b border-slate-50">
                        <td class="px-4 py-3 font-semibold">{{ $a->user?->name }}</td>
                        <td class="px-4 py-3">{{ $a->shift?->name }}</td>
                        <td class="px-4 py-3">{{ $a->check_in?->format('H:i') }}</td>
                        <td class="px-4 py-3">{{ $a->check_out?->format('H:i') ?? 'Chưa ra' }}</td>
                        <td class="px-4 py-3">{{ $a->method === 'face' ? 'Khuôn mặt' : 'Thủ công' }}</td>
                        <td class="px-4 py-3 text-xs">
                            @if ($a->lateMinutes() > 0) <p>Trễ {{ $a->lateMinutes() }}'@if ($a->late_reason): {{ $a->late_reason }}@endif</p> @endif
                            @if ($a->earlyMinutes() > 0) <p>Sớm {{ $a->earlyMinutes() }}'@if ($a->early_reason): {{ $a->early_reason }}@endif</p> @endif
                            @if ($a->lateMinutes() <= 0 && $a->earlyMinutes() <= 0 && ($a->late_reason || $a->early_reason)) <p>{{ $a->late_reason ?? $a->early_reason }}</p> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Không có lượt chấm công nào trong ngày.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 5: Chạy test, thấy PASS**

Run: `php artisan test --filter=HrViewTest`
Expected: PASS 3 test.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/PayrollController.php app/Http/Controllers/Admin/AttendanceController.php resources/views/admin/payrolls resources/views/admin/attendances routes/admin.php tests/Feature/Admin/HrViewTest.php
git commit -m "feat(admin): xem bảng lương và chấm công theo chi nhánh (chỉ xem)"
```

---

### Task 5: Cài đặt chung (footer, trang hỗ trợ, phương thức thanh toán)

**Files:**
- Create: `app/Support/PaymentMethods.php`
- Create: `app/Http/Controllers/Admin/SettingController.php`, `resources/views/admin/settings/edit.blade.php`
- Modify: `routes/admin.php`
- Modify: `resources/views/layouts/app.blade.php` (khối "Thông Tin Liên Hệ" ở footer), `resources/views/pages/support.blade.php`
- Modify: `app/Http/Controllers/CheckoutController.php` (`index`, `store`), `resources/views/checkout/index.blade.php` (khối "Phương thức thanh toán")
- Test: `tests/Feature/Admin/SettingTest.php`

**Interfaces:**
- Consumes: `Setting::get(string $key, $default)`, `Setting::set(string $key, $value)` (có sẵn, có cache).
- Produces:
  - `PaymentMethods::LABELS = ['cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPay']`
  - `PaymentMethods::enabled(): array` — các key đang bật, theo thứ tự trên; đọc `Setting::get("payment_{$key}", '1') === '1'`.
  - `SettingController::FIELDS` — danh sách key cài đặt dạng chữ.
  - Route `admin.settings.edit`, `admin.settings.update`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/SettingTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Setting;
use App\Support\PaymentMethods;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class SettingTest extends TestCase
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
            'brand_name' => 'ZomZop', 'brand_slogan' => 'Ăn ngon mỗi ngày', 'hotline' => '1900 8888',
            'email' => 'hotro@zomzop.vn', 'address' => '12 Ấp Bắc, Mỹ Tho', 'open_hours' => '08:00 - 22:00',
            'footer_about' => 'Chuỗi đồ ăn nhanh', 'footer_facebook' => 'https://facebook.com/zomzop', 'footer_zalo' => '',
            'payment_cash' => '1', 'payment_momo' => '1',
        ], $over);
    }

    public function test_admin_saves_settings_and_footer_uses_them(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->put('/admin/settings', $this->validData())
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('1900 8888', Setting::get('hotline'));
        $this->assertSame('settings.update', AdminLog::sole()->action);

        $this->get('/support')->assertOk()->assertSee('1900 8888')->assertSee('hotro@zomzop.vn');
        $this->get('/about-us')->assertOk()->assertSee('1900 8888'); // footer
    }

    public function test_validation(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->put('/admin/settings', $this->validData(['email' => 'khong-phai-email', 'footer_facebook' => 'abc']))
            ->assertSessionHasErrors(['email', 'footer_facebook']);

        $this->actingAs($admin)->put('/admin/settings', $this->validData(['payment_cash' => null, 'payment_momo' => null]))
            ->assertSessionHasErrors('payment');
    }

    public function test_disabled_payment_method_is_hidden_and_rejected(): void
    {
        $this->actingAs($this->makeUser('admin'))->put('/admin/settings', $this->validData()); // vnpay không gửi = tắt

        $this->assertSame(['cash', 'momo'], PaymentMethods::enabled());

        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $cart   = ['branch_id' => $branch->id, 'items' => [['id' => $item->id, 'name' => $item->name, 'price' => 50000, 'quantity' => 1, 'image' => '']]];
        $customer = $this->makeUser('customer');

        $this->actingAs($customer)->withSession(['cart' => $cart])->get('/checkout')
            ->assertOk()->assertDontSee('value="vnpay"', false);

        $this->actingAs($customer)->withSession(['cart' => $cart])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'vnpay'])
            ->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=SettingTest`
Expected: FAIL (404, class `PaymentMethods` không tồn tại).

- [ ] **Step 3: Helper phương thức thanh toán**

`app/Support/PaymentMethods.php`:

```php
<?php

namespace App\Support;

use App\Models\Setting;

class PaymentMethods
{
    public const LABELS = ['cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPay'];

    /** Các phương thức admin đang bật (chưa cài đặt gì = bật hết) */
    public static function enabled(): array
    {
        return array_values(array_filter(
            array_keys(self::LABELS),
            fn ($key) => (string) Setting::get("payment_{$key}", '1') === '1'
        ));
    }
}
```

- [ ] **Step 4: Controller + route cài đặt**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\SettingController;`, trong group:

```php
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
```

`app/Http/Controllers/Admin/SettingController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Support\PaymentMethods;
use Illuminate\Http\Request;

class SettingController extends AdminController
{
    /** key => [nhãn, rule validate, giá trị mặc định khi chưa có trong DB] */
    public const FIELDS = [
        'brand_name'      => ['Tên thương hiệu', ['required', 'string', 'max:50'], 'ZomZop'],
        'brand_slogan'    => ['Khẩu hiệu', ['nullable', 'string', 'max:100'], ''],
        'hotline'         => ['Hotline', ['required', 'string', 'max:20', 'regex:/^[0-9 +.]{6,20}$/'], '1900 1234'],
        'email'           => ['Email hỗ trợ', ['required', 'email', 'max:150'], 'contact@zomzop.vn'],
        'address'         => ['Địa chỉ trụ sở', ['nullable', 'string', 'max:255'], ''],
        'open_hours'      => ['Giờ mở cửa', ['nullable', 'string', 'max:50'], '09:00 - 22:00'],
        'footer_about'    => ['Giới thiệu ở chân trang', ['nullable', 'string', 'max:500'], ''],
        'footer_facebook' => ['Link Facebook', ['nullable', 'url', 'max:255'], ''],
        'footer_zalo'     => ['Link Zalo', ['nullable', 'url', 'max:255'], ''],
    ];

    public function edit()
    {
        $values = collect(self::FIELDS)->mapWithKeys(fn ($f, $key) => [$key => Setting::get($key, $f[2])]);

        return view('admin.settings.edit', [
            'fields'   => self::FIELDS,
            'values'   => $values,
            'payments' => PaymentMethods::LABELS,
            'enabled'  => PaymentMethods::enabled(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(
            collect(self::FIELDS)->map(fn ($f) => $f[1])->all(),
            [
                'required'      => ':attribute không được để trống.',
                'email'         => 'Email không hợp lệ.',
                'url'           => ':attribute phải là đường dẫn đầy đủ (https://…).',
                'hotline.regex' => 'Hotline chỉ gồm số, khoảng trắng, dấu + hoặc dấu chấm.',
                'max'           => ':attribute quá dài.',
            ],
            collect(self::FIELDS)->map(fn ($f) => $f[0])->all(),
        );

        $payments = collect(PaymentMethods::LABELS)->keys()->mapWithKeys(fn ($k) => ["payment_{$k}" => $request->boolean("payment_{$k}") ? '1' : '0']);
        if (!$payments->contains('1')) {
            return back()->withInput()->withErrors(['payment' => 'Phải bật ít nhất một phương thức thanh toán.']);
        }

        $before = [];
        $after  = [];
        foreach ($payments->all() + array_map(fn ($v) => (string) $v, $data) as $key => $value) {
            $old = (string) Setting::get($key, self::FIELDS[$key][2] ?? '1');
            if ($old !== $value) {
                $before[$key] = $old;
                $after[$key]  = $value;
                Setting::set($key, $value);
            }
        }
        $this->log('settings.update', null, ['before' => $before, 'after' => $after]);

        return redirect()->route('admin.settings.edit')->with('success', 'Đã lưu cài đặt.');
    }
}
```

> `Setting::set()` dùng `updateOrCreate` nên key chưa có sẽ được tạo (group mặc định `general`). `Setting::get()` có cache; `set()` đã tự xoá cache của key đó.

- [ ] **Step 5: View cài đặt**

`resources/views/admin/settings/edit.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Cài đặt chung')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">Cài đặt chung</h1>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf @method('PUT')

        @foreach ($fields as $key => [$label])
            <label class="grid gap-1">{{ $label }}
                @if ($key === 'footer_about')
                    <textarea name="{{ $key }}" rows="3" class="{{ $input }}">{{ old($key, $values[$key]) }}</textarea>
                @else
                    <input name="{{ $key }}" value="{{ old($key, $values[$key]) }}" class="{{ $input }}">
                @endif
            </label>
        @endforeach

        <fieldset class="grid gap-2">
            <legend class="font-semibold mb-1">Phương thức thanh toán cho khách</legend>
            @foreach ($payments as $key => $label)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="payment_{{ $key }}" value="1" @checked(old("payment_{$key}", in_array($key, $enabled, true))) class="accent-red-500"> {{ $label }}
                </label>
            @endforeach
            <p class="text-xs text-slate-400">MoMo/VNPay hiện chưa nối cổng thanh toán — khách chọn thì chỉ ghi nhận phương thức.</p>
        </fieldset>

        <button class="justify-self-start px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu cài đặt</button>
    </form>
@endsection
```

- [ ] **Step 6: Footer và trang hỗ trợ đọc cài đặt**

`resources/views/layouts/app.blade.php` — trong khối `<h4 ...>Thông Tin Liên Hệ</h4>`, thay 4 dòng `<li>` bằng:

```blade
                        <li class="flex items-center gap-1.5"><span>📞</span> <strong class="text-slate-700">Hotline:</strong> {{ \App\Models\Setting::get('hotline', '1900 1234') }}</li>
                        <li class="flex items-center gap-1.5"><span>✉️</span> <strong class="text-slate-700">Email:</strong> {{ \App\Models\Setting::get('email', 'contact@zomzop.vn') }}</li>
                        <li class="flex items-center gap-1.5"><span>⏰</span> <strong class="text-slate-700">Giờ mở cửa:</strong> {{ \App\Models\Setting::get('open_hours', '09:00 - 22:00') }}</li>
                        <li class="flex items-start gap-1.5"><span class="mt-0.5">📍</span> <span><strong class="text-slate-700">Trụ sở:</strong> {{ \App\Models\Setting::get('address', 'Toà nhà ZomZop, Quận 1, TP. Hồ Chí Minh') }}</span></li>
```

`resources/views/pages/support.blade.php` — thay 3 giá trị cứng:
- `1900 1234 567` → `{{ \App\Models\Setting::get('hotline', '1900 1234') }}`
- `hotro@zomzop.vn` → `{{ \App\Models\Setting::get('email', 'contact@zomzop.vn') }}`
- `123 Đường Cơm Tấm, Quận 1, TP.HCM` → `{{ \App\Models\Setting::get('address', '123 Đường Cơm Tấm, Quận 1, TP.HCM') }}`

- [ ] **Step 7: Thanh toán chỉ hiện phương thức đang bật**

`app/Http/Controllers/CheckoutController.php`:
- Thêm `use App\Support\PaymentMethods;` và `use Illuminate\Validation\Rule;`.
- Trong `index(...)`, ngay trước `return view('checkout.index', ...)`, thêm `$paymentMethods = PaymentMethods::enabled();` và thêm `'paymentMethods'` vào danh sách `compact(...)`.
- Trong `store(...)`, thay rule `'payment_method' => 'required|in:cash,momo,vnpay',` bằng:

```php
            'payment_method'   => ['required', Rule::in(PaymentMethods::enabled())],
```

và thêm vào mảng message của validate này (tạo mảng thứ 2 nếu chưa có): `'payment_method.in' => 'Phương thức thanh toán này đang tạm ngưng, vui lòng chọn cách khác.'`.

`resources/views/checkout/index.blade.php` — trong khối "Phương thức thanh toán":
- Bọc **mỗi** `<label ...>` bằng điều kiện, ví dụ label tiền mặt: `@if (in_array('cash', $paymentMethods)) <label ...> ... </label> @endif`; tương tự `momo`, `vnpay`.
- Bỏ `checked` cố định ở radio `cash`; thêm vào **mỗi** radio: `@checked($paymentMethods[0] === 'cash')` (đổi `'cash'` thành `'momo'`, `'vnpay'` cho đúng radio) để phương thức bật đầu tiên được chọn sẵn.

- [ ] **Step 8: Chạy test, thấy PASS**

Run: `php artisan test --filter="SettingTest|Tests\\Feature\\Menu|CheckoutCouponTest"`
Expected: PASS (nếu Phase B chưa merge thì bỏ `CheckoutCouponTest` khỏi filter).

- [ ] **Step 9: Commit**

```bash
git add app/Support/PaymentMethods.php app/Http/Controllers/Admin/SettingController.php resources/views/admin/settings routes/admin.php resources/views/layouts/app.blade.php resources/views/pages/support.blade.php app/Http/Controllers/CheckoutController.php resources/views/checkout/index.blade.php tests/Feature/Admin/SettingTest.php
git commit -m "feat(admin): cài đặt chung (liên hệ, chân trang, bật/tắt phương thức thanh toán)"
```

---

### Task 6: Trang nhật ký thao tác

**Files:**
- Create: `app/Http/Controllers/Admin/LogController.php`, `resources/views/admin/logs/index.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/LogPageTest.php`

**Interfaces:**
- Consumes: `AdminLog` + quan hệ `user` (Phase A Task 1).
- Produces: route `admin.logs.index`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/LogPageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class LogPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_logs_newest_first_with_filter(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);
        AdminLog::record('branch.create', null, ['after' => ['name' => 'Chi nhánh Cũ']]);
        AdminLog::record('coupon.create', null, ['after' => ['code' => 'MAMOI']]);

        $this->get('/admin/logs')->assertOk()->assertSeeInOrder(['coupon.create', 'branch.create']);
        $this->get('/admin/logs?action=coupon')->assertOk()->assertSee('MAMOI')->assertDontSee('Chi nhánh Cũ');
    }

    public function test_manager_cannot_see_logs(): void
    {
        $this->actingAs($this->makeUser('manager', $this->makeBranch()))->get('/admin/logs')->assertForbidden();
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=LogPageTest`
Expected: FAIL (404).

- [ ] **Step 3: Route + controller + view**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\LogController;`, trong group:

```php
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
```

`app/Http/Controllers/Admin/LogController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use Illuminate\Http\Request;

class LogController extends AdminController
{
    public function index(Request $request)
    {
        $action = preg_replace('/[^a-z_.]/', '', strtolower((string) $request->query('action')));
        $date   = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date')) ? $request->query('date') : null;

        $logs = AdminLog::with('user')
            ->when($action, fn ($q) => $q->where('action', 'like', "{$action}%"))
            ->when($date, fn ($q) => $q->whereDate('created_at', $date))
            ->latest('id')
            ->paginate(30)->withQueryString();

        return view('admin.logs.index', compact('logs', 'action', 'date'));
    }
}
```

`resources/views/admin/logs/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Nhật ký thao tác')

@section('content')
    <h1 class="text-xl font-bold mb-4">Nhật ký thao tác</h1>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="action" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Mọi thao tác</option>
            @foreach (['branch' => 'Chi nhánh', 'manager' => 'Tài khoản quản lý', 'customer' => 'Khách hàng', 'banner' => 'Banner', 'coupon' => 'Mã giảm giá', 'settings' => 'Cài đặt', 'category' => 'Danh mục', 'menu_item' => 'Món ăn'] as $k => $label)
                <option value="{{ $k }}" @selected($action === $k)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ $date }}" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Thời gian</th><th class="px-4 py-3">Người làm</th><th class="px-4 py-3">Thao tác</th><th class="px-4 py-3">Đối tượng</th><th class="px-4 py-3">Thay đổi</th><th class="px-4 py-3">IP</th></tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b border-slate-50 align-top">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $log->action }}</td>
                        <td class="px-4 py-3">{{ $log->subject_type ? $log->subject_type . ' #' . $log->subject_id : '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($log->changes)
                                <details><summary class="cursor-pointer text-red-500 text-xs">Xem</summary>
                                    <pre class="text-xs bg-slate-50 rounded-lg p-2 mt-1 whitespace-pre-wrap">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Chưa có thao tác nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
```

> Test lọc `?action=coupon` kiểm tra `assertSee('MAMOI')` — giá trị này nằm trong `<pre>` bên trong `<details>` nên vẫn có trong HTML.

- [ ] **Step 4: Chạy test, thấy PASS**

Run: `php artisan test --filter=LogPageTest` rồi `php artisan test` (toàn bộ).
Expected: PASS (trừ `Tests\Feature\ExampleTest` đã fail từ trước).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Admin/LogController.php resources/views/admin/logs routes/admin.php tests/Feature/Admin/LogPageTest.php
git commit -m "feat(admin): trang nhật ký thao tác (lọc theo loại, ngày)"
```
