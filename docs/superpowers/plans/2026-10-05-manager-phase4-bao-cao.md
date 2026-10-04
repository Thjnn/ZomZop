# Dashboard Manager — Giai đoạn 4: Báo Cáo & Đánh Giá — Kế Hoạch Triển Khai

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Mục tiêu:** Manager xem báo cáo doanh thu của chi nhánh theo khoảng ngày tuỳ chọn (tổng quan, theo ngày, theo hình thức, theo thanh toán, món bán chạy) và xem đánh giá của khách.

**Kiến trúc:** Service `BranchReport` tính số liệu trực tiếp từ `orders` / `order_items` (không dùng bảng `daily_sales_summary` vì bảng này chỉ có dữ liệu seed, không ai cập nhật). Hai controller `ReportController`, `ReviewController`. Không thêm thư viện biểu đồ; dùng bảng + thanh `div` như trang tổng quan.

**Tech Stack:** Laravel 13, Blade, Tailwind 4, PHPUnit (SQLite in-memory).

**Spec:** Không có file spec; quyết định ở dưới.

## Quyết định thiết kế

1. **Doanh thu** = tổng `total` các đơn `completed` có ngày tạo trong khoảng (giống trang tổng quan). Số đơn huỷ, tỉ lệ huỷ tính trên tổng đơn trong khoảng.
2. **Khoảng ngày mặc định:** 7 ngày gần nhất (hôm nay − 6 → hôm nay). Tối đa 366 ngày. `from > to` hoặc ngày sai → báo lỗi tại chỗ và dùng mặc định (không redirect, cùng cách đã sửa ở danh sách đơn).
3. **Giá trị trung bình đơn** = doanh thu / số đơn hoàn thành (0 nếu không có đơn).
4. **Đánh giá:** danh sách mới nhất trước, lọc theo số sao, phân trang 15. Phần đầu trang: điểm trung bình, số lượt, phân bố 1–5 sao, điểm giao hàng trung bình. Manager chỉ xem (chưa trả lời đánh giá — bảng không có cột trả lời).
5. **Xuất Excel:** chưa làm.

## Global Constraints

- PHP: `/e/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe` (gọi tắt `$PHP`).
- Test class: `RefreshDatabase`, `CreatesBranchData`, `withoutVite()`.
- Tiếng Việt; tiền `number_format($v, 0, ',', '.') . 'đ'`. Nhánh `manager-dashboard`, commit mỗi task, **không push**.

## Review Focus

1. **Khoảng ngày bậy** (`from=abc`, `from` sau `to`, khoảng 5 năm): không lỗi 500, báo lỗi tiếng Việt, hiện báo cáo mặc định. Test ở Task 2.
2. **Đơn của chi nhánh khác / đơn chưa hoàn thành** không được lọt vào doanh thu và món bán chạy. Test ở Task 1.
3. **Đơn tạo lúc 23:30 ngày cuối khoảng** phải được tính (so sánh theo ngày, không theo `to 00:00`). Test ở Task 1.
4. **Đánh giá chi nhánh khác** không hiện; lọc sao bậy (`rating=9`) không lỗi. Test ở Task 3.
5. **Khoảng không có đơn nào**: không chia cho 0, hiện 0đ. Test ở Task 1.

---

## Cấu trúc file

| File | Loại | Trách nhiệm |
|---|---|---|
| `app/Services/BranchReport.php` | Tạo | Truy vấn số liệu theo khoảng ngày |
| `app/Http/Controllers/Manager/ReportController.php` | Tạo | Trang báo cáo |
| `app/Http/Controllers/Manager/ReviewController.php` | Tạo | Trang đánh giá |
| `routes/manager.php` | Sửa | `manager.reports.index`, `manager.reviews.index` |
| `resources/views/manager/reports/index.blade.php` | Tạo | |
| `resources/views/manager/reviews/index.blade.php` | Tạo | |
| `resources/views/layouts/manager.blade.php` | Sửa | 2 mục nav |
| `tests/Feature/Manager/BranchReportTest.php` | Tạo | |
| `tests/Feature/Manager/ReportPageTest.php` | Tạo | |
| `tests/Feature/Manager/ReviewPageTest.php` | Tạo | |

---

### Task 1: Service `BranchReport`

**Files:**
- Create: `app/Services/BranchReport.php`
- Test: `tests/Feature/Manager/BranchReportTest.php`

**Interfaces:**
- Produces (mọi hàm nhận `int $branchId, Carbon $from, Carbon $to`, so sánh theo **ngày**, bao gồm cả hai đầu):
  - `summary(): array{revenue:int, orders:int, completed:int, cancelled:int, avg_order:int, cancel_rate:float}` (`cancel_rate` là % làm tròn 1 chữ số).
  - `daily(): array<string Y-m-d, array{revenue:int, orders:int}>` — đủ mọi ngày trong khoảng.
  - `byType(): array{takeaway: array{orders:int, revenue:int}, delivery: array{orders:int, revenue:int}}` — chỉ đơn `completed`.
  - `byPayment(): array{cash:…, momo:…, vnpay:…}` cùng dạng — chỉ đơn `completed`.
  - `topItems(int $branchId, Carbon $from, Carbon $to, int $limit = 10): Collection` của `{name, qty, revenue}` — chỉ đơn `completed`.

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/BranchReportTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Services\BranchReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BranchReportTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private function at(string $time, callable $fn)
    {
        $this->travelTo(Carbon::parse($time));
        return $fn();
    }

    private function range(): array
    {
        return [Carbon::parse('2026-10-01'), Carbon::parse('2026-10-03')];
    }

    /** Review Focus #2, #3 */
    public function test_summary_counts_range_inclusive_and_own_branch_only(): void
    {
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');
        $this->at('2026-09-30 23:59', fn () => $this->makeOrder($mine, ['status' => 'completed', 'total' => 999000]));
        $this->at('2026-10-01 00:01', fn () => $this->makeOrder($mine, ['status' => 'completed', 'total' => 100000]));
        $this->at('2026-10-03 23:30', fn () => $this->makeOrder($mine, ['status' => 'completed', 'total' => 50000]));
        $this->at('2026-10-02 12:00', fn () => $this->makeOrder($mine, ['status' => 'cancelled', 'total' => 70000]));
        $this->at('2026-10-02 12:00', fn () => $this->makeOrder($mine, ['status' => 'pending', 'total' => 30000]));
        $this->at('2026-10-02 12:00', fn () => $this->makeOrder($other, ['status' => 'completed', 'total' => 888000]));

        [$from, $to] = $this->range();
        $this->assertSame([
            'revenue' => 150000, 'orders' => 4, 'completed' => 2, 'cancelled' => 1,
            'avg_order' => 75000, 'cancel_rate' => 25.0,
        ], (new BranchReport())->summary($mine->id, $from, $to));
    }

    /** Review Focus #5 */
    public function test_empty_range_has_zeros(): void
    {
        $branch = $this->makeBranch();
        [$from, $to] = $this->range();

        $this->assertSame([
            'revenue' => 0, 'orders' => 0, 'completed' => 0, 'cancelled' => 0, 'avg_order' => 0, 'cancel_rate' => 0.0,
        ], (new BranchReport())->summary($branch->id, $from, $to));
    }

    public function test_daily_fills_every_day(): void
    {
        $branch = $this->makeBranch();
        $this->at('2026-10-01 09:00', fn () => $this->makeOrder($branch, ['status' => 'completed', 'total' => 40000]));
        $this->at('2026-10-03 09:00', fn () => $this->makeOrder($branch, ['status' => 'cancelled', 'total' => 40000]));

        [$from, $to] = $this->range();
        $this->assertSame([
            '2026-10-01' => ['revenue' => 40000, 'orders' => 1],
            '2026-10-02' => ['revenue' => 0, 'orders' => 0],
            '2026-10-03' => ['revenue' => 0, 'orders' => 1],
        ], (new BranchReport())->daily($branch->id, $from, $to));
    }

    public function test_by_type_and_payment_count_completed_only(): void
    {
        $branch = $this->makeBranch();
        $this->at('2026-10-02 09:00', function () use ($branch) {
            $this->makeOrder($branch, ['status' => 'completed', 'type' => 'delivery', 'payment_method' => 'momo', 'total' => 60000]);
            $this->makeOrder($branch, ['status' => 'completed', 'type' => 'takeaway', 'payment_method' => 'cash', 'total' => 40000]);
            $this->makeOrder($branch, ['status' => 'cancelled', 'type' => 'delivery', 'payment_method' => 'momo', 'total' => 99000]);
        });

        [$from, $to] = $this->range();
        $report = new BranchReport();
        $this->assertSame([
            'takeaway' => ['orders' => 1, 'revenue' => 40000],
            'delivery' => ['orders' => 1, 'revenue' => 60000],
        ], $report->byType($branch->id, $from, $to));
        $this->assertSame([
            'cash'  => ['orders' => 1, 'revenue' => 40000],
            'momo'  => ['orders' => 1, 'revenue' => 60000],
            'vnpay' => ['orders' => 0, 'revenue' => 0],
        ], $report->byPayment($branch->id, $from, $to));
    }

    public function test_top_items_in_range(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $coke   = $this->makeMenuItem('Coca');
        $this->at('2026-10-02 09:00', function () use ($branch, $burger, $coke) {
            $o = $this->makeOrder($branch, ['status' => 'completed']);
            $this->addItem($o, $burger, 3);
            $this->addItem($o, $coke, 5, 10000);
            $x = $this->makeOrder($branch, ['status' => 'cancelled']);
            $this->addItem($x, $burger, 50);
        });
        $this->at('2026-10-09 09:00', fn () => $this->addItem($this->makeOrder($branch, ['status' => 'completed']), $burger, 99));

        [$from, $to] = $this->range();
        $top = (new BranchReport())->topItems($branch->id, $from, $to);

        $this->assertSame(['Coca', 'Burger'], $top->pluck('name')->all());
        $this->assertSame([5, 3], $top->pluck('qty')->map(fn ($v) => (int) $v)->all());
    }
}
```

- [ ] **Step 2: Chạy, xác nhận fail** — Run: `$PHP artisan test tests/Feature/Manager/BranchReportTest.php` — Expected: FAIL `Class "App\Services\BranchReport" not found`.

- [ ] **Step 3: Viết service**

`app/Services/BranchReport.php`:

```php
<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BranchReport
{
    /** Đơn của chi nhánh, tạo trong [from, to] tính theo ngày (gồm cả ngày cuối) */
    private function orders(int $branchId, Carbon $from, Carbon $to): Builder
    {
        return Order::ofBranch($branchId)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString());
    }

    public function summary(int $branchId, Carbon $from, Carbon $to): array
    {
        $row = $this->orders($branchId, $from, $to)
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->first();

        $orders    = (int) $row->orders;
        $revenue   = (int) $row->revenue;
        $completed = (int) $row->completed;
        $cancelled = (int) $row->cancelled;

        return [
            'revenue'     => $revenue,
            'orders'      => $orders,
            'completed'   => $completed,
            'cancelled'   => $cancelled,
            'avg_order'   => $completed ? intdiv($revenue, $completed) : 0,
            'cancel_rate' => $orders ? round($cancelled / $orders * 100, 1) : 0.0,
        ];
    }

    public function daily(int $branchId, Carbon $from, Carbon $to): array
    {
        $rows = $this->orders($branchId, $from, $to)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->groupBy('day')
            ->get()->keyBy('day');

        $result = [];
        for ($d = $from->copy()->startOfDay(); $d->lte($to); $d->addDay()) {
            $key = $d->toDateString();
            $result[$key] = ['revenue' => (int) ($rows[$key]->revenue ?? 0), 'orders' => (int) ($rows[$key]->orders ?? 0)];
        }

        return $result;
    }

    private function completedGroupedBy(string $column, array $keys, int $branchId, Carbon $from, Carbon $to): array
    {
        $rows = $this->orders($branchId, $from, $to)
            ->where('status', 'completed')
            ->selectRaw("{$column} as k, COUNT(*) as orders, SUM(total) as revenue")
            ->groupBy('k')
            ->get()->keyBy('k');

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = ['orders' => (int) ($rows[$key]->orders ?? 0), 'revenue' => (int) ($rows[$key]->revenue ?? 0)];
        }

        return $result;
    }

    public function byType(int $branchId, Carbon $from, Carbon $to): array
    {
        return $this->completedGroupedBy('type', ['takeaway', 'delivery'], $branchId, $from, $to);
    }

    public function byPayment(int $branchId, Carbon $from, Carbon $to): array
    {
        return $this->completedGroupedBy('payment_method', ['cash', 'momo', 'vnpay'], $branchId, $from, $to);
    }

    public function topItems(int $branchId, Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', '>=', $from->toDateString())
            ->whereDate('orders.created_at', '<=', $to->toDateString())
            ->groupBy('order_items.menu_item_id', 'order_items.name_snapshot')
            ->selectRaw('order_items.name_snapshot as name, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as revenue')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }
}
```

- [ ] **Step 4: Chạy test** — Run: `$PHP artisan test tests/Feature/Manager/BranchReportTest.php` — Expected: PASS (5 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Services/BranchReport.php tests/Feature/Manager/BranchReportTest.php
git commit -m "feat(manager): service BranchReport — báo cáo theo khoảng ngày"
```

---

### Task 2: Trang báo cáo

**Files:**
- Create: `app/Http/Controllers/Manager/ReportController.php`
- Modify: `routes/manager.php`
- Create: `resources/views/manager/reports/index.blade.php`
- Modify: `resources/views/layouts/manager.blade.php`
- Test: `tests/Feature/Manager/ReportPageTest.php`

**Interfaces:**
- Consumes: `BranchReport` (Task 1).
- Produces: route `manager.reports.index` (GET `/manager/reports`, query `from`, `to` dạng Y-m-d).

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/ReportPageTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));
    }

    public function test_default_range_is_last_7_days(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-09-29 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 123000]);
        $this->travelTo(Carbon::parse('2026-09-28 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 777000]);
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/reports')
            ->assertOk()
            ->assertSee('value="2026-09-29"', false)
            ->assertSee('value="2026-10-05"', false)
            ->assertSee('123.000đ')
            ->assertDontSee('777.000đ');
    }

    public function test_custom_range(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-09-01 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 777000]);
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/reports?from=2026-09-01&to=2026-09-30')
            ->assertSee('777.000đ');
    }

    /** Review Focus #1 */
    public function test_bad_ranges_show_error_and_fall_back(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->get('/manager/reports?from=abc&to=2026-10-01')
            ->assertOk()->assertSee('Ngày bắt đầu không hợp lệ.')->assertSee('value="2026-09-29"', false);

        $this->actingAs($manager)->get('/manager/reports?from=2026-10-05&to=2026-10-01')
            ->assertOk()->assertSee('Ngày kết thúc phải từ ngày bắt đầu trở đi.');

        $this->actingAs($manager)->get('/manager/reports?from=2020-01-01&to=2026-10-01')
            ->assertOk()->assertSee('Khoảng ngày tối đa 366 ngày.');
    }
}
```

- [ ] **Step 2: Chạy, xác nhận fail** — Run: `$PHP artisan test tests/Feature/Manager/ReportPageTest.php` — Expected: FAIL 404.

- [ ] **Step 3: Thêm route** (thêm `use App\Http\Controllers\Manager\ReportController;`):

```php
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
```

- [ ] **Step 4: Tạo `ReportController`**

`app/Http/Controllers/Manager/ReportController.php`:

```php
<?php

namespace App\Http\Controllers\Manager;

use App\Services\BranchReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ReportController extends ManagerController
{
    public function index(Request $request, BranchReport $report)
    {
        $branchId = $this->branchId();
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
            // Có lỗi thì dùng khoảng mặc định nếu khoảng hiện tại không dùng được
            if ($to->lt($from) || $from->diffInDays($to) > 366) {
                [$from, $to] = [$defaultFrom, $defaultTo];
            }
        }

        return view('manager.reports.index', [
            'from'     => $from,
            'to'       => $to,
            'summary'  => $report->summary($branchId, $from, $to),
            'daily'    => $report->daily($branchId, $from, $to),
            'byType'   => $report->byType($branchId, $from, $to),
            'byPay'    => $report->byPayment($branchId, $from, $to),
            'topItems' => $report->topItems($branchId, $from, $to),
        ])->withErrors($validator);
    }
}
```

- [ ] **Step 5: Tạo view**

`resources/views/manager/reports/index.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Báo cáo')

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $typeLabel = ['takeaway' => 'Mang đi', 'delivery' => 'Giao hàng'];
        $payLabel  = ['cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPay'];
        $max = max(1, max(array_column($daily, 'revenue') ?: [0]));
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold">Báo cáo</h1>
            <p class="text-sm text-slate-500">{{ $from->format('d/m/Y') }} – {{ $to->format('d/m/Y') }}</p>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-2 text-sm">
            <label class="flex flex-col gap-1"><span class="text-xs text-slate-400">Từ ngày</span>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200"></label>
            <label class="flex flex-col gap-1"><span class="text-xs text-slate-400">Đến ngày</span>
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200"></label>
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Xem</button>
        </form>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Doanh thu', $money($summary['revenue']), 'text-green-600'],
            ['Đơn hoàn thành', $summary['completed'] . ' / ' . $summary['orders'], 'text-slate-800'],
            ['Giá trị TB / đơn', $money($summary['avg_order']), 'text-slate-800'],
            ['Tỉ lệ huỷ', number_format($summary['cancel_rate'], 1, ',', '.') . '%', 'text-slate-500'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-2xl p-4 border border-slate-100">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1 {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <section class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100 overflow-x-auto">
            <h2 class="font-semibold mb-4">Doanh thu theo ngày</h2>
            <table class="w-full text-sm">
                @foreach ($daily as $day => $d)
                    <tr class="border-b border-slate-50">
                        <td class="py-1.5 pr-3 whitespace-nowrap text-slate-500">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</td>
                        <td class="py-1.5 w-full"><div class="h-3 rounded bg-red-400" style="width: {{ round($d['revenue'] / $max * 100) }}%"></div></td>
                        <td class="py-1.5 pl-3 whitespace-nowrap text-right">{{ $money($d['revenue']) }}</td>
                        <td class="py-1.5 pl-3 whitespace-nowrap text-right text-slate-400">{{ $d['orders'] }} đơn</td>
                    </tr>
                @endforeach
            </table>
        </section>

        <div class="space-y-6">
            @foreach ([['Theo hình thức', $byType, $typeLabel], ['Theo thanh toán', $byPay, $payLabel]] as [$title, $rows, $labels])
                <section class="bg-white rounded-2xl p-5 border border-slate-100 text-sm">
                    <h2 class="font-semibold mb-3">{{ $title }}</h2>
                    @foreach ($rows as $key => $r)
                        <div class="flex justify-between py-1.5 border-b border-slate-50">
                            <span>{{ $labels[$key] }} <span class="text-slate-400">({{ $r['orders'] }})</span></span>
                            <span class="font-semibold">{{ $money($r['revenue']) }}</span>
                        </div>
                    @endforeach
                </section>
            @endforeach
        </div>
    </div>

    <section class="bg-white rounded-2xl p-5 border border-slate-100 text-sm">
        <h2 class="font-semibold mb-3">Món bán chạy</h2>
        @forelse ($topItems as $i => $item)
            <div class="flex justify-between py-1.5 border-b border-slate-50">
                <span>{{ $i + 1 }}. {{ $item->name }}</span>
                <span class="text-slate-500">{{ $item->qty }} phần · {{ $money($item->revenue) }}</span>
            </div>
        @empty
            <p class="text-slate-400">Không có đơn hoàn thành trong khoảng này.</p>
        @endforelse
    </section>
@endsection
```

- [ ] **Step 6: Thêm mục nav:**

```php
            ['route' => 'manager.reports.index', 'match' => 'manager.reports.*', 'label' => 'Báo cáo',   'icon' => '📈'],
```

- [ ] **Step 7: Chạy test** — Run: `$PHP artisan test` — Expected: PASS trừ `ExampleTest`.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Manager/ReportController.php routes/manager.php resources/views/manager/reports \
        resources/views/layouts/manager.blade.php tests/Feature/Manager/ReportPageTest.php
git commit -m "feat(manager): trang báo cáo doanh thu theo khoảng ngày"
```

---

### Task 3: Trang đánh giá

**Files:**
- Create: `app/Http/Controllers/Manager/ReviewController.php`
- Modify: `routes/manager.php`
- Create: `resources/views/manager/reviews/index.blade.php`
- Modify: `resources/views/layouts/manager.blade.php`
- Test: `tests/Feature/Manager/ReviewPageTest.php`

**Interfaces:**
- Produces: route `manager.reviews.index` (GET `/manager/reviews`, query `rating` 1–5).

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/ReviewPageTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function review($branch, int $rating, string $comment, ?int $delivery = null): Review
    {
        $order = $this->makeOrder($branch, ['status' => 'completed']);

        return Review::create(['order_id' => $order->id, 'user_id' => $order->user_id, 'branch_id' => $branch->id,
            'rating' => $rating, 'delivery_rating' => $delivery, 'comment' => $comment]);
    }

    /** Review Focus #4 */
    public function test_lists_own_branch_reviews_with_average(): void
    {
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');
        $this->review($mine, 5, 'Burger ngon tuyệt', 4);
        $this->review($mine, 2, 'Giao hơi chậm', 2);
        $this->review($other, 1, 'Chi nhánh khác chê');

        $this->actingAs($this->makeUser('manager', $mine))
            ->get('/manager/reviews')
            ->assertOk()
            ->assertSee('Burger ngon tuyệt')
            ->assertSee('Giao hơi chậm')
            ->assertDontSee('Chi nhánh khác chê')
            ->assertSee('3,5')      // điểm TB
            ->assertSee('2 lượt');
    }

    public function test_filter_by_rating_and_bad_filter(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $this->review($branch, 5, 'Năm sao');
        $this->review($branch, 1, 'Một sao');

        $this->actingAs($manager)->get('/manager/reviews?rating=1')
            ->assertSee('Một sao')->assertDontSee('Năm sao');

        $this->actingAs($manager)->get('/manager/reviews?rating=9')
            ->assertOk()->assertSee('Năm sao')->assertSee('Một sao');
    }
}
```

- [ ] **Step 2: Chạy, xác nhận fail** — Run: `$PHP artisan test tests/Feature/Manager/ReviewPageTest.php` — Expected: FAIL 404.

- [ ] **Step 3: Thêm route** (thêm `use App\Http\Controllers\Manager\ReviewController;`):

```php
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
```

- [ ] **Step 4: Tạo `ReviewController`**

`app/Http/Controllers/Manager/ReviewController.php`:

```php
<?php

namespace App\Http\Controllers\Manager;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends ManagerController
{
    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $rating   = Validator::make($request->only('rating'), ['rating' => ['nullable', 'integer', 'between:1,5']])
            ->valid()['rating'] ?? null;

        $stats = Review::ofBranch($branchId)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg_rating, AVG(delivery_rating) as avg_delivery')
            ->first();
        $distribution = Review::ofBranch($branchId)->selectRaw('rating, COUNT(*) as c')->groupBy('rating')->pluck('c', 'rating');

        $reviews = Review::ofBranch($branchId)
            ->with(['user', 'order'])
            ->when($rating, fn ($q, $r) => $q->where('rating', $r))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('manager.reviews.index', compact('stats', 'distribution', 'reviews', 'rating'));
    }
}
```

- [ ] **Step 5: Tạo view**

`resources/views/manager/reviews/index.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Đánh giá')

@section('content')
    @php $stars = fn ($n) => str_repeat('★', $n) . str_repeat('☆', 5 - $n); @endphp

    <h1 class="text-xl font-bold mb-4">Đánh giá của khách</h1>

    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-slate-100">
            <p class="text-xs text-slate-400">Điểm trung bình</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ number_format((float) $stats->avg_rating, 1, ',', '.') }} ★</p>
            <p class="text-xs text-slate-400">{{ (int) $stats->total }} lượt</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100">
            <p class="text-xs text-slate-400">Điểm giao hàng TB</p>
            <p class="text-2xl font-bold mt-1">{{ $stats->avg_delivery ? number_format((float) $stats->avg_delivery, 1, ',', '.') . ' ★' : '—' }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-xs space-y-1">
            @for ($s = 5; $s >= 1; $s--)
                <a href="{{ route('manager.reviews.index', ['rating' => $s]) }}" class="flex items-center gap-2 hover:text-red-500 {{ $rating == $s ? 'font-bold text-red-500' : '' }}">
                    <span class="w-10">{{ $s }} ★</span>
                    <span class="flex-1 h-2 rounded bg-slate-100"><span class="block h-2 rounded bg-amber-400" style="width: {{ $stats->total ? round(($distribution[$s] ?? 0) / $stats->total * 100) : 0 }}%"></span></span>
                    <span class="w-6 text-right">{{ $distribution[$s] ?? 0 }}</span>
                </a>
            @endfor
            @if ($rating) <a href="{{ route('manager.reviews.index') }}" class="block text-red-500 pt-1">Bỏ lọc</a> @endif
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($reviews as $r)
            <div class="bg-white rounded-2xl p-4 border border-slate-100 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <span class="text-amber-500">{{ $stars($r->rating) }}</span>
                    <span class="text-xs text-slate-400">{{ $r->user?->name }} · {{ $r->order?->order_code }} · {{ $r->created_at->format('d/m/Y') }}</span>
                </div>
                @if ($r->comment) <p class="mt-2">{{ $r->comment }}</p> @endif
                @if ($r->delivery_rating) <p class="mt-1 text-xs text-slate-500">Giao hàng: {{ $r->delivery_rating }} ★</p> @endif
            </div>
        @empty
            <p class="text-slate-400 text-sm">Chưa có đánh giá nào.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>
@endsection
```

- [ ] **Step 6: Thêm mục nav:**

```php
            ['route' => 'manager.reviews.index', 'match' => 'manager.reviews.*', 'label' => 'Đánh giá',  'icon' => '⭐'],
```

- [ ] **Step 7: Chạy test** — Run: `$PHP artisan test` — Expected: PASS trừ `ExampleTest`.

- [ ] **Step 8: Kiểm tra tay:** mở `/manager/reports` (mặc định 7 ngày, đổi khoảng sang cả tháng 6 để thấy đơn mẫu), `/manager/reviews` (chi nhánh Mỹ Tho 1 có 2 đánh giá mẫu).

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Manager/ReviewController.php routes/manager.php resources/views/manager/reviews \
        resources/views/layouts/manager.blade.php tests/Feature/Manager/ReviewPageTest.php
git commit -m "feat(manager): trang đánh giá của khách"
```

---

## Sau cả 3 giai đoạn

- Cập nhật `README.md` (mục ✅ / 🚧) cho đủ các trang manager mới.
- Review toàn nhánh bằng reviewer mới (model mạnh nhất), sửa Critical/Important.
- Test tay trên trình duyệt như giai đoạn 1.
