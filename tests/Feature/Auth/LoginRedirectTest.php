<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    /** Lỗi #1: trang đã ghi nhớ (url.intended) không được treo lại sau đăng nhập */
    public function test_choosing_branch_after_login_does_not_jump_to_stale_page(): void
    {
        $branch   = $this->makeBranch();
        $customer = $this->makeUser('customer');

        $this->get('/manager')->assertRedirect(route('login'));
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->post('/branches/confirm', ['branch_id' => $branch->id])
            ->assertRedirect(route('home'));
    }

    public function test_customer_returns_to_requested_page_after_login(): void
    {
        $customer = $this->makeUser('customer');

        $this->get('/checkout')->assertRedirect(route('login'));
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect('/checkout');
    }

    public function test_manager_goes_to_dashboard_even_with_other_remembered_page(): void
    {
        $manager = $this->makeUser('manager', $this->makeBranch());

        $this->get('/checkout')->assertRedirect(route('login'));
        $this->post('/login', ['email' => $manager->email, 'password' => 'password'])
            ->assertRedirect(route('manager.dashboard'));

        $this->assertNull(session('url.intended'));
    }
}
