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
}
