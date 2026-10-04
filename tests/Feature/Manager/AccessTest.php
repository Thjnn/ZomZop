<?php

namespace Tests\Feature\Manager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/manager')->assertRedirect(route('login'));
    }

    public function test_customer_gets_403(): void
    {
        $this->actingAs($this->makeUser('customer'))->get('/manager')->assertForbidden();
    }

    public function test_staff_gets_403(): void
    {
        $branch = $this->makeBranch();
        $this->actingAs($this->makeUser('staff', $branch))->get('/manager')->assertForbidden();
    }

    public function test_manager_without_branch_gets_403(): void
    {
        $this->actingAs($this->makeUser('manager'))->get('/manager')->assertForbidden();
    }

    public function test_manager_with_branch_sees_dashboard(): void
    {
        $branch = $this->makeBranch('Chi nhánh Quận 7');

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager')
            ->assertOk()
            ->assertSee('Chi nhánh Quận 7');
    }

    public function test_manager_login_redirects_to_dashboard(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $this->post('/login', ['email' => $manager->email, 'password' => 'password'])
            ->assertRedirect(route('manager.dashboard'));
    }
}
