<?php

namespace Tests\Feature\Face;

use App\Models\Attendance;
use App\Models\FaceDescriptor;
use App\Models\KioskDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class KioskApiTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private function vec(float $a): array
    {
        $v = array_fill(0, 128, 0.0);
        $v[0] = $a;

        return $v;
    }

    private function setupBranch(): array
    {
        $branch = $this->makeBranch('Chi nhánh Quận 1');
        $this->makeShift($branch, '08:00', '14:00', 'Ca sáng');
        [$device, $token] = KioskDevice::issue($branch->id, 'Quầy 1');
        $staff = $this->makeUser('staff', $branch);
        $staff->update(['name' => 'Nguyễn Thu Ngân']);
        FaceDescriptor::create(['user_id' => $staff->id, 'descriptor' => $this->vec(0.0)]);

        return [$branch, $device, $token, $staff];
    }

    public function test_kiosk_page_and_face_enroll_page_render(): void
    {
        $this->withoutVite();
        [$branch, , , $staff] = $this->setupBranch();

        $this->get('/kiosk')->assertOk()->assertSee('kiosk-video', false);
        $this->actingAs($this->makeUser('manager', $branch))
            ->get("/manager/staff/{$staff->id}/face")->assertOk()->assertSee('1</b>/5', false);
    }

    public function test_status_needs_valid_token(): void
    {
        [, $device, $token] = $this->setupBranch();

        $this->getJson('/kiosk/api/status')->assertStatus(401);
        $this->getJson('/kiosk/api/status', ['X-Kiosk-Token' => 'sai'])->assertStatus(401);
        $this->getJson('/kiosk/api/status', ['X-Kiosk-Token' => $token])
            ->assertOk()->assertJson(['branch' => 'Chi nhánh Quận 1', 'device' => 'Quầy 1']);
        $this->assertNotNull($device->fresh()->last_used_at);

        $device->update(['revoked_at' => now()]);
        $this->getJson('/kiosk/api/status', ['X-Kiosk-Token' => $token])->assertStatus(401);
    }

    public function test_punch_recognized_checks_in(): void
    {
        [, , $token, $staff] = $this->setupBranch();
        $this->travelTo('2026-10-06 08:00');

        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.05), 'action' => 'in'], ['X-Kiosk-Token' => $token])
            ->assertOk()
            ->assertJson(['status' => 'checked_in', 'name' => 'Nguyễn Thu Ngân', 'shift' => 'Ca sáng', 'time' => '08:00']);

        $a = Attendance::first();
        $this->assertSame($staff->id, $a->user_id);
        $this->assertSame('face', $a->method);
    }

    public function test_unknown_face_records_nothing(): void
    {
        [, , $token] = $this->setupBranch();

        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.9), 'action' => 'in'], ['X-Kiosk-Token' => $token])
            ->assertOk()->assertJson(['status' => 'not_recognized']);
        $this->assertSame(0, Attendance::count());
    }

    public function test_device_only_matches_its_own_branch(): void
    {
        [, , , $staff] = $this->setupBranch();
        $other = $this->makeBranch('B');
        $this->makeShift($other, '00:00', '23:59');
        [, $otherToken] = KioskDevice::issue($other->id, 'Quầy B');

        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.0), 'action' => 'in'], ['X-Kiosk-Token' => $otherToken])
            ->assertJson(['status' => 'not_recognized']);
    }

    public function test_bad_descriptor_is_rejected(): void
    {
        [, , $token] = $this->setupBranch();
        $h = ['X-Kiosk-Token' => $token];

        $this->postJson('/kiosk/api/punch', ['descriptor' => array_fill(0, 127, 0.0)], $h)->assertStatus(422);
        $this->postJson('/kiosk/api/punch', ['descriptor' => array_fill(0, 128, 5.0)], $h)->assertStatus(422);
        $this->postJson('/kiosk/api/punch', ['descriptor' => array_fill(0, 128, 'x')], $h)->assertStatus(422);
        $this->postJson('/kiosk/api/punch', [], $h)->assertStatus(422);
    }

    public function test_action_is_required_and_reason_limited(): void
    {
        [, , $token] = $this->setupBranch();
        $h = ['X-Kiosk-Token' => $token];

        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.0)], $h)->assertStatus(422)->assertJsonValidationErrors('action');
        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.0), 'action' => 'break'], $h)->assertStatus(422);
        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.0), 'action' => 'in', 'reason' => str_repeat('a', 256)], $h)
            ->assertStatus(422)->assertJsonValidationErrors('reason');
    }

    public function test_late_check_in_asks_reason_then_records_it(): void
    {
        [, , $token] = $this->setupBranch();
        $h = ['X-Kiosk-Token' => $token];
        $this->travelTo('2026-10-06 08:20');

        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.0), 'action' => 'in'], $h)
            ->assertJson(['status' => 'need_late_reason', 'minutes' => 20, 'name' => 'Nguyễn Thu Ngân']);
        $this->assertSame(0, Attendance::count());

        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.0), 'action' => 'in', 'reason' => 'Kẹt xe'], $h)
            ->assertJson(['status' => 'checked_in', 'late_minutes' => 20]);
        $this->assertSame('Kẹt xe', Attendance::first()->late_reason);
    }

    public function test_checkout_returns_hours(): void
    {
        [, , $token] = $this->setupBranch();
        $h = ['X-Kiosk-Token' => $token];
        $this->travelTo('2026-10-06 08:00');
        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.0), 'action' => 'in'], $h);
        $this->travelTo('2026-10-06 14:30');

        $this->postJson('/kiosk/api/punch', ['descriptor' => $this->vec(0.0), 'action' => 'out'], $h)
            ->assertJson(['status' => 'checked_out', 'time' => '14:30', 'hours' => 6.5]);
    }
}
