<?php

namespace Tests\Feature\Face;

use App\Models\Attendance;
use App\Models\FaceDescriptor;
use App\Models\KioskDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class AttendancePhotoTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private $branch;
    private string $token;
    private $staff;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->branch = $this->makeBranch();
        $this->makeShift($this->branch, '00:00', '23:59');
        [, $this->token] = KioskDevice::issue($this->branch->id, 'Quầy');
        $this->staff = $this->makeUser('staff', $this->branch);
        FaceDescriptor::create(['user_id' => $this->staff->id, 'descriptor' => $this->vec()]);
    }

    private function vec(): array
    {
        return array_fill(0, 128, 0.0);
    }

    private function punch(?UploadedFile $photo)
    {
        return $this->post('/kiosk/api/punch', array_filter(['descriptor' => $this->vec(), 'photo' => $photo]),
            ['X-Kiosk-Token' => $this->token, 'Accept' => 'application/json']);
    }

    public function test_photos_saved_on_check_in_and_out(): void
    {
        $this->travelTo('2026-10-06 08:00');
        $this->punch(UploadedFile::fake()->image('in.jpg', 320, 240))->assertJson(['status' => 'checked_in']);
        $a = Attendance::first();
        $dir = "attendance-photos/{$this->branch->id}/2026-10-06";
        $this->assertSame("{$dir}/{$a->id}-in.jpg", $a->photo_path);
        Storage::disk('local')->assertExists($a->photo_path);

        $this->travelTo('2026-10-06 14:00');
        $this->punch(UploadedFile::fake()->image('out.jpg', 320, 240))->assertJson(['status' => 'checked_out']);
        Storage::disk('local')->assertExists("{$dir}/{$a->id}-out.jpg");
    }

    public function test_bad_or_missing_photo_still_punches(): void
    {
        $this->punch(UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'))->assertJson(['status' => 'checked_in']);
        $this->assertNull(Attendance::first()->photo_path);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_not_recognized_saves_no_photo(): void
    {
        $v = $this->vec();
        $v[0] = 0.9;
        $this->post('/kiosk/api/punch', ['descriptor' => $v, 'photo' => UploadedFile::fake()->image('a.jpg')],
            ['X-Kiosk-Token' => $this->token, 'Accept' => 'application/json'])->assertJson(['status' => 'not_recognized']);

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_manager_views_photo_only_for_own_branch(): void
    {
        $this->punch(UploadedFile::fake()->image('in.jpg'));
        $a = Attendance::first();

        $this->actingAs($this->makeUser('manager', $this->branch))
            ->get("/manager/attendances/{$a->id}/photo/in")->assertOk();
        $this->actingAs($this->makeUser('manager', $this->branch))
            ->get("/manager/attendances/{$a->id}/photo/out")->assertNotFound();
        $this->actingAs($this->makeUser('manager', $this->makeBranch('B')))
            ->get("/manager/attendances/{$a->id}/photo/in")->assertNotFound();
    }

    public function test_prune_deletes_photos_older_than_30_days(): void
    {
        $this->travelTo('2026-09-01 08:00');
        $this->punch(UploadedFile::fake()->image('old.jpg'));
        $old = Attendance::first();
        $this->travelTo('2026-10-06 08:00');
        $this->punch(UploadedFile::fake()->image('new.jpg'));
        $new = Attendance::latest('id')->first();

        $this->artisan('attendance:prune-photos')->assertSuccessful();

        Storage::disk('local')->assertMissing("attendance-photos/{$this->branch->id}/2026-09-01/{$old->id}-in.jpg");
        $this->assertNull($old->fresh()->photo_path);
        Storage::disk('local')->assertExists($new->photo_path);
    }

    public function test_deleting_face_data_also_deletes_face_checkout_photo_of_manual_check_in(): void
    {
        // Manager chấm vào tay, nhân viên chấm ra ở máy quầy → ảnh "ra" nằm trên lượt manual
        $a = Attendance::create(['user_id' => $this->staff->id, 'branch_id' => $this->branch->id,
            'shift_id' => \App\Models\Shift::first()->id, 'check_in' => now()->subHours(3), 'method' => 'manual']);
        $this->punch(UploadedFile::fake()->image('out.jpg'))->assertJson(['status' => 'checked_out']);
        Storage::disk('local')->assertExists($a->fresh()->photoFile('out'));

        $this->actingAs($this->makeUser('manager', $this->branch))->delete("/manager/staff/{$this->staff->id}/face");

        Storage::disk('local')->assertMissing($a->fresh()->photoFile('out'));
    }

    public function test_deleting_face_data_deletes_photos(): void
    {
        $this->punch(UploadedFile::fake()->image('in.jpg'));
        $a = Attendance::first();

        $this->actingAs($this->makeUser('manager', $this->branch))->delete("/manager/staff/{$this->staff->id}/face");

        Storage::disk('local')->assertMissing("attendance-photos/{$this->branch->id}/" . $a->check_in->toDateString() . "/{$a->id}-in.jpg");
        $this->assertNull($a->fresh()->photo_path);
    }
}
