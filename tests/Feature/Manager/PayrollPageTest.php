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
