<?php

namespace Tests\Feature\Face;

use App\Models\KioskDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class KioskDeviceTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    public function test_issue_stores_only_hash_and_finds_by_token(): void
    {
        $branch = $this->makeBranch();

        [$device, $token] = KioskDevice::issue($branch->id, 'Quầy thu ngân');

        $this->assertSame(40, strlen($token));
        $this->assertSame(hash('sha256', $token), $device->token_hash);
        $this->assertTrue(KioskDevice::findByToken($token)->is($device));
        $this->assertNull(KioskDevice::findByToken('sai-token'));
        $this->assertNull(KioskDevice::findByToken(''));
    }

    public function test_revoked_device_is_not_found(): void
    {
        [$device, $token] = KioskDevice::issue($this->makeBranch()->id, 'Quầy');
        $device->update(['revoked_at' => now()]);

        $this->assertNull(KioskDevice::findByToken($token));
    }

    public function test_config_defaults(): void
    {
        $this->assertSame(0.45, config('attendance.face.threshold'));
        $this->assertSame(30, config('attendance.face.photo_days'));
    }
}
