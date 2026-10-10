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
