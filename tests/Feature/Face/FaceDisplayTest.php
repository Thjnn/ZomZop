<?php

namespace Tests\Feature\Face;

use App\Models\Attendance;
use App\Models\FaceDescriptor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class FaceDisplayTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_staff_list_shows_face_samples(): void
    {
        $branch = $this->makeBranch();
        $withFace = $this->makeUser('staff', $branch);
        $this->makeUser('kitchen', $branch);
        FaceDescriptor::create(['user_id' => $withFace->id, 'descriptor' => array_fill(0, 128, 0.0)]);

        $this->actingAs($this->makeUser('manager', $branch))->get('/manager/staff')
            ->assertSee('1/5')
            ->assertSee('Chưa đăng ký')
            ->assertSee(route('manager.staff.face', $withFace));
    }

    public function test_attendance_shows_late_and_early_reasons(): void
    {
        $branch = $this->makeBranch();
        $shift  = $this->makeShift($branch, '08:00', '14:00');
        Attendance::create(['user_id' => $this->makeUser('staff', $branch)->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id,
            'check_in' => '2026-10-06 08:12', 'check_out' => '2026-10-06 13:25', 'method' => 'face', 'face_confidence' => 90,
            'late_reason' => 'Kẹt xe', 'early_reason' => 'Ốm']);

        $this->actingAs($this->makeUser('manager', $branch))->get('/manager/attendances?date=2026-10-06')
            ->assertSee("Trễ 12' · Kẹt xe", false)
            ->assertSee("Ra sớm 35' · Ốm", false);
    }

    public function test_attendance_shows_face_confidence_warning_and_photo_link(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $shift  = $this->makeShift($branch);
        $ok = Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id,
            'check_in' => '2026-10-06 08:00', 'method' => 'face', 'face_confidence' => 87.5,
            'photo_path' => 'attendance-photos/x/in.jpg', 'note' => 'Thiết bị: Quầy 1']);
        Attendance::create(['user_id' => $this->makeUser('staff', $branch)->id, 'branch_id' => $branch->id, 'shift_id' => $shift->id,
            'check_in' => '2026-10-06 09:00', 'method' => 'face', 'face_confidence' => 20]);

        $this->actingAs($this->makeUser('manager', $branch))->get('/manager/attendances?date=2026-10-06')
            ->assertSee('Khuôn mặt · 88%')
            ->assertSee('Khuôn mặt · 20%')
            ->assertSee('text-orange-600', false)
            ->assertSee(route('manager.attendances.photo', [$ok, 'in']));
    }
}
