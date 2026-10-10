<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CustomerManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_only_customers_with_spending(): void
    {
        $branch   = $this->makeBranch();
        $customer = $this->makeUser('customer');
        $this->makeOrder($branch, ['user_id' => $customer->id, 'status' => 'completed', 'total' => 120000]);
        $this->makeOrder($branch, ['user_id' => $customer->id, 'status' => 'cancelled', 'total' => 999000]);
        $staff = $this->makeUser('staff', $branch);

        $this->actingAs($this->makeUser('admin'))->get('/admin/customers')
            ->assertOk()
            ->assertSee($customer->email)
            ->assertSee('120.000')          // chỉ cộng đơn hoàn thành
            ->assertDontSee('999.000')
            ->assertDontSee($staff->email);
    }

    public function test_search_by_phone(): void
    {
        $a = $this->makeUser('customer');
        $a->update(['phone' => '0909111222']);
        $b = $this->makeUser('customer');

        $this->actingAs($this->makeUser('admin'))->get('/admin/customers?q=0909111222')
            ->assertOk()->assertSee($a->email)->assertDontSee($b->email);
    }

    public function test_show_lists_customer_orders(): void
    {
        $branch   = $this->makeBranch();
        $customer = $this->makeUser('customer');
        $order    = $this->makeOrder($branch, ['user_id' => $customer->id]);

        $this->actingAs($this->makeUser('admin'))->get("/admin/customers/{$customer->id}")
            ->assertOk()->assertSee($order->order_code);
    }

    public function test_lock_customer_blocks_login(): void
    {
        $customer = $this->makeUser('customer');

        $this->actingAs($this->makeUser('admin'))->patch("/admin/customers/{$customer->id}/lock")->assertRedirect();
        $this->assertFalse((bool) $customer->fresh()->is_active);

        auth()->logout();
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])->assertSessionHasErrors('email');
    }

    public function test_cannot_lock_non_customer(): void
    {
        $manager = $this->makeUser('manager', $this->makeBranch());

        $this->actingAs($this->makeUser('admin'))->patch("/admin/customers/{$manager->id}/lock")->assertNotFound();
        $this->actingAs($this->makeUser('admin'))->get("/admin/customers/{$manager->id}")->assertNotFound();
    }
}
