<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ProfileWorkLinksTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_profile_shows_work_links_by_role(): void
    {
        $branch = $this->makeBranch();
        $cases  = [
            'admin'   => ['admin.dashboard'],
            'manager' => ['manager.dashboard', 'staff.pos'],
            'staff'   => ['staff.pos', 'staff.me.orders'],
            'kitchen' => ['kitchen.dashboard', 'staff.dashboard'],
        ];

        foreach ($cases as $role => $routes) {
            $res = $this->actingAs($this->makeUser($role, $branch))->get('/profile')->assertOk()->assertSee('Khu vực làm việc');
            foreach ($routes as $r) {
                $res->assertSee('href="' . route($r) . '"', false);
            }
        }

        $this->actingAs($this->makeUser('customer'))->get('/profile')->assertOk()->assertDontSee('Khu vực làm việc');
    }
}
