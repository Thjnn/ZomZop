<?php

namespace Tests\Feature\Manager;

use App\Models\ShiftAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_manager_saves_week_with_two_shifts_a_day_and_overwrites_on_resave(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $morning = $this->makeShift($branch);
        $evening = $this->makeShift($branch, '17:00', '22:00', 'Ca tối');
        $cook    = $this->makeUser('kitchen', $branch);

        // 2026-10-21 là thứ Tư → tuần bắt đầu 19/10; ngày 27/10 ngoài tuần bị bỏ qua
        $this->actingAs($manager)->put('/manager/schedule', ['week' => '2026-10-21', 'slots' => [
            $cook->id => ['2026-10-19' => [$morning->id, $evening->id], '2026-10-27' => [$morning->id]],
        ]])->assertRedirect('/manager/shifts?week=2026-10-19');

        $this->assertSame(2, ShiftAssignment::where('user_id', $cook->id)->where('work_date', '2026-10-19')->count());
        $this->assertSame(0, ShiftAssignment::where('work_date', '2026-10-27')->count());

        $this->get('/manager/shifts?week=2026-10-19')->assertOk()->assertSee('Xếp lịch tuần');
        $this->get('/manager/schedule/export?week=2026-10-19')->assertOk();

        $this->put('/manager/schedule', ['week' => '2026-10-19', 'slots' => [$cook->id => ['2026-10-20' => [$evening->id]]]]);
        $this->assertSame(1, ShiftAssignment::count());
    }

    public function test_cannot_schedule_other_branch_staff_or_shift(): void
    {
        $a = $this->makeBranch();
        $b = $this->makeBranch('Chi nhánh B');
        $shiftA = $this->makeShift($a);
        $shiftB = $this->makeShift($b);
        $mine   = $this->makeUser('staff', $a);
        $theirs = $this->makeUser('staff', $b);

        $this->actingAs($this->makeUser('manager', $a))->put('/manager/schedule', ['week' => '2026-10-19', 'slots' => [
            $theirs->id => ['2026-10-19' => [$shiftA->id]],
            $mine->id   => ['2026-10-19' => [$shiftB->id]],
        ]]);

        $this->assertSame(0, ShiftAssignment::count());
    }

    public function test_staff_sees_and_exports_only_own_schedule(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch, '08:00', '14:00', 'Ca sáng');
        $late   = $this->makeShift($branch, '17:00', '22:00', 'Ca khuya riêng');
        $me     = $this->makeUser('staff', $branch);
        $other  = $this->makeUser('staff', $branch);
        ShiftAssignment::create(['user_id' => $me->id, 'shift_id' => $shift->id, 'work_date' => '2026-10-20']);
        ShiftAssignment::create(['user_id' => $other->id, 'shift_id' => $late->id, 'work_date' => '2026-10-20']);

        $this->actingAs($me)->get('/staff?month=2026-10')->assertOk()
            ->assertSee('Ca sáng (08:00–14:00)')->assertDontSee('Ca khuya riêng (');
        $this->get('/staff/schedule/export?month=2026-10')->assertOk();
    }
}
