<?php

namespace Tests\Feature\Face;

use App\Models\Attendance;
use App\Models\KioskDevice;
use App\Services\FacePunch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class FacePunchTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private $branch;
    private KioskDevice $device;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = $this->makeBranch();
        [$this->device] = KioskDevice::issue($this->branch->id, 'Quầy 1');
    }

    private function punch($user, bool $force = false): array
    {
        return app(FacePunch::class)->punch($this->device, $user, 87.5, $force);
    }

    public function test_check_in_picks_shift_starting_soonest_and_records_face(): void
    {
        $this->makeShift($this->branch, '06:00', '12:00', 'Ca sáng');
        $chieu = $this->makeShift($this->branch, '12:00', '18:00', 'Ca chiều');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 11:40');   // trong ca sáng, nhưng ca chiều bắt đầu gần hơn

        $r = $this->punch($staff);

        $this->assertSame('checked_in', $r['status']);
        $a = $r['attendance'];
        $this->assertSame($chieu->id, $a->shift_id);
        $this->assertSame('face', $a->method);
        $this->assertSame('87.50', $a->face_confidence);
        $this->assertSame('Thiết bị: Quầy 1', $a->note);
        $this->assertSame('2026-10-06 11:40:00', $a->check_in->format('Y-m-d H:i:s'));
    }

    public function test_overnight_shift_after_midnight(): void
    {
        $dem   = $this->makeShift($this->branch, '22:00', '02:00', 'Ca đêm');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-07 00:30');

        $r = $this->punch($staff);

        $this->assertSame('checked_in', $r['status']);
        $this->assertSame($dem->id, $r['attendance']->shift_id);
    }

    public function test_no_shift_now_records_nothing(): void
    {
        $this->makeShift($this->branch, '08:00', '12:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 15:00');

        $this->assertSame('no_shift', $this->punch($staff)['status']);
        $this->assertSame(0, Attendance::count());
    }

    public function test_check_out_and_short_shift_confirmation(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 08:00');
        $this->punch($staff);

        $this->travelTo('2026-10-06 08:05');
        $this->assertSame('confirm_checkout', $this->punch($staff)['status']);
        $this->assertNull(Attendance::first()->check_out);

        $r = $this->punch($staff, force: true);
        $this->assertSame('checked_out', $r['status']);
        $this->assertSame('08:05', $r['attendance']->check_out->format('H:i'));
    }

    public function test_normal_check_out(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 08:00');
        $this->punch($staff);
        $this->travelTo('2026-10-06 14:03');

        $r = $this->punch($staff);

        $this->assertSame('checked_out', $r['status']);
        $this->assertSame(1, Attendance::count());
    }

    public function test_repeat_within_cooldown_is_duplicate(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 08:00');
        $this->punch($staff);
        $this->travelTo('2026-10-06 08:01');

        $this->assertSame('duplicate', $this->punch($staff, force: true)['status']);
        $this->assertNull(Attendance::first()->check_out);
    }

    public function test_stale_open_attendance_is_left_for_manager(): void
    {
        $shift = $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $old = Attendance::create(['user_id' => $staff->id, 'branch_id' => $this->branch->id, 'shift_id' => $shift->id,
            'check_in' => '2026-10-05 08:00', 'method' => 'manual']);
        $this->travelTo('2026-10-06 08:00');

        $r = $this->punch($staff);

        $this->assertSame('checked_in', $r['status']);
        $this->assertNull($old->fresh()->check_out);
        $this->assertSame(2, Attendance::count());
    }
}
