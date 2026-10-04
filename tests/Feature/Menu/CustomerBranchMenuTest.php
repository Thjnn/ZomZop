<?php

namespace Tests\Feature\Menu;

use App\Models\BranchMenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CustomerBranchMenuTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_category_page_uses_branch_price_and_hides_turned_off_items(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Chi Nhánh');
        $pizza  = $this->makeMenuItem('Pizza Đã Tắt');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $pizza->id, 'is_available' => false]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->get('/category/test')
            ->assertOk()
            ->assertSee('Burger Chi Nhánh')
            ->assertSee('61.000 đ')
            ->assertDontSee('Pizza Đã Tắt');
    }

    public function test_detail_json_uses_branch_price(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->getJson("/menu-items/{$burger->id}/detail")
            ->assertJsonPath('base_price', 61000)
            ->assertJsonPath('display_price', '61.000 đ');
    }

    public function test_cart_add_uses_branch_price(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->postJson('/cart/add', ['menu_item_id' => $burger->id, 'quantity' => 2])
            ->assertOk();

        $this->assertSame(61000, session('cart')['items'][0]['price']);
    }

    /** Review Focus #2 */
    public function test_cart_add_rejects_item_turned_off_at_branch(): void
    {
        $branch = $this->makeBranch();
        $pizza  = $this->makeMenuItem('Pizza');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $pizza->id, 'is_available' => false]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->postJson('/cart/add', ['menu_item_id' => $pizza->id, 'quantity' => 1])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Món này tạm hết tại chi nhánh bạn chọn.');

        $this->assertEmpty(session('cart')['items'] ?? []);
    }

    /** Review Focus #1 */
    public function test_checkout_store_uses_fresh_price_and_stops_to_inform_customer(): void
    {
        $branch   = $this->makeBranch();
        $burger   = $this->makeMenuItem('Burger');
        $customer = $this->makeUser('customer');
        $cart = ['branch_id' => $branch->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 50000, 'quantity' => 2, 'note' => ''],
        ]];
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 70000, 'is_available' => true]);

        $this->actingAs($customer)
            ->withSession(['cart' => $cart, 'selected_branch_id' => $branch->id])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash'])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('cart_changes', ['Burger: giá đổi từ 50.000đ thành 70.000đ.']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(70000, session('cart')['items'][0]['price']);

        // Lần đặt thứ 2 (khách đã thấy giá mới) thì đặt được, đúng giá mới
        $this->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash'])
            ->assertRedirect();
        $this->assertDatabaseHas('orders', ['total' => 140000]);
        $this->assertDatabaseHas('order_items', ['price_snapshot' => 70000, 'quantity' => 2]);
    }

    public function test_checkout_drops_item_turned_off_and_redirects_to_cart_if_empty(): void
    {
        $branch   = $this->makeBranch();
        $pizza    = $this->makeMenuItem('Pizza');
        $customer = $this->makeUser('customer');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $pizza->id, 'is_available' => false]);
        $cart = ['branch_id' => $branch->id, 'items' => [
            ['id' => $pizza->id, 'name' => 'Pizza', 'image' => '', 'price' => 50000, 'quantity' => 1, 'note' => ''],
        ]];

        $this->actingAs($customer)
            ->withSession(['cart' => $cart])
            ->get('/checkout')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart_changes', ['Pizza: tạm hết tại chi nhánh, đã bỏ khỏi giỏ.']);
    }

    public function test_checkout_page_shows_change_notice(): void
    {
        $branch   = $this->makeBranch();
        $burger   = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 70000, 'is_available' => true]);
        $cart = ['branch_id' => $branch->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 50000, 'quantity' => 1, 'note' => ''],
        ]];

        $this->actingAs($this->makeUser('customer'))
            ->withSession(['cart' => $cart])
            ->get('/checkout')
            ->assertOk()
            ->assertSee('Burger: giá đổi từ 50.000đ thành 70.000đ.')
            ->assertSee('70.000');
    }
}
