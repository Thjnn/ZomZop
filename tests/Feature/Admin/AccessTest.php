<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
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
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_customer_and_manager_get_403(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('customer'))->get('/admin')->assertForbidden();
        $this->actingAs($this->makeUser('manager', $branch))->get('/admin')->assertForbidden();
        $this->actingAs($this->makeUser('staff', $branch))->get('/admin')->assertForbidden();
    }

    public function test_locked_admin_with_existing_session_gets_403(): void
    {
        $admin = $this->makeUser('admin');
        $admin->forceFill(['is_active' => false])->save();

        $this->actingAs($admin)->get('/admin')->assertForbidden();
    }

    public function test_admin_sees_dashboard(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get('/admin')
            ->assertOk()
            ->assertSee('Quản trị toàn chuỗi');
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = $this->makeUser('admin');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_cannot_use_manager_area(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/manager')->assertForbidden();
    }
}
