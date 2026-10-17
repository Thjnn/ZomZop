<?php

namespace Tests\Feature\Staff;

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollAdjustment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class StaffAreaTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_only_staff_and_kitchen_open_own_page_and_only_kitchen_opens_display(): void
    {
        $branch = $this->makeBranch();

        foreach (['customer', 'manager', 'admin'] as $role) {
            $this->actingAs($this->makeUser($role, $branch))->get('/staff')->assertForbidden();
        }
        $this->actingAs($this->makeUser('staff', $branch))->get('/kitchen')->assertForbidden();
        $this->actingAs($this->makeUser('staff', $branch))->get('/staff')->assertOk();
        $this->actingAs($this->makeUser('kitchen', $branch))->get('/staff')->assertOk();
        $this->actingAs($this->makeUser('kitchen', $branch))->get('/kitchen')->assertOk();
    }

    public function test_own_page_shows_only_my_attendance_and_payroll(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch);
        $me     = $this->makeUser('staff', $branch);
        $other  = $this->makeUser('staff', $branch);

        Attendance::create(['user_id' => $me->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id,
            'check_in' => '2026-10-02 08:20', 'check_out' => '2026-10-02 14:00', 'method' => 'face']);
        Attendance::create(['user_id' => $other->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id,
            'check_in' => '2026-10-03 08:00', 'check_out' => '2026-10-03 11:11', 'method' => 'face']);

        $p = Payroll::create(['user_id' => $me->id, 'branch_id' => $branch->id, 'month' => 10, 'year' => 2026,
            'total_hours' => 5.67, 'total_days' => 1, 'base_salary' => 141750, 'bonus' => 20000, 'deduction' => 0, 'total' => 161750, 'status' => 'draft']);
        PayrollAdjustment::create(['payroll_id' => $p->id, 'type' => 'bonus', 'amount' => 20000, 'reason' => 'Làm thêm ngày lễ']);
        Payroll::create(['user_id' => $other->id, 'branch_id' => $branch->id, 'month' => 10, 'year' => 2026,
            'total_hours' => 3, 'total_days' => 1, 'base_salary' => 999999, 'bonus' => 0, 'deduction' => 0, 'total' => 999999, 'status' => 'paid']);

        $this->actingAs($me)->get('/staff?month=2026-10')
            ->assertOk()
            ->assertSee('trễ 20′')
            ->assertSee('161.750đ')
            ->assertSee('Làm thêm ngày lễ')
            ->assertSee('Tạm tính')
            ->assertDontSee('11:11')
            ->assertDontSee('999.999đ');
    }

    public function test_kitchen_display_lists_confirmed_and_cooking_of_own_branch_without_customer_info(): void
    {
        $mine  = $this->makeBranch();
        $other = $this->makeBranch('B');
        $item  = $this->makeMenuItem('Gà Rán Giòn');

        $waiting = $this->makeOrder($mine, ['status' => 'confirmed', 'order_code' => 'ZZWAIT01']);
        $this->addItem($waiting, $item, 3);
        $this->makeOrder($mine, ['status' => 'cooking', 'order_code' => 'ZZCOOK01']);
        $this->makeOrder($mine, ['status' => 'pending', 'order_code' => 'ZZPEND01']);
        $this->makeOrder($mine, ['status' => 'ready', 'order_code' => 'ZZREADY1']);
        $this->makeOrder($other, ['status' => 'confirmed', 'order_code' => 'ZZOTHER1']);

        $this->actingAs($this->makeUser('kitchen', $mine))->get('/kitchen')
            ->assertOk()
            ->assertSee('ZZWAIT01')
            ->assertSee('Gà Rán Giòn')
            ->assertSee('ZZCOOK01')
            ->assertDontSee('ZZPEND01')
            ->assertDontSee('ZZREADY1')
            ->assertDontSee('ZZOTHER1')
            ->assertDontSee($waiting->user->name)
            ->assertDontSee('A01');
    }
}
