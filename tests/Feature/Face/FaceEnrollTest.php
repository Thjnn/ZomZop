<?php

namespace Tests\Feature\Face;

use App\Models\FaceDescriptor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class FaceEnrollTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private function vec(float $a): array
    {
        $v = array_fill(0, 128, 0.0);
        $v[0] = $a;

        return $v;
    }

    public function test_enroll_up_to_five_samples_with_consent(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->makeUser('staff', $branch);

        $this->actingAs($manager)->postJson("/manager/staff/{$staff->id}/face", ['descriptor' => $this->vec(0.0)])
            ->assertStatus(422)->assertJsonValidationErrors('consent');

        for ($i = 1; $i <= 5; $i++) {
            $this->actingAs($manager)
                ->postJson("/manager/staff/{$staff->id}/face", ['descriptor' => $this->vec($i / 100), 'consent' => true])
                ->assertOk()->assertJson(['count' => $i]);
        }

        $this->actingAs($manager)->postJson("/manager/staff/{$staff->id}/face", ['descriptor' => $this->vec(0.0), 'consent' => true])
            ->assertStatus(422)->assertJsonValidationErrors('descriptor');
        $this->assertSame(5, FaceDescriptor::where('user_id', $staff->id)->count());
    }

    public function test_rejects_face_of_another_staff(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $ngan    = $this->makeUser('staff', $branch);
        $ngan->update(['name' => 'Nguyễn Thu Ngân']);
        $bep     = $this->makeUser('kitchen', $branch);
        FaceDescriptor::create(['user_id' => $ngan->id, 'descriptor' => $this->vec(0.0)]);

        $this->actingAs($manager)->postJson("/manager/staff/{$bep->id}/face", ['descriptor' => $this->vec(0.05), 'consent' => true])
            ->assertStatus(422)
            ->assertJsonPath('errors.descriptor.0', 'Khuôn mặt này giống nhân viên Nguyễn Thu Ngân đã đăng ký.');
    }

    public function test_bad_descriptor_and_branch_isolation(): void
    {
        $a = $this->makeBranch('A');
        $manager = $this->makeUser('manager', $a);
        $mine  = $this->makeUser('staff', $a);
        $other = $this->makeUser('staff', $this->makeBranch('B'));

        $this->actingAs($manager)->postJson("/manager/staff/{$mine->id}/face", ['descriptor' => [1, 2], 'consent' => true])
            ->assertStatus(422);
        $this->actingAs($manager)->postJson("/manager/staff/{$other->id}/face", ['descriptor' => $this->vec(0.0), 'consent' => true])
            ->assertNotFound();
        $this->actingAs($manager)->delete("/manager/staff/{$other->id}/face")->assertNotFound();
    }

    public function test_delete_all_samples(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->makeUser('staff', $branch);
        FaceDescriptor::create(['user_id' => $staff->id, 'descriptor' => $this->vec(0.0)]);

        $this->actingAs($manager)->delete("/manager/staff/{$staff->id}/face")->assertSessionHas('success');
        $this->assertSame(0, FaceDescriptor::count());
    }
}
