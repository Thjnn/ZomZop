<?php

namespace Tests\Feature\Admin;

use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class OrderViewTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_orders_of_all_branches_and_filters(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $oa = $this->makeOrder($a, ['status' => 'pending']);
        $ob = $this->makeOrder($b, ['status' => 'completed']);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee($oa->order_code)->assertSee($ob->order_code);
        $this->actingAs($admin)->get("/admin/orders?branch_id={$a->id}")->assertSee($oa->order_code)->assertDontSee($ob->order_code);
        $this->actingAs($admin)->get('/admin/orders?status=completed')->assertDontSee($oa->order_code)->assertSee($ob->order_code);
        $this->actingAs($admin)->get("/admin/orders?q={$ob->order_code}")->assertDontSee($oa->order_code)->assertSee($ob->order_code);
    }

    public function test_invalid_filter_shows_error_not_redirect(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/admin/orders?status=abc')
            ->assertOk()->assertSee('Trạng thái lọc không hợp lệ.');
    }

    public function test_show_has_items_and_history_but_no_actions(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);
        $this->addItem($order, $this->makeMenuItem('Burger Bò Phô Mai'), 2);
        app(OrderStatusService::class)->transition($order, 'confirmed', $this->makeUser('manager', $branch));

        $this->actingAs($this->makeUser('admin'))->get("/admin/orders/{$order->id}")
            ->assertOk()
            ->assertSee('Burger Bò Phô Mai')
            ->assertSee('Đã xác nhận')
            ->assertDontSee('name="status"', false);
    }

    public function test_admin_cannot_change_order_status(): void
    {
        $order = $this->makeOrder($this->makeBranch());

        $this->actingAs($this->makeUser('admin'))
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertForbidden();
    }
}
