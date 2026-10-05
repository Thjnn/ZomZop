<?php

namespace Tests\Feature\Face;

use App\Models\FaceDescriptor;
use App\Models\User;
use App\Services\FaceMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class FaceMatcherTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    /** Vector 128 chiều chỉ khác nhau ở phần tử đầu → khoảng cách Euclid = |a - b| */
    private function vec(float $a): array
    {
        $v = array_fill(0, 128, 0.0);
        $v[0] = $a;

        return $v;
    }

    private function enroll(User $user, float ...$samples): void
    {
        foreach ($samples as $s) {
            FaceDescriptor::create(['user_id' => $user->id, 'descriptor' => $this->vec($s)]);
        }
    }

    public function test_matches_nearest_staff_within_threshold(): void
    {
        $branch = $this->makeBranch();
        $ngan   = $this->makeUser('staff', $branch);
        $bep    = $this->makeUser('kitchen', $branch);
        $this->enroll($ngan, 0.0);
        $this->enroll($bep, 0.8);

        $m = app(FaceMatcher::class)->match($branch->id, $this->vec(0.1));

        $this->assertTrue($m['user']->is($ngan));
        $this->assertEqualsWithDelta(0.1, $m['distance'], 1e-9);
        $this->assertEqualsWithDelta(77.78, $m['confidence'], 0.01);   // (1 - 0.1/0.45) * 100
    }

    public function test_too_far_is_not_recognized(): void
    {
        $branch = $this->makeBranch();
        $this->enroll($this->makeUser('staff', $branch), 0.0);

        $this->assertNull(app(FaceMatcher::class)->match($branch->id, $this->vec(0.5)));
    }

    public function test_two_people_too_close_is_rejected(): void
    {
        $branch = $this->makeBranch();
        $this->enroll($this->makeUser('staff', $branch), 0.0);
        $this->enroll($this->makeUser('staff', $branch), 0.3);

        // 0.12 vs 0.18: chênh 0.06 < margin 0.08
        $this->assertNull(app(FaceMatcher::class)->match($branch->id, $this->vec(0.12)));
    }

    public function test_uses_closest_of_many_samples(): void
    {
        $branch = $this->makeBranch();
        $ngan   = $this->makeUser('staff', $branch);
        $this->enroll($ngan, 0.9, 0.0, -0.9);

        $this->assertTrue(app(FaceMatcher::class)->match($branch->id, $this->vec(0.05))['user']->is($ngan));
    }

    public function test_ignores_locked_other_branch_and_non_staff(): void
    {
        $branch = $this->makeBranch();
        $locked = $this->makeUser('staff', $branch);
        $locked->update(['is_active' => false]);
        $this->enroll($locked, 0.0);
        $this->enroll($this->makeUser('staff', $this->makeBranch('B')), 0.0);
        $this->enroll($this->makeUser('manager', $branch), 0.0);

        $this->assertNull(app(FaceMatcher::class)->match($branch->id, $this->vec(0.0)));
    }

    public function test_nearest_other_finds_duplicate_face(): void
    {
        $branch = $this->makeBranch();
        $ngan   = $this->makeUser('staff', $branch);
        $bep    = $this->makeUser('kitchen', $branch);
        $this->enroll($ngan, 0.0);
        $this->enroll($bep, 0.6);

        $hit = app(FaceMatcher::class)->nearestOther($branch->id, $this->vec(0.05), $bep->id);
        $this->assertTrue($hit['user']->is($ngan));
        $this->assertNull(app(FaceMatcher::class)->nearestOther($branch->id, $this->vec(0.05), $ngan->id));
    }
}
