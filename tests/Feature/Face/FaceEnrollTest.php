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

    /** Bộ $n mẫu quanh tâm $center (mẫu thứ i lệch i/1000 ở phần tử thứ hai) */
    private function set(float $center, int $n = 15): array
    {
        return array_map(function ($i) use ($center) {
            $v = $this->vec($center);
            $v[1] = $i / 1000;

            return $v;
        }, range(1, $n));
    }

    private function enroll($staff, array $descriptors, bool $consent = true)
    {
        return $this->postJson("/manager/staff/{$staff->id}/face", ['descriptors' => $descriptors, 'consent' => $consent]);
    }

    public function test_enroll_full_set_of_fifteen_replaces_old_samples(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        FaceDescriptor::create(['user_id' => $staff->id, 'descriptor' => $this->vec(0.3)]);   // mẫu kiểu cũ
        $this->actingAs($this->makeUser('manager', $branch));

        $this->enroll($staff, $this->set(0.0))->assertOk()->assertJson(['count' => 15]);
        $this->assertSame(15, FaceDescriptor::where('user_id', $staff->id)->count());

        // Chụp lại → thay cả bộ, không cộng dồn
        $this->enroll($staff, $this->set(0.1))->assertOk()->assertJson(['count' => 15]);
        $this->assertSame(15, FaceDescriptor::where('user_id', $staff->id)->count());
        $this->assertEquals(0.1, FaceDescriptor::where('user_id', $staff->id)->first()->descriptor[0]);
    }

    public function test_invalid_sets_are_rejected_and_keep_old_samples(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        FaceDescriptor::create(['user_id' => $staff->id, 'descriptor' => $this->vec(0.3)]);
        $this->actingAs($this->makeUser('manager', $branch));

        $this->enroll($staff, $this->set(0.0), false)->assertStatus(422)->assertJsonValidationErrors('consent');
        $this->enroll($staff, $this->set(0.0, 14))->assertStatus(422)->assertJsonValidationErrors('descriptors');
        $this->enroll($staff, $this->set(0.0, 16))->assertStatus(422)->assertJsonValidationErrors('descriptors');

        $short = $this->set(0.0);
        array_pop($short[3]);                     // 127 số
        $this->enroll($staff, $short)->assertStatus(422);

        $big = $this->set(0.0);
        $big[0][5] = 2;                           // ngoài [-1, 1]
        $this->enroll($staff, $big)->assertStatus(422);

        $mixed = $this->set(0.0);
        $mixed[7] = $this->vec(0.9);              // lẫn mặt người khác
        $this->enroll($staff, $mixed)->assertStatus(422)
            ->assertJsonPath('errors.descriptors.0', 'Các mẫu không cùng một người, hãy chụp lại.');

        $this->assertSame(1, FaceDescriptor::where('user_id', $staff->id)->count());
    }

    public function test_rejects_face_of_another_staff(): void
    {
        $branch = $this->makeBranch();
        $ngan   = $this->makeUser('staff', $branch);
        $ngan->update(['name' => 'Nguyễn Thu Ngân']);
        $bep    = $this->makeUser('kitchen', $branch);
        FaceDescriptor::create(['user_id' => $ngan->id, 'descriptor' => $this->vec(0.0)]);

        $this->actingAs($this->makeUser('manager', $branch));
        $this->enroll($bep, $this->set(0.05))->assertStatus(422)
            ->assertJsonPath('errors.descriptors.0', 'Khuôn mặt này giống nhân viên Nguyễn Thu Ngân đã đăng ký.');
        $this->assertSame(0, FaceDescriptor::where('user_id', $bep->id)->count());
    }

    public function test_branch_isolation(): void
    {
        $a = $this->makeBranch('A');
        $other = $this->makeUser('staff', $this->makeBranch('B'));
        $this->actingAs($this->makeUser('manager', $a));

        $this->enroll($other, $this->set(0.0))->assertNotFound();
        $this->delete("/manager/staff/{$other->id}/face")->assertNotFound();
    }

    public function test_staff_name_cannot_inject_script_into_page(): void
    {
        $this->withoutVite();
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $staff->update(['name' => "x');alert(1)//"]);   // nhân viên tự đổi tên ở trang hồ sơ
        FaceDescriptor::create(['user_id' => $staff->id, 'descriptor' => $this->vec(0.0)]);

        $html = $this->actingAs($this->makeUser('manager', $branch))
            ->get("/manager/staff/{$staff->id}/face")->assertOk()->getContent();

        // Tên không được nằm trong thuộc tính chạy JS (onsubmit/onclick)
        $this->assertDoesNotMatchRegularExpression('/on\w+="[^"]*alert\(1\)/', $html);
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
