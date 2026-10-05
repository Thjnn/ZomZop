<?php

namespace Tests\Feature\Face;

use App\Models\KioskDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class KioskManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_create_shows_link_once_and_lists_devices(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $res = $this->actingAs($manager)->post('/manager/kiosks', ['name' => 'Quầy thu ngân']);
        $res->assertRedirect(route('manager.kiosks.index'))->assertSessionHas('kiosk_link');

        $device = KioskDevice::first();
        $this->assertSame($branch->id, (int) $device->branch_id);
        $link = session('kiosk_link');
        $this->assertStringContainsString('/kiosk#device=', $link);
        $this->assertTrue(KioskDevice::findByToken(substr($link, strpos($link, '=') + 1))->is($device));

        $this->actingAs($manager)->get('/manager/kiosks')->assertOk()->assertSee('Quầy thu ngân');
    }

    public function test_name_required(): void
    {
        $this->actingAs($this->makeUser('manager', $this->makeBranch()))
            ->post('/manager/kiosks', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_revoke_and_branch_isolation(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        [$mine]  = KioskDevice::issue($a->id, 'Quầy A');
        [$other] = KioskDevice::issue($b->id, 'Quầy B bí mật');
        $manager = $this->makeUser('manager', $a);

        $this->actingAs($manager)->get('/manager/kiosks')->assertDontSee('Quầy B bí mật');
        $this->actingAs($manager)->patch("/manager/kiosks/{$other->id}/revoke")->assertNotFound();
        $this->assertNull($other->fresh()->revoked_at);

        $this->actingAs($manager)->patch("/manager/kiosks/{$mine->id}/revoke")->assertSessionHas('success');
        $this->assertNotNull($mine->fresh()->revoked_at);
    }
}
