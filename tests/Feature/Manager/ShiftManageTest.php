<?php

namespace Tests\Feature\Manager;

use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_create_list_update_shift(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->post('/manager/shifts', ['name' => 'Ca tối', 'start_time' => '18:00', 'end_time' => '23:00'])
            ->assertSessionHas('success');
        $shift = Shift::where('name', 'Ca tối')->first();
        $this->assertSame($branch->id, (int) $shift->branch_id);

        $this->actingAs($manager)->get('/manager/shifts')->assertSee('Ca tối')->assertSee('18:00');

        $this->actingAs($manager)->put("/manager/shifts/{$shift->id}", ['name' => 'Ca đêm', 'start_time' => '22:00', 'end_time' => '06:00'])
            ->assertSessionHas('success');
        $this->assertSame('Ca đêm', $shift->fresh()->name);
    }

    public function test_invalid_times_rejected(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))->from('/manager/shifts')
            ->post('/manager/shifts', ['name' => 'Lỗi', 'start_time' => '25:00', 'end_time' => '25:00'])
            ->assertSessionHasErrors(['start_time', 'end_time']);
        $this->actingAs($this->makeUser('manager', $branch))->from('/manager/shifts')
            ->post('/manager/shifts', ['name' => 'Lỗi', 'start_time' => '08:00', 'end_time' => '08:00'])
            ->assertSessionHasErrors('end_time');
        $this->assertDatabaseCount('shifts', 0);
    }

    public function test_delete_unused_shift(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch);

        $this->actingAs($this->makeUser('manager', $branch))->delete("/manager/shifts/{$shift->id}")
            ->assertSessionHas('success');
        $this->assertDatabaseCount('shifts', 0);
    }

    /** Review Focus #5 */
    public function test_cannot_delete_shift_with_attendance(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch);
        $staff  = $this->makeUser('staff', $branch);
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id, 'check_in' => now(), 'method' => 'manual']);

        $this->actingAs($this->makeUser('manager', $branch))->from('/manager/shifts')
            ->delete("/manager/shifts/{$shift->id}")
            ->assertSessionHasErrors('shift');
        $this->assertDatabaseCount('shifts', 1);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_other_branch_shift_is_404(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $shift = $this->makeShift($b);
        $manager = $this->makeUser('manager', $a);

        $this->actingAs($manager)->put("/manager/shifts/{$shift->id}", ['name' => 'X', 'start_time' => '08:00', 'end_time' => '09:00'])->assertNotFound();
        $this->actingAs($manager)->delete("/manager/shifts/{$shift->id}")->assertNotFound();
        $this->actingAs($manager)->get('/manager/shifts')->assertDontSee('Ca sáng');
    }
}
