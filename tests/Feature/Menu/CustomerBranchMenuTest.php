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

    public function test_search_finds_items_by_name_with_branch_price(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Bò');
        $this->makeMenuItem('Pizza Hải Sản');
        $off = $this->makeMenuItem('Burger Đã Tắt');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $off->id, 'is_available' => false]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->get('/search?q=burger')
            ->assertOk()
            ->assertSee('Burger Bò')
            ->assertSee('61.000 đ')
            ->assertDontSee('Pizza Hải Sản')
            ->assertDontSee('Burger Đã Tắt');

        $this->get('/search?q=%25')->assertOk()->assertDontSee('Burger Bò');
        $this->get('/search')->assertOk();

        // Gõ trực tiếp: chỉ trả khối kết quả, không kèm layout
        $this->withSession(['selected_branch_id' => $branch->id])
            ->get('/search?q=burger', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('Burger Bò')
            ->assertDontSee('<html', false);
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

    public function test_bestsellers_page_shows_only_items_flagged_at_selected_branch(): void
    {
        $a = $this->makeBranch('Chi nhánh A');
        $b = $this->makeBranch('Chi nhánh B');
        $hot   = $this->makeMenuItem('Burger Hot A');
        $other = $this->makeMenuItem('Pizza Hot B');
        $off   = $this->makeMenuItem('Gà Hot Tắt');
        $this->makeMenuItem('Khoai Thường');
        BranchMenuItem::create(['branch_id' => $a->id, 'menu_item_id' => $hot->id, 'price' => 61000, 'is_available' => true, 'is_bestseller' => true]);
        BranchMenuItem::create(['branch_id' => $b->id, 'menu_item_id' => $other->id, 'is_available' => true, 'is_bestseller' => true]);
        BranchMenuItem::create(['branch_id' => $a->id, 'menu_item_id' => $off->id, 'is_available' => false, 'is_bestseller' => true]);

        $this->withSession(['selected_branch_id' => $a->id])
            ->get('/ban-chay')
            ->assertOk()
            ->assertSee('Burger Hot A')
            ->assertSee('61.000 đ')
            ->assertDontSee('Pizza Hot B')
            ->assertDontSee('Gà Hot Tắt')
            ->assertDontSee('Khoai Thường');

        $this->flushSession()->get('/ban-chay')->assertSee('Burger Hot A')->assertSee('Pizza Hot B')->assertDontSee('Khoai Thường');
    }

    public function test_item_card_shows_average_rating_from_reviews_of_orders_containing_it(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Có Sao');
        foreach ([5, 4] as $rating) {
            $order = $this->makeOrder($branch);
            $this->addItem($order, $burger, 1);
            \App\Models\Review::create(['order_id' => $order->id, 'user_id' => $order->user_id, 'branch_id' => $branch->id, 'rating' => $rating]);
        }
        $this->makeMenuItem('Pizza Chưa Ai Chấm');

        $this->get('/category/test')
            ->assertOk()
            ->assertSee('4.5')
            ->assertDontSee('đánh giá');
    }

    public function test_home_special_offers_show_branch_chef_picks_else_discounted_items(): void
    {
        $branch = $this->makeBranch();
        $sale   = $this->makeMenuItem('Món Đang Giảm');
        $sale->update(['discount_percent' => 20]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->get('/')->assertOk()->assertSee('Món Đang Giảm');

        $pick = $this->makeMenuItem('Món Đầu Bếp Chọn');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $pick->id, 'is_available' => true, 'is_chef_pick' => true]);

        $html = $this->get('/')->assertOk()->getContent();
        $section = substr($html, strpos($html, 'specialOffersSwiper'), 4000);
        $this->assertStringContainsString('Món Đầu Bếp Chọn', $section);
        $this->assertStringNotContainsString('Món Đang Giảm', $section);
        $this->assertStringNotContainsString('Giảm 0%', $html);
    }

    public function test_all_menu_page_sorts_by_rating_and_price_and_home_links_to_it(): void
    {
        $branch = $this->makeBranch();
        $low  = $this->makeMenuItem('Món Ít Sao');
        $high = $this->makeMenuItem('Món Nhiều Sao');
        $none = $this->makeMenuItem('Món Chưa Chấm');
        $none->update(['base_price' => 10000]);
        foreach ([$low->id => 3, $high->id => 5] as $id => $rating) {
            $order = $this->makeOrder($branch);
            $this->addItem($order, \App\Models\MenuItem::find($id), 1);
            \App\Models\Review::create(['order_id' => $order->id, 'user_id' => $order->user_id, 'branch_id' => $branch->id, 'rating' => $rating]);
        }

        $this->get('/thuc-don')->assertOk()->assertSeeInOrder(['Món Nhiều Sao', 'Món Ít Sao', 'Món Chưa Chấm']);
        $this->get('/thuc-don?sort=price_asc')->assertSeeInOrder(['Món Chưa Chấm', 'Món Ít Sao']);
        $this->get('/thuc-don?sort=bogus')->assertOk()->assertSeeInOrder(['Món Nhiều Sao', 'Món Ít Sao']);

        $this->get('/')->assertOk()
            ->assertSee(route('menu.all', ['sort' => 'rating']), false)
            ->assertSee(route('branches.select'), false)
            ->assertSeeInOrder(['Món Ngon Được Yêu Thích', 'Món Nhiều Sao', 'Món Ít Sao']);

        // Admin ghim món chưa có sao → đứng đầu mục trên trang chủ
        $none->update(['is_featured' => true]);
        $this->get('/')->assertSeeInOrder(['Món Ngon Được Yêu Thích', 'Món Chưa Chấm', 'Món Nhiều Sao', 'Món Ít Sao']);
    }
}
