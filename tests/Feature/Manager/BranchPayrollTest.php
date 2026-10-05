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
