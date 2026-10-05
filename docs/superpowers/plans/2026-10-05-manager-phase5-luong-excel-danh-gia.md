# Manager giai đoạn 5 — Tính lương, xuất Excel, trả lời đánh giá — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Manager tính lương theo giờ (có thử việc 1 tuần), đặt lương từng nhân viên, xuất .xlsx cho 4 trang, trả lời đánh giá.

**Architecture:** Service `BranchPayroll` tính lương từ `attendances` + `salary_configs` và ghi vào bảng `payrolls` có sẵn. Helper `XlsxExport` (OpenSpout) dùng chung cho mọi nút xuất; mỗi controller tách query của trang xem ra method private để `index` và `export` dùng chung. Trả lời đánh giá là 2 cột mới trên `reviews`.

**Tech Stack:** Laravel 13, PHP 8.3, Blade + Tailwind, SQLite in-memory cho test, `openspout/openspout` ^4.

**Spec:** `docs/superpowers/specs/2026-10-05-manager-luong-excel-tra-loi-danh-gia-design.md`

## Global Constraints

- PHP: `/e/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe` (gọi là `$PHP`); composer: `$PHP /e/laragon/bin/composer/composer.phar`.
- Chạy test: `$PHP artisan test --filter=<Tên>` (SQLite in-memory, không đụng DB dev).
- Mọi thao tác chỉ trên chi nhánh của manager (`$this->branchId()`); bản ghi chi nhánh khác → 404.
- Lương chỉ tính theo giờ; `salary_configs.type` luôn ghi `hourly`.
- Thử việc = 7 ngày kể từ `users.started_at` (đến hết ngày `started_at + 6`); `started_at` null → chính thức.
- Mức lương: số nguyên 1–999.999.999. Thưởng/phạt: số nguyên 0–999.999.999.
- Thông báo lỗi/thành công bằng tiếng Việt, giống các controller manager hiện có.
- Commit cục bộ, **không push**. Cuối commit message: `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`.

## Review Focus

1. Phạt lớn hơn cơ bản + thưởng → tổng âm: phải báo lỗi, không lưu (test ở Task 3).
2. Lượt chấm công qua đêm cuối tháng (check_in 31/10 22:00, check_out 01/11 02:00): tính đủ 4 giờ vào tháng 10, không tính vào tháng 11 (test ở Task 1).
3. Manager sửa thưởng/chốt/đã trả dòng lương chi nhánh khác → 404 (test ở Task 3).
4. File xuất không lộ dữ liệu chi nhánh khác và tôn trọng bộ lọc (test ở Task 5).
5. Nhân viên bị khoá giữa tháng vẫn có dòng lương cho giờ đã làm (test ở Task 1).

## File Structure

| File | Vai trò |
|---|---|
| `database/migrations/2026_10_05_000001_add_probation_to_salary.php` | `users.started_at`, `salary_configs.probation_rate` |
| `database/migrations/2026_10_05_000002_add_reply_to_reviews_table.php` | `reviews.reply`, `reviews.replied_at` |
| `app/Services/BranchPayroll.php` | Tính lương tháng của chi nhánh |
| `app/Http/Controllers/Manager/PayrollController.php` | Trang bảng lương + thưởng/phạt/chốt/đã trả/xuất |
| `resources/views/manager/payrolls/index.blade.php` | View bảng lương |
| `app/Support/XlsxExport.php` | Stream file .xlsx |
| `tests/Feature/Manager/BranchPayrollTest.php`, `PayrollPageTest.php`, `ExportTest.php` | Test mới |
| Sửa: `User`, `SalaryConfig`, `Review` models; `StaffController`, `ReviewController`, `OrderController`, `AttendanceController`, `ReportController`; views staff/reviews/orders/attendances/reports; `layouts/manager.blade.php`; `routes/manager.php`; seeders `SalaryConfigSeeder`, `PayrollSeeder`; `README.md` | |

---

### Task 1: Dữ liệu thử việc + service `BranchPayroll`

**Files:**
- Create: `database/migrations/2026_10_05_000001_add_probation_to_salary.php`
- Modify: `app/Models/User.php`, `app/Models/SalaryConfig.php`
- Create: `app/Services/BranchPayroll.php`
- Test: `tests/Feature/Manager/BranchPayrollTest.php`

**Interfaces:**
- Produces:
  - `User::$started_at` (cast `date`, fillable), `User::salaryConfigs(): HasMany`, `User::latestSalary(): HasOne` (bản `effective_from` lớn nhất, cùng ngày thì `id` lớn nhất), `User::probationEndsAt(): ?Carbon`, `User::isOnProbation(Carbon $at): bool`.
  - `SalaryConfig::$probation_rate` (fillable, cast `decimal:0`).
  - `BranchPayroll::calculate(int $branchId, int $month, int $year): void`.

- [ ] **Step 1: Migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('started_at')->nullable()->after('branch_id')->comment('Ngày bắt đầu làm; thử việc 7 ngày đầu');
        });
        Schema::table('salary_configs', function (Blueprint $table) {
            $table->decimal('probation_rate', 12, 0)->nullable()->after('rate')->comment('Lương thử việc/giờ');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('started_at'));
        Schema::table('salary_configs', fn (Blueprint $table) => $table->dropColumn('probation_rate'));
    }
};
```

- [ ] **Step 2: Model**

`app/Models/User.php`: thêm `'started_at'` vào `$fillable`, `'started_at' => 'date'` vào `$casts`, thêm `use Illuminate\Database\Eloquent\Relations\HasOne;` và `use Illuminate\Support\Carbon;`, rồi thêm vào phần Relationships / Helpers:

```php
    /** Lịch sử mức lương (staff/kitchen) */
    public function salaryConfigs(): HasMany
    {
        return $this->hasMany(SalaryConfig::class);
    }

    /** Mức lương đang áp dụng */
    public function latestSalary(): HasOne
    {
        return $this->hasOne(SalaryConfig::class)->ofMany(['effective_from' => 'max', 'id' => 'max']);
    }

    /** Ngày cuối thử việc (thử việc 7 ngày kể cả ngày bắt đầu) */
    public function probationEndsAt(): ?Carbon
    {
        return $this->started_at?->copy()->addDays(6);
    }

    public function isOnProbation(Carbon $at): bool
    {
        $end = $this->probationEndsAt();

        return $end !== null && $at->toDateString() <= $end->toDateString();
    }
```

`app/Models/SalaryConfig.php`: thêm `'probation_rate'` vào `$fillable` và `'probation_rate' => 'decimal:0'` vào `$casts`.

- [ ] **Step 3: Viết test (fail)**

`tests/Feature/Manager/BranchPayrollTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\SalaryConfig;
use App\Models\User;
use App\Services\BranchPayroll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchPayrollTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private function salary(User $user, int $rate, ?int $probation = null, string $from = '2026-01-01'): void
    {
        SalaryConfig::create(['user_id' => $user->id, 'type' => 'hourly', 'rate' => $rate,
            'probation_rate' => $probation, 'effective_from' => $from]);
    }

    private function work(User $user, $branch, string $in, ?string $out): void
    {
        Attendance::create(['user_id' => $user->id, 'branch_id' => $branch->id,
            'shift_id' => $this->makeShift($branch)->id, 'check_in' => $in, 'check_out' => $out, 'method' => 'manual']);
    }

    private function payroll(User $user, int $month = 10): ?Payroll
    {
        return Payroll::where('user_id', $user->id)->ofMonth($month, 2026)->first();
    }

    public function test_hourly_sum_of_closed_shifts_in_own_branch(): void
    {
        $branch = $this->makeBranch();
        $other  = $this->makeBranch('B');
        $staff  = $this->makeUser('staff', $branch);
        $this->salary($staff, 25000);
        $this->work($staff, $branch, '2026-10-02 08:00', '2026-10-02 11:00');   // 3h
        $this->work($staff, $branch, '2026-10-03 08:00', '2026-10-03 12:30');   // 4.5h
        $this->work($staff, $branch, '2026-10-04 08:00', null);                 // chưa chấm ra
        $this->work($staff, $other, '2026-10-05 08:00', '2026-10-05 18:00');    // chi nhánh khác
        $this->work($staff, $branch, '2026-09-30 08:00', '2026-09-30 18:00');   // tháng trước

        app(BranchPayroll::class)->calculate($branch->id, 10, 2026);

        $p = $this->payroll($staff);
        $this->assertSame('7.50', $p->total_hours);
        $this->assertSame(2, $p->total_days);
        $this->assertSame(187500, $p->base_salary);
        $this->assertSame(187500, $p->total);
        $this->assertSame('draft', $p->status);
    }

    public function test_probation_rate_for_first_seven_days(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $staff->update(['started_at' => '2026-10-01']);
        $this->salary($staff, 25000, 20000);
        $this->work($staff, $branch, '2026-10-07 08:00', '2026-10-07 10:00');   // ngày thứ 7: 2h × 20k
        $this->work($staff, $branch, '2026-10-08 08:00', '2026-10-08 10:00');   // ngày thứ 8: 2h × 25k

        app(BranchPayroll::class)->calculate($branch->id, 10, 2026);

        $this->assertSame(90000, $this->payroll($staff)->base_salary);
    }

    public function test_no_start_date_means_official_rate(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('kitchen', $branch);
        $this->salary($staff, 22000, 18000);
        $this->work($staff, $branch, '2026-10-01 08:00', '2026-10-01 09:00');

        app(BranchPayroll::class)->calculate($branch->id, 10, 2026);

        $this->assertSame(22000, $this->payroll($staff)->base_salary);
    }

    public function test_rate_change_mid_month_applies_from_its_date(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $this->salary($staff, 20000, null, '2026-01-01');
        $this->salary($staff, 30000, null, '2026-10-15');
        $this->work($staff, $branch, '2026-10-14 08:00', '2026-10-14 09:00');   // 20k
        $this->work($staff, $branch, '2026-10-15 08:00', '2026-10-15 09:00');   // 30k

        app(BranchPayroll::class)->calculate($branch->id, 10, 2026);

        $this->assertSame(50000, $this->payroll($staff)->base_salary);
    }

    /** Review Focus #2 */
    public function test_overnight_shift_counts_in_check_in_month(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $this->salary($staff, 25000);
        $this->work($staff, $branch, '2026-10-31 22:00', '2026-11-01 02:00');

        app(BranchPayroll::class)->calculate($branch->id, 10, 2026);
        app(BranchPayroll::class)->calculate($branch->id, 11, 2026);

        $this->assertSame(100000, $this->payroll($staff, 10)->base_salary);
        $this->assertSame(0, $this->payroll($staff, 11)->base_salary);
    }

    public function test_recalculate_keeps_bonus_and_skips_confirmed(): void
    {
        $branch = $this->makeBranch();
        $draft  = $this->makeUser('staff', $branch);
        $locked = $this->makeUser('staff', $branch);
        $this->salary($draft, 10000);
        $this->salary($locked, 10000);
        $this->work($draft, $branch, '2026-10-01 08:00', '2026-10-01 09:00');
        $this->work($locked, $branch, '2026-10-01 08:00', '2026-10-01 09:00');

        $service = app(BranchPayroll::class);
        $service->calculate($branch->id, 10, 2026);
        $this->payroll($draft)->update(['bonus' => 5000, 'deduction' => 2000]);
        $this->payroll($locked)->update(['status' => 'confirmed']);

        $this->work($draft, $branch, '2026-10-02 08:00', '2026-10-02 09:00');
        $this->work($locked, $branch, '2026-10-02 08:00', '2026-10-02 09:00');
        $service->calculate($branch->id, 10, 2026);

        $p = $this->payroll($draft);
        $this->assertSame(20000, $p->base_salary);
        $this->assertSame(23000, $p->total);
        $this->assertSame(10000, $this->payroll($locked)->base_salary);
    }

    /** Review Focus #5 */
    public function test_locked_staff_still_paid_for_worked_hours_and_others_skipped(): void
    {
        $branch   = $this->makeBranch();
        $locked   = $this->makeUser('staff', $branch);
        $noConfig = $this->makeUser('staff', $branch);
        $manager  = $this->makeUser('manager', $branch);
        $this->salary($locked, 10000);
        $this->salary($manager, 10000);
        $this->work($locked, $branch, '2026-10-01 08:00', '2026-10-01 09:00');
        $locked->update(['is_active' => false]);

        app(BranchPayroll::class)->calculate($branch->id, 10, 2026);

        $this->assertSame(10000, $this->payroll($locked)->base_salary);
        $this->assertNull($this->payroll($noConfig));
        $this->assertNull($this->payroll($manager));
    }
}
```

- [ ] **Step 4: Chạy test, xác nhận fail**

Run: `$PHP artisan test --filter=BranchPayrollTest`
Expected: FAIL — `Class "App\Services\BranchPayroll" not found`.

- [ ] **Step 5: Viết service**

`app/Services/BranchPayroll.php`:

```php
<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\SalaryConfig;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BranchPayroll
{
    /**
     * Tính (lại) lương tháng cho staff/kitchen của chi nhánh.
     * Chỉ ghi đè dòng còn nháp; giữ thưởng/phạt đã nhập.
     */
    public function calculate(int $branchId, int $month, int $year): void
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::ofBranch($branchId)
            ->whereBetween('check_in', [$start, $end])
            ->whereNotNull('check_out')
            ->get()
            ->groupBy('user_id');

        $existing = Payroll::ofBranch($branchId)->ofMonth($month, $year)->get()->keyBy('user_id');

        // Người đang làm + người đã có giờ/dòng lương tháng này (kể cả đã bị khoá)
        $users = User::where('branch_id', $branchId)
            ->whereIn('role', ['staff', 'kitchen'])
            ->where(fn ($q) => $q->where('is_active', true)
                ->orWhereIn('id', $attendances->keys())
                ->orWhereIn('id', $existing->keys()))
            ->get();

        $configs = SalaryConfig::whereIn('user_id', $users->pluck('id'))
            ->whereDate('effective_from', '<=', $end->toDateString())
            ->orderBy('effective_from')->orderBy('id')
            ->get()
            ->groupBy('user_id');

        foreach ($users as $user) {
            $rows        = $attendances->get($user->id, collect());
            $userConfigs = $configs->get($user->id, collect());
            $payroll     = $existing->get($user->id);

            if (($rows->isEmpty() && $userConfigs->isEmpty()) || ($payroll && !$payroll->isDraft())) {
                continue;
            }

            $hours = 0.0;
            $base  = 0.0;
            foreach ($rows as $a) {
                $h      = $a->check_in->diffInMinutes($a->check_out) / 60;
                $hours += $h;
                $base  += $h * $this->rateAt($user, $userConfigs, $a->check_in);
            }

            $base      = (int) round($base);
            $bonus     = $payroll?->bonus ?? 0;
            $deduction = $payroll?->deduction ?? 0;

            Payroll::updateOrCreate(
                ['user_id' => $user->id, 'branch_id' => $branchId, 'month' => $month, 'year' => $year],
                [
                    'total_hours' => round($hours, 2),
                    'total_days'  => $rows->map(fn ($a) => $a->check_in->toDateString())->unique()->count(),
                    'base_salary' => $base,
                    'bonus'       => $bonus,
                    'deduction'   => $deduction,
                    'total'       => $base + $bonus - $deduction,
                    'status'      => 'draft',
                ]
            );
        }
    }

    /** Lương/giờ áp dụng cho một lượt: config mới nhất tính đến ngày đó, thử việc thì dùng mức thử việc */
    private function rateAt(User $user, Collection $configs, Carbon $at): int
    {
        $config = $configs->last(fn ($c) => $c->effective_from->toDateString() <= $at->toDateString());

        if (!$config) {
            return 0;
        }

        return $user->isOnProbation($at) && $config->probation_rate !== null
            ? (int) $config->probation_rate
            : (int) $config->rate;
    }
}
```

- [ ] **Step 6: Chạy test, xác nhận pass**

Run: `$PHP artisan test --filter=BranchPayrollTest`
Expected: PASS (7 tests).

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_10_05_000001_add_probation_to_salary.php app/Models/User.php app/Models/SalaryConfig.php app/Services/BranchPayroll.php tests/Feature/Manager/BranchPayrollTest.php
git commit -m "feat(manager): service BranchPayroll — lương theo giờ, thử việc 7 ngày"
```

---

### Task 2: Ngày bắt đầu + lương trong form nhân viên

**Files:**
- Modify: `app/Http/Controllers/Manager/StaffController.php`
- Modify: `resources/views/manager/staff/form.blade.php`, `resources/views/manager/staff/index.blade.php`
- Test: `tests/Feature/Manager/StaffManageTest.php`

**Interfaces:**
- Consumes: `User::latestSalary()`, `User::salaryConfigs()`, `User::probationEndsAt()`, `User::isOnProbation()` (Task 1).
- Produces: request fields `started_at` (Y-m-d), `probation_rate`, `salary_rate` trên `POST /manager/staff` và `PUT /manager/staff/{user}`.

- [ ] **Step 1: Sửa test**

Trong `StaffManageTest`:
- `validData()` thêm `'started_at' => '2026-10-01', 'probation_rate' => 20000, 'salary_rate' => 25000`.
- Trong `test_update_lock_and_reset_password`, dữ liệu `put` thêm `'started_at' => '2026-10-01', 'probation_rate' => 20000, 'salary_rate' => 25000`.
- Thêm `use App\Models\SalaryConfig;` và 2 test:

```php
    public function test_create_saves_start_date_and_salary(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))->post('/manager/staff', $this->validData());

        $user = User::where('email', 'bep1@zomzop.com')->first();
        $this->assertSame('2026-10-01', $user->started_at->toDateString());
        $config = $user->latestSalary;
        $this->assertSame(25000, (int) $config->rate);
        $this->assertSame(20000, (int) $config->probation_rate);
        $this->assertSame('hourly', $config->type);
        $this->assertSame(today()->toDateString(), $config->effective_from->toDateString());
    }

    public function test_update_creates_new_salary_only_when_changed(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->makeUser('staff', $branch);
        SalaryConfig::create(['user_id' => $staff->id, 'type' => 'hourly', 'rate' => 25000, 'probation_rate' => 20000, 'effective_from' => '2026-01-01']);
        $data = ['name' => $staff->name, 'email' => $staff->email, 'role' => 'staff', 'started_at' => '2026-01-01'];

        $this->actingAs($manager)->put("/manager/staff/{$staff->id}", $data + ['probation_rate' => 20000, 'salary_rate' => 25000]);
        $this->assertSame(1, SalaryConfig::where('user_id', $staff->id)->count());

        $this->actingAs($manager)->put("/manager/staff/{$staff->id}", $data + ['probation_rate' => 20000, 'salary_rate' => 30000]);
        $this->assertSame(2, SalaryConfig::where('user_id', $staff->id)->count());
        $this->assertSame(30000, (int) $staff->fresh()->latestSalary->rate);

        $this->actingAs($manager)->from("/manager/staff/{$staff->id}/edit")
            ->put("/manager/staff/{$staff->id}", $data + ['probation_rate' => 0, 'salary_rate' => 'abc'])
            ->assertSessionHasErrors(['probation_rate', 'salary_rate']);
    }

    public function test_index_shows_rate_and_probation_badge(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $staff->update(['started_at' => today()->subDays(2)]);
        SalaryConfig::create(['user_id' => $staff->id, 'type' => 'hourly', 'rate' => 25000, 'probation_rate' => 20000, 'effective_from' => today()]);

        $this->actingAs($this->makeUser('manager', $branch))->get('/manager/staff')
            ->assertSee('25.000đ/giờ')
            ->assertSee('Thử việc đến ' . today()->addDays(4)->format('d/m'));
    }
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test --filter=StaffManageTest`
Expected: FAIL ở 3 test mới (`started_at` null / không có `latestSalary` / không thấy chuỗi).

- [ ] **Step 3: Sửa controller**

`StaffController`:
- `MESSAGES` thêm:

```php
        'started_at.required'        => 'Vui lòng nhập ngày bắt đầu làm.',
        'started_at.date_format'     => 'Ngày bắt đầu không hợp lệ.',
        'started_at.before_or_equal' => 'Ngày bắt đầu không được quá 30 ngày tới.',
        'probation_rate.*'           => 'Lương thử việc/giờ phải là số nguyên từ 1 đến 999.999.999.',
        'salary_rate.*'              => 'Lương chính thức/giờ phải là số nguyên từ 1 đến 999.999.999.',
```

- `rules()` thêm:

```php
            'started_at'     => ['required', 'date_format:Y-m-d', 'before_or_equal:' . today()->addDays(30)->toDateString()],
            'probation_rate' => ['required', 'integer', 'min:1', 'max:999999999'],
            'salary_rate'    => ['required', 'integer', 'min:1', 'max:999999999'],
```

- `index()`: thêm `->with('latestSalary')` trước `->orderByDesc('is_active')`.
- `edit()`: `$user->load('latestSalary');` trước khi trả view.
- Thêm method và dùng trong `store`/`update`:

```php
    private const USER_FIELDS = ['name', 'email', 'phone', 'role', 'started_at'];

    /** Đổi lương thì lưu mức mới hiệu lực từ hôm nay; giữ nguyên lịch sử cũ */
    private function saveSalary(User $user, array $data): void
    {
        $current = $user->latestSalary()->first();

        if ($current && (int) $current->rate === (int) $data['salary_rate']
            && (int) $current->probation_rate === (int) $data['probation_rate']) {
            return;
        }

        $user->salaryConfigs()->create([
            'type'           => 'hourly',
            'rate'           => $data['salary_rate'],
            'probation_rate' => $data['probation_rate'],
            'effective_from' => today(),
        ]);
    }
```

`store`:

```php
        $user = User::create(Arr::only($data, [...self::USER_FIELDS, 'password']) + ['branch_id' => $this->branchId(), 'is_active' => true]);
        $this->saveSalary($user, $data);
```

`update`:

```php
        $user->update(Arr::only($data, self::USER_FIELDS));
        $this->saveSalary($user, $data);
```

Thêm `use Illuminate\Support\Arr;`.

- [ ] **Step 4: Sửa view**

`staff/form.blade.php`, chèn sau `<label>` Vai trò:

```blade
            <label class="block">Ngày bắt đầu làm
                <input type="date" name="started_at" value="{{ old('started_at', $user?->started_at?->toDateString() ?? today()->toDateString()) }}" class="{{ $input }} mt-1" required>
                <span class="text-xs text-slate-400">7 ngày đầu tính lương thử việc.</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="block">Lương thử việc/giờ
                    <input type="number" name="probation_rate" min="1" step="1" value="{{ old('probation_rate', $user?->latestSalary?->probation_rate) }}" class="{{ $input }} mt-1" required></label>
                <label class="block">Lương chính thức/giờ
                    <input type="number" name="salary_rate" min="1" step="1" value="{{ old('salary_rate', $user?->latestSalary?->rate) }}" class="{{ $input }} mt-1" required></label>
            </div>
            @if ($user) <p class="text-xs text-slate-400">Đổi lương sẽ áp dụng cho giờ làm từ hôm nay.</p> @endif
```

`staff/index.blade.php`: thêm cột `<th class="px-4 py-3">Lương/giờ</th>` sau "Vai trò" (đổi `colspan="6"` → `7`), và ô:

```blade
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $u->latestSalary ? number_format($u->latestSalary->rate, 0, ',', '.') . 'đ/giờ' : '—' }}
                            @if ($u->isOnProbation(today()))
                                <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Thử việc đến {{ $u->probationEndsAt()->format('d/m') }}</span>
                            @endif
                        </td>
```

- [ ] **Step 5: Chạy test, xác nhận pass**

Run: `$PHP artisan test --filter=StaffManageTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Manager/StaffController.php resources/views/manager/staff tests/Feature/Manager/StaffManageTest.php
git commit -m "feat(manager): đặt ngày bắt đầu, lương thử việc/chính thức cho nhân viên"
```

---

### Task 3: Trang bảng lương

**Files:**
- Create: `app/Http/Controllers/Manager/PayrollController.php`
- Create: `resources/views/manager/payrolls/index.blade.php`
- Modify: `routes/manager.php`, `resources/views/layouts/manager.blade.php`
- Test: `tests/Feature/Manager/PayrollPageTest.php`

**Interfaces:**
- Consumes: `BranchPayroll::calculate()` (Task 1), `User::latestSalary`, `User::probationEndsAt()`.
- Produces: routes `manager.payrolls.index` (GET `/payrolls?month=Y-m`), `manager.payrolls.recalculate` (POST `/payrolls/recalculate`), `manager.payrolls.update` (PUT `/payrolls/{payroll}`), `manager.payrolls.confirm` (PATCH `/payrolls/{payroll}/confirm`), `manager.payrolls.pay` (PATCH `/payrolls/{payroll}/pay`). Private `PayrollController::month(Request): Carbon` và `rows(int, Carbon): Collection` — Task 5 dùng cho export.

- [ ] **Step 1: Viết test (fail)**

`tests/Feature/Manager/PayrollPageTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\SalaryConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function setupStaff($branch, string $name = 'Nguyễn Thu Ngân')
    {
        $staff = $this->makeUser('staff', $branch);
        $staff->update(['name' => $name]);
        SalaryConfig::create(['user_id' => $staff->id, 'type' => 'hourly', 'rate' => 25000, 'effective_from' => '2026-01-01']);
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $this->makeShift($branch)->id,
            'check_in' => '2026-10-02 08:00', 'check_out' => '2026-10-02 12:00', 'method' => 'manual']);

        return $staff;
    }

    public function test_first_visit_calculates_and_lists_own_branch(): void
    {
        $mine  = $this->makeBranch();
        $other = $this->makeBranch('B');
        $this->setupStaff($mine);
        $this->setupStaff($other, 'Người Chi Nhánh Khác');

        $this->actingAs($this->makeUser('manager', $mine))
            ->get('/manager/payrolls?month=2026-10')
            ->assertOk()
            ->assertSee('Nguyễn Thu Ngân')
            ->assertSee('100.000đ')
            ->assertDontSee('Người Chi Nhánh Khác');
    }

    public function test_bad_month_falls_back_to_current(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/payrolls?month=2026-99')
            ->assertOk()
            ->assertSee(now()->format('m/Y'));
    }

    public function test_bonus_confirm_and_pay_flow(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->setupStaff($branch);
        $this->actingAs($manager)->get('/manager/payrolls?month=2026-10');
        $p = Payroll::where('user_id', $staff->id)->first();

        $this->actingAs($manager)->put("/manager/payrolls/{$p->id}", ['bonus' => 50000, 'deduction' => 10000])
            ->assertSessionHas('success');
        $this->assertSame(140000, $p->fresh()->total);

        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/pay")->assertSessionHasErrors('payroll');
        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/confirm")->assertSessionHas('success');
        $this->assertSame('confirmed', $p->fresh()->status);

        $this->actingAs($manager)->put("/manager/payrolls/{$p->id}", ['bonus' => 1, 'deduction' => 0])->assertSessionHasErrors('payroll');
        $this->assertSame(50000, $p->fresh()->bonus);

        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/pay")->assertSessionHas('success');
        $this->assertSame('paid', $p->fresh()->status);
    }

    /** Review Focus #1 */
    public function test_deduction_cannot_make_total_negative(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->setupStaff($branch);
        $this->actingAs($manager)->get('/manager/payrolls?month=2026-10');
        $p = Payroll::where('user_id', $staff->id)->first();

        $this->actingAs($manager)->from('/manager/payrolls?month=2026-10')
            ->put("/manager/payrolls/{$p->id}", ['bonus' => 0, 'deduction' => 100001])
            ->assertSessionHasErrors('deduction');
        $this->assertSame(0, $p->fresh()->deduction);

        $this->actingAs($manager)->put("/manager/payrolls/{$p->id}", ['bonus' => -1, 'deduction' => 'x'])
            ->assertSessionHasErrors(['bonus', 'deduction']);
    }

    public function test_recalculate_picks_up_new_hours(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->setupStaff($branch);
        $this->actingAs($manager)->get('/manager/payrolls?month=2026-10');
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $this->makeShift($branch)->id,
            'check_in' => '2026-10-03 08:00', 'check_out' => '2026-10-03 10:00', 'method' => 'manual']);

        $this->actingAs($manager)->post('/manager/payrolls/recalculate', ['month' => '2026-10'])
            ->assertRedirect(route('manager.payrolls.index', ['month' => '2026-10']));
        $this->assertSame(150000, Payroll::where('user_id', $staff->id)->value('base_salary'));
    }

    /** Review Focus #3 */
    public function test_cannot_touch_other_branch_payroll(): void
    {
        $other = $this->makeBranch('B');
        $staff = $this->setupStaff($other);
        $p = Payroll::create(['user_id' => $staff->id, 'branch_id' => $other->id, 'month' => 10, 'year' => 2026]);
        $manager = $this->makeUser('manager', $this->makeBranch('A'));

        $this->actingAs($manager)->put("/manager/payrolls/{$p->id}", ['bonus' => 1, 'deduction' => 0])->assertNotFound();
        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/confirm")->assertNotFound();
        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/pay")->assertNotFound();
        $this->assertSame('draft', $p->fresh()->status);
    }
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test --filter=PayrollPageTest`
Expected: FAIL — 404 vì chưa có route.

- [ ] **Step 3: Controller**

`app/Http/Controllers/Manager/PayrollController.php`:

```php
<?php

namespace App\Http\Controllers\Manager;

use App\Models\Payroll;
use App\Services\BranchPayroll;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class PayrollController extends ManagerController
{
    public const STATUS = ['draft' => 'Nháp', 'confirmed' => 'Đã chốt', 'paid' => 'Đã trả'];

    /** Tháng đang xem (ngày 1); sai định dạng → tháng hiện tại */
    private function month(Request $request): Carbon
    {
        $month = Validator::make($request->only('month'), ['month' => ['nullable', 'date_format:Y-m']])->valid()['month'] ?? null;

        return $month ? Carbon::parse("{$month}-01") : today()->startOfMonth();
    }

    private function rows(int $branchId, Carbon $month): Collection
    {
        return Payroll::ofBranch($branchId)->ofMonth($month->month, $month->year)
            ->with('user.latestSalary')
            ->get()
            ->sortBy('user.name')
            ->values();
    }

    private function ensureOwn(Payroll $payroll): void
    {
        abort_if((int) $payroll->branch_id !== $this->branchId(), 404);
    }

    public function index(Request $request, BranchPayroll $service)
    {
        $branchId = $this->branchId();
        $month    = $this->month($request);

        if (!Payroll::ofBranch($branchId)->ofMonth($month->month, $month->year)->exists()) {
            $service->calculate($branchId, $month->month, $month->year);
        }

        return view('manager.payrolls.index', ['month' => $month, 'payrolls' => $this->rows($branchId, $month), 'status' => self::STATUS]);
    }

    public function recalculate(Request $request, BranchPayroll $service)
    {
        $month = $this->month($request);
        $service->calculate($this->branchId(), $month->month, $month->year);

        return redirect()->route('manager.payrolls.index', ['month' => $month->format('Y-m')])
            ->with('success', 'Đã tính lại lương tháng ' . $month->format('m/Y') . ' (các dòng còn nháp).');
    }

    public function update(Request $request, Payroll $payroll)
    {
        $this->ensureOwn($payroll);

        if (!$payroll->isDraft()) {
            return back()->withErrors(['payroll' => 'Chỉ sửa được dòng lương còn nháp.']);
        }

        $data = $request->validate([
            'bonus'     => ['required', 'integer', 'min:0', 'max:999999999'],
            'deduction' => ['required', 'integer', 'min:0', 'max:999999999'],
        ], [
            'bonus.*'     => 'Thưởng phải là số nguyên từ 0 đến 999.999.999.',
            'deduction.*' => 'Phạt phải là số nguyên từ 0 đến 999.999.999.',
        ]);

        $total = $payroll->base_salary + (int) $data['bonus'] - (int) $data['deduction'];
        if ($total < 0) {
            return back()->withErrors(['deduction' => 'Phạt không được lớn hơn lương cơ bản + thưởng.']);
        }

        $payroll->update(['bonus' => $data['bonus'], 'deduction' => $data['deduction'], 'total' => $total]);

        return back()->with('success', "Đã cập nhật lương {$payroll->user->name}.");
    }

    public function confirm(Payroll $payroll)
    {
        return $this->move($payroll, 'draft', 'confirmed', 'Đã chốt lương');
    }

    public function pay(Payroll $payroll)
    {
        return $this->move($payroll, 'confirmed', 'paid', 'Đã đánh dấu trả lương');
    }

    private function move(Payroll $payroll, string $from, string $to, string $message)
    {
        $this->ensureOwn($payroll);

        if ($payroll->status !== $from) {
            return back()->withErrors(['payroll' => 'Dòng lương đang "' . self::STATUS[$payroll->status] . '", không thể chuyển sang "' . self::STATUS[$to] . '".']);
        }

        $payroll->update(['status' => $to]);

        return back()->with('success', "{$message} {$payroll->user->name}.");
    }
}
```

- [ ] **Step 4: Route + sidebar**

`routes/manager.php`: thêm `use App\Http\Controllers\Manager\PayrollController;` và sau các route attendances:

```php
        Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::post('/payrolls/recalculate', [PayrollController::class, 'recalculate'])->name('payrolls.recalculate');
        Route::put('/payrolls/{payroll}', [PayrollController::class, 'update'])->name('payrolls.update');
        Route::patch('/payrolls/{payroll}/confirm', [PayrollController::class, 'confirm'])->name('payrolls.confirm');
        Route::patch('/payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');
```

`layouts/manager.blade.php`: `$_icons` thêm `'money' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M7 9.5v5M17 9.5v5"/>',`; nhóm `'Nhân sự'` thêm dòng cuối:

```php
                ['route' => 'manager.payrolls.index', 'match' => 'manager.payrolls.*', 'label' => 'Bảng lương', 'icon' => 'money'],
```

- [ ] **Step 5: View**

`resources/views/manager/payrolls/index.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Bảng lương')

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $input = 'w-24 px-2 py-1 rounded-lg border border-slate-200 text-sm text-right';
        $monthEnd = $month->copy()->endOfMonth();
        $badge = ['draft' => 'bg-slate-100 text-slate-600', 'confirmed' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-emerald-100 text-emerald-700'];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold">Bảng lương</h1>
            <p class="text-sm text-slate-500">Tháng {{ $month->format('m/Y') }} · tính theo giờ đã chấm ra</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="px-3 py-2 rounded-lg border border-slate-200">
                <button class="px-3 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
            </form>
            <form method="POST" action="{{ route('manager.payrolls.recalculate') }}">
                @csrf <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <button class="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Tính lại</button>
            </form>
        </div>
    </div>

    @error('payroll') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
    @error('bonus') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
    @error('deduction') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Lương/giờ</th>
                    <th class="px-4 py-3 text-right">Giờ</th><th class="px-4 py-3 text-right">Ngày</th>
                    <th class="px-4 py-3 text-right">Cơ bản</th><th class="px-4 py-3 text-right">Thưởng / Phạt</th>
                    <th class="px-4 py-3 text-right">Tổng</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payrolls as $p)
                    @php
                        $u = $p->user;
                        $probEnd = $u->probationEndsAt();
                        $probInMonth = $probEnd && $probEnd->gte($month) && $u->started_at->lte($monthEnd);
                    @endphp
                    <tr class="border-b border-slate-50 align-middle">
                        <td class="px-4 py-3">
                            <span class="font-semibold">{{ $u->name }}</span>
                            <span class="text-xs text-slate-400">{{ \App\Http\Controllers\Manager\StaffController::ROLES[$u->role] ?? $u->role }}</span>
                            @if ($probInMonth)
                                <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Thử việc đến {{ $probEnd->format('d/m') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $u->latestSalary ? $money($u->latestSalary->rate) : '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $p->total_hours, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ $p->total_days }}</td>
                        <td class="px-4 py-3 text-right">{{ $money($p->base_salary) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if ($p->isDraft())
                                <form method="POST" action="{{ route('manager.payrolls.update', $p) }}" class="flex justify-end gap-1">
                                    @csrf @method('PUT')
                                    <input type="number" name="bonus" min="0" step="1" value="{{ $p->bonus }}" class="{{ $input }}" title="Thưởng">
                                    <input type="number" name="deduction" min="0" step="1" value="{{ $p->deduction }}" class="{{ $input }}" title="Phạt">
                                    <button class="px-2 rounded-lg bg-slate-100 hover:bg-slate-200 cursor-pointer">Lưu</button>
                                </form>
                            @else
                                +{{ $money($p->bonus) }} / −{{ $money($p->deduction) }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $money($p->total) }}</td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full {{ $badge[$p->status] }}">{{ $status[$p->status] }}</span></td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if ($p->isDraft())
                                <form method="POST" action="{{ route('manager.payrolls.confirm', $p) }}">@csrf @method('PATCH')
                                    <button class="text-red-500 hover:underline cursor-pointer">Chốt</button></form>
                            @elseif ($p->isConfirmed())
                                <form method="POST" action="{{ route('manager.payrolls.pay', $p) }}">@csrf @method('PATCH')
                                    <button class="text-emerald-600 hover:underline cursor-pointer">Đã trả</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-slate-400">Tháng này chưa có nhân viên nào có lương.</td></tr>
                @endforelse
            </tbody>
            @if ($payrolls->isNotEmpty())
                <tfoot class="font-semibold">
                    <tr>
                        <td class="px-4 py-3" colspan="2">Tổng cộng</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $payrolls->sum('total_hours'), 2, ',', '.') }}</td>
                        <td></td>
                        <td class="px-4 py-3 text-right">{{ $money($payrolls->sum('base_salary')) }}</td>
                        <td></td>
                        <td class="px-4 py-3 text-right">{{ $money($payrolls->sum('total')) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
```

- [ ] **Step 6: Chạy test, xác nhận pass**

Run: `$PHP artisan test --filter=PayrollPageTest`
Expected: PASS (6 tests).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Manager/PayrollController.php resources/views/manager/payrolls routes/manager.php resources/views/layouts/manager.blade.php tests/Feature/Manager/PayrollPageTest.php
git commit -m "feat(manager): trang bảng lương — thưởng/phạt, chốt, đã trả, tính lại"
```

---

### Task 4: Trả lời đánh giá

**Files:**
- Create: `database/migrations/2026_10_05_000002_add_reply_to_reviews_table.php`
- Modify: `app/Models/Review.php`, `app/Http/Controllers/Manager/ReviewController.php`, `routes/manager.php`, `resources/views/manager/reviews/index.blade.php`
- Test: `tests/Feature/Manager/ReviewPageTest.php`

**Interfaces:**
- Produces: route `manager.reviews.reply` (PUT `/manager/reviews/{review}/reply`, field `reply`); `Review::$reply`, `Review::$replied_at` (cast datetime).

- [ ] **Step 1: Viết test (fail)** — thêm vào `ReviewPageTest`:

```php
    public function test_reply_and_edit_reply(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $review  = $this->review($branch, 2, 'Giao hơi chậm');

        $this->actingAs($manager)->put("/manager/reviews/{$review->id}/reply", ['reply' => 'Xin lỗi anh/chị, lần sau tụi em giao nhanh hơn.'])
            ->assertSessionHas('success');
        $this->assertSame('Xin lỗi anh/chị, lần sau tụi em giao nhanh hơn.', $review->fresh()->reply);
        $this->assertNotNull($review->fresh()->replied_at);

        $this->actingAs($manager)->put("/manager/reviews/{$review->id}/reply", ['reply' => 'Đã sửa lời đáp']);
        $this->actingAs($manager)->get('/manager/reviews')->assertSee('Đã sửa lời đáp');

        $this->actingAs($manager)->put("/manager/reviews/{$review->id}/reply", ['reply' => ''])->assertSessionHasErrors('reply');
        $this->actingAs($manager)->put("/manager/reviews/{$review->id}/reply", ['reply' => str_repeat('a', 1001)])->assertSessionHasErrors('reply');
        $this->assertSame('Đã sửa lời đáp', $review->fresh()->reply);
    }

    public function test_cannot_reply_other_branch_review(): void
    {
        $review = $this->review($this->makeBranch('B'), 1, 'Chê');

        $this->actingAs($this->makeUser('manager', $this->makeBranch('A')))
            ->put("/manager/reviews/{$review->id}/reply", ['reply' => 'Hack'])
            ->assertNotFound();
        $this->assertNull($review->fresh()->reply);
    }
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test --filter=ReviewPageTest`
Expected: FAIL — 404/405 vì chưa có route.

- [ ] **Step 3: Migration + model**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('comment')->comment('Manager trả lời');
            $table->timestamp('replied_at')->nullable()->after('reply');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', fn (Blueprint $table) => $table->dropColumn(['reply', 'replied_at']));
    }
};
```

`Review`: `$fillable` thêm `'reply', 'replied_at'`; `$casts` thêm `'replied_at' => 'datetime'`.

- [ ] **Step 4: Controller + route**

`ReviewController` thêm:

```php
    public function reply(Request $request, Review $review)
    {
        abort_if((int) $review->branch_id !== $this->branchId(), 404);

        $data = $request->validate(['reply' => ['required', 'string', 'max:1000']], [
            'reply.required' => 'Vui lòng nhập nội dung trả lời.',
            'reply.max'      => 'Trả lời tối đa 1000 ký tự.',
        ]);

        $review->update(['reply' => $data['reply'], 'replied_at' => now()]);

        return back()->with('success', 'Đã lưu trả lời.');
    }
```

`routes/manager.php`: `Route::put('/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');`

- [ ] **Step 5: View** — trong `reviews/index.blade.php`, sau dòng `@if ($r->delivery_rating) ...`:

```blade
                @if ($r->reply)
                    <div class="mt-3 pl-3 border-l-2 border-red-200 text-slate-600">
                        <p class="text-xs text-slate-400">Chi nhánh trả lời · {{ $r->replied_at?->format('H:i d/m/Y') }}</p>
                        <p>{{ $r->reply }}</p>
                    </div>
                @endif
                <details class="mt-2" @if ($errors->has('reply') && old('review_id') == $r->id) open @endif>
                    <summary class="text-xs text-red-500 cursor-pointer">{{ $r->reply ? 'Sửa trả lời' : 'Trả lời' }}</summary>
                    <form method="POST" action="{{ route('manager.reviews.reply', $r) }}" class="mt-2 space-y-2">
                        @csrf @method('PUT')
                        <input type="hidden" name="review_id" value="{{ $r->id }}">
                        <textarea name="reply" rows="2" maxlength="1000" required class="w-full px-3 py-2 rounded-lg border border-slate-200">{{ old('review_id') == $r->id ? old('reply') : $r->reply }}</textarea>
                        @if (old('review_id') == $r->id) @error('reply') <p class="text-xs text-red-600">{{ $message }}</p> @enderror @endif
                        <button class="px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-semibold cursor-pointer">Lưu trả lời</button>
                    </form>
                </details>
```

- [ ] **Step 6: Chạy test, xác nhận pass**

Run: `$PHP artisan test --filter=ReviewPageTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_10_05_000002_add_reply_to_reviews_table.php app/Models/Review.php app/Http/Controllers/Manager/ReviewController.php routes/manager.php resources/views/manager/reviews/index.blade.php tests/Feature/Manager/ReviewPageTest.php
git commit -m "feat(manager): trả lời đánh giá của khách"
```

---

### Task 5: Xuất Excel (.xlsx) cho 4 trang

**Files:**
- Modify: `composer.json`, `composer.lock` (thêm `openspout/openspout`)
- Create: `app/Support/XlsxExport.php`
- Modify: `ReportController`, `PayrollController`, `AttendanceController`, `OrderController`, `routes/manager.php`
- Modify views: `reports/index`, `payrolls/index`, `attendances/index`, `orders/index` (nút "Xuất Excel")
- Test: `tests/Feature/Manager/ExportTest.php`

**Interfaces:**
- Consumes: `PayrollController::month()`, `rows()`, `STATUS` (Task 3); `BranchReport` methods; `OrderStatusService::LABELS`.
- Produces: `XlsxExport::download(string $filename, array $headings, iterable $rows): StreamedResponse`, `XlsxExport::bold(array $values): Row`; routes `manager.reports.export`, `manager.payrolls.export`, `manager.attendances.export`, `manager.orders.export`.

- [ ] **Step 1: Cài thư viện**

Run: `$PHP /e/laragon/bin/composer/composer.phar require openspout/openspout:^4`
Expected: cài thành công. Kiểm tra API style: `grep -n "function setFontBold" vendor/openspout/openspout/src/Common/Entity/Style/Style.php` phải có kết quả (v4).

- [ ] **Step 2: Viết test (fail)**

`tests/Feature/Manager/ExportTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\Attendance;
use App\Models\SalaryConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    /** Đọc file .xlsx trả về thành mảng các dòng */
    private function rows(TestResponse $response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());

        $reader = new Reader();
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
        }
        $reader->close();
        unlink($path);

        return $rows;
    }

    private function flat(array $rows): string
    {
        return implode('|', array_map(fn ($r) => implode('|', $r), $rows));
    }

    public function test_orders_export_respects_filter_and_branch(): void
    {
        $mine  = $this->makeBranch();
        $other = $this->makeBranch('B');
        $this->makeOrder($mine, ['order_code' => 'ZZMINE01', 'status' => 'completed']);
        $this->makeOrder($mine, ['order_code' => 'ZZMINE02', 'status' => 'pending']);
        $this->makeOrder($other, ['order_code' => 'ZZOTHER1', 'status' => 'completed']);

        $res = $this->actingAs($this->makeUser('manager', $mine))->get('/manager/orders/export?status=completed');
        $res->assertOk()->assertDownload('don-hang-' . today()->toDateString() . '.xlsx');
        $rows = $this->rows($res);

        $this->assertSame('Mã đơn', $rows[0][0]);
        $this->assertStringContainsString('ZZMINE01', $this->flat($rows));
        $this->assertStringNotContainsString('ZZMINE02', $this->flat($rows));
        $this->assertStringNotContainsString('ZZOTHER1', $this->flat($rows));
    }

    public function test_payroll_export(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $staff->update(['name' => 'Trần Bếp']);
        SalaryConfig::create(['user_id' => $staff->id, 'type' => 'hourly', 'rate' => 25000, 'effective_from' => '2026-01-01']);
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $this->makeShift($branch)->id,
            'check_in' => '2026-10-02 08:00', 'check_out' => '2026-10-02 12:00', 'method' => 'manual']);

        $res = $this->actingAs($this->makeUser('manager', $branch))->get('/manager/payrolls/export?month=2026-10');
        $res->assertOk()->assertDownload('bang-luong-2026-10.xlsx');
        $rows = $this->rows($res);

        $this->assertSame('Nhân viên', $rows[0][0]);
        $this->assertSame('Trần Bếp', $rows[1][0]);
        $this->assertEquals(100000, $rows[1][7]);   // cột Tổng là số
    }

    public function test_attendance_export(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $staff->update(['name' => 'Lê Thu Ngân']);
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $this->makeShift($branch)->id,
            'check_in' => '2026-10-02 08:00', 'check_out' => '2026-10-02 12:00', 'method' => 'manual']);

        $res = $this->actingAs($this->makeUser('manager', $branch))->get('/manager/attendances/export?date=2026-10-02');
        $res->assertOk()->assertDownload('cham-cong-2026-10-02.xlsx');
        $rows = $this->rows($res);

        $this->assertSame('Nhân viên', $rows[0][0]);
        $this->assertSame('Lê Thu Ngân', $rows[1][0]);
    }

    public function test_report_export(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 120000]);   // tạo hôm nay
        $from = today()->subDays(2)->toDateString();
        $to   = today()->toDateString();

        $res = $this->actingAs($this->makeUser('manager', $branch))->get("/manager/reports/export?from={$from}&to={$to}");
        $res->assertOk()->assertDownload("bao-cao-{$from}-{$to}.xlsx");
        $flat = $this->flat($this->rows($res));

        $this->assertStringContainsString('Doanh thu', $flat);
        $this->assertStringContainsString('120000', $flat);
        $this->assertStringContainsString(today()->format('d/m/Y'), $flat);
    }
}
```

- [ ] **Step 3: Chạy test, xác nhận fail**

Run: `$PHP artisan test --filter=ExportTest`
Expected: FAIL — 404 (chưa có route export).

- [ ] **Step 4: Helper `XlsxExport`**

`app/Support/XlsxExport.php`:

```php
<?php

namespace App\Support;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxExport
{
    /** Một sheet: dòng tiêu đề in đậm, rồi từng dòng (mảng giá trị hoặc Row có sẵn style) */
    public static function download(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $writer->addRow(self::bold($headings));
            foreach ($rows as $row) {
                $writer->addRow($row instanceof Row ? $row : Row::fromValues($row));
            }
            $writer->close();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public static function bold(array $values): Row
    {
        return Row::fromValues($values, (new Style())->setFontBold());
    }
}
```

- [ ] **Step 5: Export đơn hàng** — `OrderController`: tách bộ lọc ra method dùng chung.

```php
    /** @return array{0: \Illuminate\Database\Eloquent\Builder, 1: array, 2: \Illuminate\Validation\Validator} */
    private function filtered(Request $request): array
    {
        // Không dùng $request->validate(): lọc sai sẽ bị redirect về trang trước (thường là Tổng quan).
        // Ở lại trang Đơn hàng, báo lỗi và chỉ áp dụng các bộ lọc hợp lệ.
        $validator = /* giữ nguyên Validator::make(...) hiện có */;
        $filters = $validator->valid();

        $query = Order::ofBranch($this->branchId())
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->ofStatus($status))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->when(trim($filters['q'] ?? ''), function ($q, $search) {
                $q->where(fn ($w) => $w->where('order_code', 'like', "%{$search}%")
                                      ->orWhere('pickup_code', $search));
            })
            ->latest();

        return [$query, $filters, $validator];
    }

    public function index(Request $request)
    {
        [$query, $filters, $validator] = $this->filtered($request);

        $orders = $query->with('user')->withCount('items')->paginate(15)->withQueryString();

        return view('manager.orders.index', [
            'orders'  => $orders,
            'filters' => $filters,
            'labels'  => OrderStatusService::LABELS,
        ])->withErrors($validator);
    }

    public function export(Request $request)
    {
        [$query] = $this->filtered($request);

        $rows = $query->with('user')->lazy()->map(fn (Order $o) => [
            $o->order_code,
            $o->created_at->format('d/m/Y H:i'),
            $o->user?->name,
            ReportController::TYPE_LABELS[$o->type] ?? $o->type,
            OrderStatusService::LABELS[$o->status] ?? $o->status,
            ReportController::PAY_LABELS[$o->payment_method] ?? $o->payment_method,
            $o->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán',
            (int) $o->total,
        ]);

        return XlsxExport::download('don-hang-' . today()->toDateString() . '.xlsx',
            ['Mã đơn', 'Thời gian', 'Khách', 'Hình thức', 'Trạng thái', 'Thanh toán', 'TT thanh toán', 'Tổng tiền'], $rows);
    }
```

(Khi tách, chép nguyên khối `Validator::make([...], [...], [...])` hiện có vào `filtered()`; thêm `use App\Support\XlsxExport;`.)

- [ ] **Step 6: Export báo cáo** — `ReportController`: thêm hằng nhãn, tách khoảng ngày.

```php
    public const TYPE_LABELS = ['takeaway' => 'Mang đi', 'delivery' => 'Giao hàng'];
    public const PAY_LABELS  = ['cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPay'];

    /** @return array{0: Carbon, 1: Carbon, 2: \Illuminate\Validation\Validator} khoảng sai → khoảng mặc định */
    private function range(Request $request): array
    {
        // chuyển nguyên phần thân index() hiện có từ $defaultFrom ... đến hết khối if ($validator->fails()) vào đây
        return [$from, $to, $validator];
    }

    public function index(Request $request, BranchReport $report)
    {
        $branchId = $this->branchId();
        [$from, $to, $validator] = $this->range($request);

        return view('manager.reports.index', [ /* giữ nguyên mảng hiện có */ ])->withErrors($validator);
    }

    public function export(Request $request, BranchReport $report)
    {
        $branchId = $this->branchId();
        [$from, $to] = $this->range($request);
        $s = $report->summary($branchId, $from, $to);

        $rows = [
            ['Doanh thu', $s['revenue']], ['Số đơn', $s['orders']], ['Hoàn thành', $s['completed']],
            ['Đã huỷ', $s['cancelled']], ['Giá trị TB/đơn', $s['avg_order']], ['Tỉ lệ huỷ (%)', $s['cancel_rate']],
            [], XlsxExport::bold(['Ngày', 'Số đơn', 'Doanh thu']),
        ];
        foreach ($report->daily($branchId, $from, $to) as $day => $d) {
            $rows[] = [Carbon::parse($day)->format('d/m/Y'), $d['orders'], $d['revenue']];
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Hình thức', 'Số đơn', 'Doanh thu']);
        foreach ($report->byType($branchId, $from, $to) as $k => $d) {
            $rows[] = [self::TYPE_LABELS[$k], $d['orders'], $d['revenue']];
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Thanh toán', 'Số đơn', 'Doanh thu']);
        foreach ($report->byPayment($branchId, $from, $to) as $k => $d) {
            $rows[] = [self::PAY_LABELS[$k], $d['orders'], $d['revenue']];
        }
        $rows[] = [];
        $rows[] = XlsxExport::bold(['Món bán chạy', 'Số lượng', 'Doanh thu']);
        foreach ($report->topItems($branchId, $from, $to) as $item) {
            $rows[] = [$item->name, (int) $item->qty, (int) $item->revenue];
        }

        return XlsxExport::download("bao-cao-{$from->toDateString()}-{$to->toDateString()}.xlsx",
            ['Báo cáo doanh thu', $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y')], $rows);
    }
```

Trong `reports/index.blade.php`, đổi hai dòng `$typeLabel = ...`, `$payLabel = ...` thành dùng hằng:
`$typeLabel = \App\Http\Controllers\Manager\ReportController::TYPE_LABELS;` và `$payLabel = \App\Http\Controllers\Manager\ReportController::PAY_LABELS;`.

- [ ] **Step 7: Export chấm công** — `AttendanceController`: tách ngày + query.

```php
    private function date(Request $request): string
    {
        return Validator::make($request->only('date'), ['date' => ['nullable', 'date_format:Y-m-d']])->valid()['date']
            ?? today()->toDateString();
    }

    private function attendancesOn(int $branchId, string $date)
    {
        return Attendance::ofBranch($branchId)
            ->with(['user', 'shift'])
            // Kèm cả lượt chưa chấm ra của ngày trước, để manager thấy và đóng được
            ->where(fn ($q) => $q->whereDate('check_in', $date)
                ->orWhere(fn ($q) => $q->whereNull('check_out')->whereDate('check_in', '<', $date)))
            ->orderBy('check_in')
            ->get();
    }

    public function export(Request $request)
    {
        $date = $this->date($request);

        $rows = $this->attendancesOn($this->branchId(), $date)->map(fn (Attendance $a) => [
            $a->user?->name,
            $a->shift?->name,
            $a->check_in->format('d/m/Y H:i'),
            $a->check_out?->format('d/m/Y H:i'),
            $a->check_out ? $a->working_hours : null,
            $a->method === 'face' ? 'Khuôn mặt' : 'Thủ công',
            $a->note,
        ]);

        return XlsxExport::download("cham-cong-{$date}.xlsx", ['Nhân viên', 'Ca', 'Giờ vào', 'Giờ ra', 'Số giờ', 'Cách chấm', 'Ghi chú'], $rows);
    }
```

`index()` đổi thành dùng `$date = $this->date($request);` và `'attendances' => $this->attendancesOn($branchId, $date)`.

- [ ] **Step 8: Export bảng lương** — `PayrollController`: tách bước "tháng chưa có dòng thì tính" để `index()` và `export()` dùng chung (export gọi thẳng cũng ra số liệu).

```php
    private function ensureCalculated(int $branchId, Carbon $month): void
    {
        if (!Payroll::ofBranch($branchId)->ofMonth($month->month, $month->year)->exists()) {
            app(BranchPayroll::class)->calculate($branchId, $month->month, $month->year);
        }
    }
```

`index()` thay khối `if (!Payroll::...exists()) { $service->calculate(...); }` bằng `$this->ensureCalculated($branchId, $month);` (bỏ tham số `BranchPayroll $service` khỏi `index`). Thêm:

```php
    public function export(Request $request)
    {
        $branchId = $this->branchId();
        $month    = $this->month($request);
        $this->ensureCalculated($branchId, $month);

        $rows = $this->rows($branchId, $month)->map(fn (Payroll $p) => [
            $p->user->name,
            StaffController::ROLES[$p->user->role] ?? $p->user->role,
            (float) $p->total_hours,
            $p->total_days,
            $p->base_salary,
            $p->bonus,
            $p->deduction,
            $p->total,
            self::STATUS[$p->status],
        ]);

        return XlsxExport::download('bang-luong-' . $month->format('Y-m') . '.xlsx',
            ['Nhân viên', 'Vai trò', 'Số giờ', 'Ngày công', 'Lương cơ bản', 'Thưởng', 'Phạt', 'Tổng', 'Trạng thái'], $rows);
    }
```

Thêm `use App\Support\XlsxExport;` vào cả 4 controller.

- [ ] **Step 9: Route + nút**

`routes/manager.php` — đặt route export **trước** route có `{param}` cùng prefix:

```php
        Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');          // trước /orders/{order}
        Route::get('/attendances/export', [AttendanceController::class, 'export'])->name('attendances.export');
        Route::get('/payrolls/export', [PayrollController::class, 'export'])->name('payrolls.export');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
```

Nút trên mỗi view (class giống nhau): `px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-sm`, nội dung "Xuất Excel":
- `orders/index`: cạnh `<h1>Đơn hàng</h1>` — bọc `<h1>` và nút trong `<div class="flex items-center justify-between mb-4">` (bỏ `mb-4` ở `h1`), `href="{{ route('manager.orders.export', request()->only(['status', 'date', 'q'])) }}"`.
- `attendances/index`: trong form GET đầu trang, sau nút "Xem": `<a href="{{ route('manager.attendances.export', ['date' => $date]) }}" ...>`.
- `reports/index`: sau nút "Xem": `<a href="{{ route('manager.reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" ...>`.
- `payrolls/index`: trước form "Tính lại": `<a href="{{ route('manager.payrolls.export', ['month' => $month->format('Y-m')]) }}" ...>`.

- [ ] **Step 10: Chạy test, xác nhận pass**

Run: `$PHP artisan test --filter="ExportTest|OrderListTest|AttendanceManageTest|ReportPageTest|PayrollPageTest"`
Expected: PASS (refactor không làm vỡ test cũ).

- [ ] **Step 11: Commit**

```bash
git add composer.json composer.lock app/Support/XlsxExport.php app/Http/Controllers/Manager routes/manager.php resources/views/manager tests/Feature/Manager/ExportTest.php
git commit -m "feat(manager): xuất Excel báo cáo, bảng lương, chấm công, đơn hàng"
```

---

### Task 6: Seeder, README, kiểm tra toàn bộ

**Files:**
- Modify: `database/seeders/SalaryConfigSeeder.php`, `database/seeders/PayrollSeeder.php`, `README.md`

- [ ] **Step 1: `SalaryConfigSeeder`** — thay thân `run()`:

```php
        // Lương theo giờ: [thử việc, chính thức]
        $salaryByRole = [
            'staff'   => [20000, 25000],
            'kitchen' => [18000, 22000],
        ];
        $startedAt = now()->subMonth()->startOfMonth();

        foreach (User::whereIn('role', array_keys($salaryByRole))->get() as $user) {
            [$probation, $rate] = $salaryByRole[$user->role];
            $user->update(['started_at' => $startedAt]);

            SalaryConfig::create([
                'user_id'        => $user->id,
                'type'           => 'hourly',
                'rate'           => $rate,
                'probation_rate' => $probation,
                'effective_from' => $startedAt,
            ]);
        }
```

- [ ] **Step 2: `PayrollSeeder`** — bỏ logic tính tay, dùng service:

```php
<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Services\BranchPayroll;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(BranchPayroll $payroll): void
    {
        foreach (Branch::pluck('id') as $branchId) {
            $payroll->calculate($branchId, now()->month, now()->year);
        }
    }
}
```

- [ ] **Step 3: Chạy seed trên DB dev để kiểm tra**

Run: `$PHP artisan migrate:fresh --seed`
Expected: không lỗi. (Hỏi người dùng trước khi chạy — lệnh xoá dữ liệu DB dev.)

- [ ] **Step 4: README** — dòng "Dashboard Manager (giai đoạn 2–4)" giữ nguyên, thêm sau nó:

```markdown
- **Dashboard Manager (giai đoạn 5):** bảng lương theo giờ (thử việc 7 ngày, thưởng/phạt, chốt, đã trả), manager đặt lương từng nhân viên, xuất Excel (báo cáo, bảng lương, chấm công, đơn hàng), trả lời đánh giá
```

và dòng "Đang phát triển": `Manager còn: tính lương, chấm công khuôn mặt, xuất Excel, trả lời đánh giá` → `Manager còn: chấm công khuôn mặt (xem plan docs/superpowers/plans/2026-10-05-manager-cham-cong-khuon-mat.md)`.

- [ ] **Step 5: Chạy toàn bộ test**

Run: `$PHP artisan test`
Expected: tất cả PASS.

- [ ] **Step 6: Commit**

```bash
git add database/seeders/SalaryConfigSeeder.php database/seeders/PayrollSeeder.php README.md
git commit -m "chore: seeder lương theo giờ + thử việc, cập nhật README giai đoạn 5"
```
