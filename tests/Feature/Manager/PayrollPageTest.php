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

        $this->actingAs($manager)->post("/manager/payrolls/{$p->id}/adjustments", ['type' => 'bonus', 'amount' => 50000, 'reason' => 'Làm thêm ngày lễ'])
            ->assertSessionHas('success');
        $this->actingAs($manager)->post("/manager/payrolls/{$p->id}/adjustments", ['type' => 'deduction', 'amount' => 10000, 'reason' => 'Đi trễ 2 lần']);
        $this->assertSame(140000, $p->fresh()->total);

        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/pay")->assertSessionHasErrors('payroll');
        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/confirm")->assertSessionHas('success');
        $this->assertSame('confirmed', $p->fresh()->status);

        $this->actingAs($manager)->post("/manager/payrolls/{$p->id}/adjustments", ['type' => 'bonus', 'amount' => 1000, 'reason' => 'x'])->assertSessionHasErrors('payroll');
        $this->actingAs($manager)->delete('/manager/payroll-adjustments/' . $p->adjustments()->first()->id)->assertSessionHasErrors('payroll');
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

        $this->actingAs($manager)->post("/manager/payrolls/{$p->id}/adjustments", ['type' => 'deduction', 'amount' => 101000, 'reason' => 'Làm hỏng máy'])
            ->assertSessionHasErrors('amount');
        $this->assertSame(0, $p->fresh()->deduction);
        $this->assertSame(0, $p->adjustments()->count());   // tổng âm → không lưu khoản

        $this->actingAs($manager)->post("/manager/payrolls/{$p->id}/adjustments", ['type' => 'x', 'amount' => -1, 'reason' => ''])
            ->assertSessionHasErrors(['type', 'amount', 'reason']);
    }

    public function test_adjustment_reason_can_be_edited_and_deleted(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->setupStaff($branch);
        $this->actingAs($manager)->get('/manager/payrolls?month=2026-10');
        $p = Payroll::where('user_id', $staff->id)->first();

        $this->actingAs($manager)->post("/manager/payrolls/{$p->id}/adjustments", ['type' => 'bonus', 'amount' => 30000, 'reason' => 'Khách khen'])
            ->assertRedirect(route('manager.payrolls.index', ['month' => '2026-10']) . "#adj-{$p->id}");
        $a = $p->adjustments()->first();
        $this->assertSame($manager->id, $a->created_by);

        $this->actingAs($manager)->put("/manager/payroll-adjustments/{$a->id}", ['type' => 'bonus', 'amount' => 40000, 'reason' => 'Khách khen trên Google'])
            ->assertSessionHas('success');
        $this->assertSame(40000, $p->fresh()->bonus);
        $this->assertSame(140000, $p->fresh()->total);
        $this->actingAs($manager)->get('/manager/payrolls?month=2026-10')->assertSee('Khách khen trên Google');

        // tính lại lương giữ nguyên các khoản
        $this->actingAs($manager)->post('/manager/payrolls/recalculate', ['month' => '2026-10']);
        $this->assertSame(40000, $p->fresh()->bonus);

        $this->actingAs($manager)->delete("/manager/payroll-adjustments/{$a->id}")->assertSessionHas('success');
        $this->assertSame(0, $p->fresh()->bonus);
        $this->assertSame(100000, $p->fresh()->total);
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

    public function test_deleted_staff_still_listed_and_paid(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->setupStaff($branch, 'Người Đã Nghỉ');
        $staff->delete();   // khách/nhân viên tự xoá tài khoản (soft delete)

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/payrolls?month=2026-10')
            ->assertOk()
            ->assertSee('Người Đã Nghỉ')
            ->assertSee('100.000đ');
    }

    public function test_page_refreshes_drafts_and_skips_future_months(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->setupStaff($branch);
        $this->actingAs($manager)->get('/manager/payrolls?month=2026-10');
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $this->makeShift($branch)->id,
            'check_in' => '2026-10-03 08:00', 'check_out' => '2026-10-03 10:00', 'method' => 'manual']);

        $this->actingAs($manager)->get('/manager/payrolls?month=2026-10')->assertSee('150.000đ');

        $future = today()->addMonth()->format('Y-m');
        $this->actingAs($manager)->get("/manager/payrolls?month={$future}")->assertOk();
        $this->assertSame(0, Payroll::ofMonth(today()->addMonth()->month, today()->addMonth()->year)->count());
    }

    /** Review Focus #3 */
    public function test_cannot_touch_other_branch_payroll(): void
    {
        $other = $this->makeBranch('B');
        $staff = $this->setupStaff($other);
        $p = Payroll::create(['user_id' => $staff->id, 'branch_id' => $other->id, 'month' => 10, 'year' => 2026]);
        $manager = $this->makeUser('manager', $this->makeBranch('A'));

        $a = $p->adjustments()->create(['type' => 'bonus', 'amount' => 5000, 'reason' => 'Của chi nhánh B']);
        $this->actingAs($manager)->post("/manager/payrolls/{$p->id}/adjustments", ['type' => 'bonus', 'amount' => 1000, 'reason' => 'x'])->assertNotFound();
        $this->actingAs($manager)->put("/manager/payroll-adjustments/{$a->id}", ['type' => 'bonus', 'amount' => 1000, 'reason' => 'x'])->assertNotFound();
        $this->actingAs($manager)->delete("/manager/payroll-adjustments/{$a->id}")->assertNotFound();
        $this->assertSame(5000, $a->fresh()->amount);
        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/confirm")->assertNotFound();
        $this->actingAs($manager)->patch("/manager/payrolls/{$p->id}/pay")->assertNotFound();
        $this->assertSame('draft', $p->fresh()->status);
    }
}
