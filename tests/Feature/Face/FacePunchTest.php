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

    private function punch($user, string $action, ?string $reason = null): array
    {
        return app(FacePunch::class)->punch($this->device, $user, 87.5, $action, $reason);
    }

    public function test_check_in_picks_shift_starting_soonest_and_records_face(): void
    {
        $this->makeShift($this->branch, '06:00', '12:00', 'Ca sáng');
        $chieu = $this->makeShift($this->branch, '12:00', '18:00', 'Ca chiều');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 11:40');   // trong ca sáng, nhưng ca chiều bắt đầu gần hơn

        $r = $this->punch($staff, 'in');

        $this->assertSame('checked_in', $r['status']);
        $a = $r['attendance'];
        $this->assertSame($chieu->id, $a->shift_id);
        $this->assertSame('face', $a->method);
        $this->assertSame('87.50', $a->face_confidence);
        $this->assertSame('Thiết bị: Quầy 1', $a->note);
        $this->assertNull($a->late_reason);
    }

    public function test_overnight_shift_after_midnight(): void
    {
        $dem   = $this->makeShift($this->branch, '22:00', '02:00', 'Ca đêm');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-07 00:30');

        $r = $this->punch($staff, 'in', 'Kẹt xe');   // trễ 2,5 giờ so với 22:00 hôm trước

        $this->assertSame('checked_in', $r['status']);
        $this->assertSame($dem->id, $r['attendance']->shift_id);
        $this->assertSame(150, $r['attendance']->lateMinutes());
    }

    public function test_no_shift_now_records_nothing(): void
    {
        $this->makeShift($this->branch, '08:00', '12:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 15:00');

        $this->assertSame('no_shift', $this->punch($staff, 'in')['status']);
        $this->assertSame(0, Attendance::count());
    }

    public function test_late_more_than_5_minutes_needs_reason(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);

        $this->travelTo('2026-10-06 08:05');
        $this->assertSame('checked_in', $this->punch($staff, 'in')['status']);   // đúng 5 phút: chưa tính trễ
        Attendance::query()->delete();

        $this->travelTo('2026-10-06 08:12');
        $r = $this->punch($staff, 'in');
        $this->assertSame('need_late_reason', $r['status']);
        $this->assertSame(12, $r['minutes']);
        $this->assertSame(0, Attendance::count());

        $r = $this->punch($staff, 'in', 'Kẹt xe');
        $this->assertSame('checked_in', $r['status']);
        $this->assertSame('Kẹt xe', $r['attendance']->late_reason);
        $this->assertSame(12, $r['attendance']->lateMinutes());
    }

    public function test_check_in_while_in_shift_is_rejected(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 08:00');
        $this->punch($staff, 'in');
        $this->travelTo('2026-10-06 09:00');

        $this->assertSame('already_in', $this->punch($staff, 'in')['status']);
        $this->assertSame(1, Attendance::count());
    }

    public function test_check_out_without_check_in_is_rejected(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $this->travelTo('2026-10-06 14:00');

        $this->assertSame('not_in', $this->punch($this->makeUser('staff', $this->branch), 'out')['status']);
    }

    public function test_normal_check_out_near_shift_end(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 08:00');
        $this->punch($staff, 'in');
        $this->travelTo('2026-10-06 13:50');   // còn đúng 10 phút: chưa tính ra sớm

        $r = $this->punch($staff, 'out');

        $this->assertSame('checked_out', $r['status']);
        $this->assertNull($r['attendance']->early_reason);
        $this->assertSame('13:50', $r['attendance']->check_out->format('H:i'));
    }

    public function test_early_leave_needs_reason(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 08:00');
        $this->punch($staff, 'in');
        $this->travelTo('2026-10-06 13:25');

        $r = $this->punch($staff, 'out');
        $this->assertSame('need_early_reason', $r['status']);
        $this->assertSame(35, $r['minutes']);
        $this->assertNull(Attendance::first()->check_out);

        $r = $this->punch($staff, 'out', 'Ốm');
        $this->assertSame('checked_out', $r['status']);
        $this->assertSame('Ốm', $r['attendance']->early_reason);
        $this->assertSame(35, $r['attendance']->earlyMinutes());
    }

    public function test_overnight_shift_check_out_is_not_early(): void
    {
        $this->makeShift($this->branch, '22:00', '02:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 22:00');
        $this->punch($staff, 'in');
        $this->travelTo('2026-10-07 02:00');

        $this->assertSame('checked_out', $this->punch($staff, 'out')['status']);
    }

    public function test_repeat_within_cooldown_is_duplicate(): void
    {
        $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $this->travelTo('2026-10-06 08:00');
        $this->punch($staff, 'in');
        $this->travelTo('2026-10-06 08:01');

        $this->assertSame('duplicate', $this->punch($staff, 'out', 'Ốm')['status']);
        $this->assertNull(Attendance::first()->check_out);
    }

    public function test_stale_open_attendance_is_left_for_manager(): void
    {
        $shift = $this->makeShift($this->branch, '08:00', '14:00');
        $staff = $this->makeUser('staff', $this->branch);
        $old = Attendance::create(['user_id' => $staff->id, 'branch_id' => $this->branch->id, 'shift_id' => $shift->id,
            'check_in' => '2026-10-05 08:00', 'method' => 'manual']);
        $this->travelTo('2026-10-06 08:00');

        $this->assertSame('checked_in', $this->punch($staff, 'in')['status']);
        $this->assertNull($old->fresh()->check_out);
        $this->assertSame(2, Attendance::count());
    }
}
