<?php

namespace Tests\Feature\Manager;

use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-05 15:00'));
    }

    public function test_manual_check_in_and_check_out(): void
    {
        $branch  = $this->makeBranch();
        $shift   = $this->makeShift($branch);
        $staff   = $this->makeUser('staff', $branch);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)
            ->post('/manager/attendances', ['user_id' => $staff->id, 'shift_id' => $shift->id, 'check_in' => '2026-10-05T08:05'])
            ->assertSessionHas('success');

        $att = Attendance::first();
        $this->assertSame('manual', $att->method);
        $this->assertSame($branch->id, (int) $att->branch_id);
        $this->assertNull($att->check_out);

        $this->actingAs($manager)->patch("/manager/attendances/{$att->id}/checkout")->assertSessionHas('success');
        $this->assertSame('2026-10-05 15:00', $att->fresh()->check_out->format('Y-m-d H:i'));

        $this->actingAs($manager)->get('/manager/attendances?date=2026-10-05')
            ->assertSee($staff->name)->assertSee('08:05')->assertSee('15:00')->assertSee('6,92');
    }

    public function test_check_in_with_check_out_at_once(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch);
        $staff  = $this->makeUser('kitchen', $branch);

        $this->actingAs($this->makeUser('manager', $branch))
            ->post('/manager/attendances', ['user_id' => $staff->id, 'shift_id' => $shift->id,
                'check_in' => '2026-10-04T08:00', 'check_out' => '2026-10-04T14:00', 'note' => 'Quên chấm']);

        $this->assertSame(6.0, Attendance::first()->working_hours);
    }

    /** Review Focus #4 */
    public function test_rejects_other_branch_staff_or_shift_and_bad_times(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $myShift    = $this->makeShift($a);
        $otherShift = $this->makeShift($b);
        $myStaff    = $this->makeUser('staff', $a);
        $otherStaff = $this->makeUser('staff', $b);
        $customer   = $this->makeUser('customer');
        $manager    = $this->makeUser('manager', $a);

        $cases = [
            [['user_id' => $otherStaff->id, 'shift_id' => $myShift->id, 'check_in' => '2026-10-05T08:00'], 'user_id'],
            [['user_id' => $customer->id,   'shift_id' => $myShift->id, 'check_in' => '2026-10-05T08:00'], 'user_id'],
            [['user_id' => $myStaff->id, 'shift_id' => $otherShift->id, 'check_in' => '2026-10-05T08:00'], 'shift_id'],
            [['user_id' => $myStaff->id, 'shift_id' => $myShift->id, 'check_in' => '2026-10-05T08:00', 'check_out' => '2026-10-05T07:00'], 'check_out'],
            [['user_id' => $myStaff->id, 'shift_id' => $myShift->id, 'check_in' => 'hôm qua'], 'check_in'],
        ];
        foreach ($cases as [$data, $field]) {
            $this->actingAs($manager)->from('/manager/attendances')->post('/manager/attendances', $data)->assertSessionHasErrors($field);
        }
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_cannot_check_in_twice_while_on_shift(): void
    {
        $branch  = $this->makeBranch();
        $shift   = $this->makeShift($branch);
        $staff   = $this->makeUser('staff', $branch);
        $manager = $this->makeUser('manager', $branch);
        $data    = ['user_id' => $staff->id, 'shift_id' => $shift->id, 'check_in' => '2026-10-05T08:00'];

        $this->actingAs($manager)->post('/manager/attendances', $data);
        $this->actingAs($manager)->from('/manager/attendances')->post('/manager/attendances', $data)
            ->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('attendances', 1);
    }

    /** Review fix #1: giờ vào/ra trong tương lai → chấm ra sẽ ra số giờ âm */
    public function test_future_times_rejected_and_checkout_before_check_in_blocked(): void
    {
        $branch  = $this->makeBranch();
        $shift   = $this->makeShift($branch);
        $staff   = $this->makeUser('staff', $branch);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->from('/manager/attendances')
            ->post('/manager/attendances', ['user_id' => $staff->id, 'shift_id' => $shift->id, 'check_in' => '2026-10-06T08:00'])
            ->assertSessionHasErrors('check_in');
        $this->actingAs($manager)->from('/manager/attendances')
            ->post('/manager/attendances', ['user_id' => $staff->id, 'shift_id' => $shift->id, 'check_in' => '2026-10-05T08:00', 'check_out' => '2026-10-05T18:00'])
            ->assertSessionHasErrors('check_out');
        $this->assertDatabaseCount('attendances', 0);

        // Bản ghi cũ lỡ có giờ vào ở tương lai: không cho chấm ra
        $att = Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id, 'check_in' => now()->addHours(3), 'method' => 'manual']);
        $this->actingAs($manager)->from('/manager/attendances')->patch("/manager/attendances/{$att->id}/checkout")
            ->assertSessionHasErrors('attendance');
        $this->assertNull($att->fresh()->check_out);
    }

    /** Review fix #2: lượt chưa chấm ra từ ngày trước phải hiện để manager đóng được */
    public function test_open_attendance_from_earlier_day_is_listed(): void
    {
        $branch  = $this->makeBranch();
        $staff   = $this->makeUser('staff', $branch);
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $this->makeShift($branch)->id,
            'check_in' => Carbon::parse('2026-10-03 08:10'), 'method' => 'manual']);

        $this->actingAs($this->makeUser('manager', $branch))->get('/manager/attendances?date=2026-10-05')
            ->assertSee($staff->name)->assertSee('03/10 08:10')->assertSee('Chấm ra');
    }

    public function test_checkout_twice_or_other_branch_rejected(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $staffB = $this->makeUser('staff', $b);
        $attB = Attendance::create(['user_id' => $staffB->id, 'branch_id' => $b->id, 'shift_id' => $this->makeShift($b)->id, 'check_in' => now()->subHours(2), 'method' => 'manual']);
        $manager = $this->makeUser('manager', $a);

        $this->actingAs($manager)->patch("/manager/attendances/{$attB->id}/checkout")->assertNotFound();
        $this->assertNull($attB->fresh()->check_out);

        $staffA = $this->makeUser('staff', $a);
        $attA = Attendance::create(['user_id' => $staffA->id, 'branch_id' => $a->id, 'shift_id' => $this->makeShift($a)->id,
            'check_in' => now()->subHours(2), 'check_out' => now()->subHour(), 'method' => 'manual']);
        $this->actingAs($manager)->from('/manager/attendances')->patch("/manager/attendances/{$attA->id}/checkout")
            ->assertSessionHasErrors('attendance');
    }
}
