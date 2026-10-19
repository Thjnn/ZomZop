<?php

namespace Tests\Feature\Menu;

use App\Models\MenuItem;
use App\Models\MenuItemTopping;
use App\Models\OrderItem;
use App\Services\BranchMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ToppingTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function burgerWithToppings(): array
    {
        $burger = $this->makeMenuItem('Burger');            // 50.000
        $burger->update(['discount_percent' => 20]);         // còn 40.000, topping không giảm
        $tomato = MenuItemTopping::create(['menu_item_id' => $burger->id, 'name' => 'Cà chua', 'price' => 5000]);
        $meat   = MenuItemTopping::create(['menu_item_id' => $burger->id, 'name' => 'Thêm thịt', 'price' => 20000]);

        return [$burger, $tomato, $meat];
    }

    private function add(MenuItem $item, array $toppings, int $qty = 1)
    {
        return $this->postJson('/cart/add', ['menu_item_id' => $item->id, 'quantity' => $qty, 'toppings' => $toppings]);
    }

    public function test_toppings_add_to_price_without_item_discount_and_split_cart_lines(): void
    {
        $branch = $this->makeBranch();
        [$burger, $tomato, $meat] = $this->burgerWithToppings();
        $this->actingAs($this->makeUser('customer'))->withSession(['selected_branch_id' => $branch->id]);

        $this->add($burger, [$tomato->id, $meat->id])->assertJson(['success' => true]);
        $this->add($burger, [$meat->id, $tomato->id]);   // cùng topping, khác thứ tự → gộp
        $this->add($burger, []);                          // không topping → dòng riêng

        $items = session('cart')['items'];
        $this->assertCount(2, $items);
        $this->assertSame([65000, 2], [$items[0]['price'], $items[0]['quantity']]);
        $this->assertSame(40000, $items[1]['price']);

        $this->postJson('/cart/update', ['key' => BranchMenu::lineKey($items[1]), 'quantity' => 5])->assertJson(['subtotal' => 65000 * 2 + 40000 * 5]);
        $this->postJson('/cart/remove', ['key' => BranchMenu::lineKey($items[0])]);
        $this->assertSame([40000], array_column(session('cart')['items'], 'price'));
    }

    public function test_rejects_topping_off_at_branch_or_from_another_item(): void
    {
        $branch = $this->makeBranch();
        [$burger, $tomato, $meat] = $this->burgerWithToppings();
        $pizza  = $this->makeMenuItem('Pizza');
        $cheese = MenuItemTopping::create(['menu_item_id' => $pizza->id, 'name' => 'Phô mai', 'price' => 1]);
        DB::table('branch_unavailable_toppings')->insert(['branch_id' => $branch->id, 'menu_item_topping_id' => $tomato->id]);
        $this->actingAs($this->makeUser('customer'))->withSession(['selected_branch_id' => $branch->id]);

        $this->add($burger, [$tomato->id])->assertStatus(422);
        $this->add($burger, [$cheese->id])->assertStatus(422);
        $this->assertEmpty(session('cart.items', []));

        $this->getJson("/menu-items/{$burger->id}/detail")
            ->assertJsonPath('toppings', [['id' => $meat->id, 'name' => 'Thêm thịt', 'price' => 20000]]);
    }

    public function test_refresh_cart_drops_topping_turned_off_and_reprices(): void
    {
        $branch = $this->makeBranch();
        [$burger, $tomato, $meat] = $this->burgerWithToppings();
        $t    = fn ($m) => ['id' => $m->id, 'name' => $m->name, 'price' => $m->price];
        $line = fn ($toppings, $price) => ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => $price, 'quantity' => 1, 'note' => '', 'toppings' => $toppings];
        DB::table('branch_unavailable_toppings')->insert(['branch_id' => $branch->id, 'menu_item_topping_id' => $meat->id]);

        $result = (new BranchMenu())->refreshCart(['branch_id' => $branch->id, 'items' => [
            $line([$t($tomato), $t($meat)], 65000),
            $line([$t($tomato)], 45000),   // bỏ thịt xong, dòng trên giống dòng này → gộp
        ]]);

        $items = $result['cart']['items'];
        $this->assertCount(1, $items);
        $this->assertSame([45000, 2], [$items[0]['price'], $items[0]['quantity']]);
        $this->assertSame([
            'Burger: topping Thêm thịt tạm hết, đã bỏ.',
            'Burger: giá đổi từ 65.000đ thành 45.000đ.',
        ], $result['changed']);
    }

    public function test_checkout_saves_topping_snapshot_and_shows_it(): void
    {
        $branch = $this->makeBranch();
        [$burger, $tomato] = $this->burgerWithToppings();
        $cart = ['branch_id' => $branch->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 45000, 'quantity' => 2, 'note' => '',
             'toppings' => [['id' => $tomato->id, 'name' => 'Cà chua', 'price' => 5000]]],
        ]];

        $this->actingAs($this->makeUser('customer'))->withSession(['cart' => $cart])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash'])
            ->assertSessionHasNoErrors()->assertRedirect();

        $line = OrderItem::sole();
        $this->assertSame([45000, 90000, 'Cà chua'], [$line->price_snapshot, $line->subtotal, $line->topping_names]);

        $tomato->delete();   // admin xoá topping sau đó: đơn cũ vẫn giữ bản chụp
        $this->get(route('orders.show', $line->order_id))->assertSee('+ Cà chua');
    }

    public function test_admin_syncs_toppings_and_manager_turns_one_off(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $old    = MenuItemTopping::create(['menu_item_id' => $burger->id, 'name' => 'Cũ', 'price' => 1000]);
        $form   = ['category_id' => $burger->category_id, 'name' => 'Burger', 'base_price' => 50000, 'discount_percent' => 0, 'prep_time_minutes' => 10];

        $this->actingAs($this->makeUser('admin'))->put(route('admin.menu-items.update', $burger), $form + ['toppings' => [
            ['id' => $old->id, 'name' => '', 'price' => ''],          // xoá tên = bỏ topping
            ['id' => null, 'name' => 'Cà chua', 'price' => 5000],
            ['id' => null, 'name' => '', 'price' => ''],
        ]])->assertSessionHasNoErrors();
        $this->assertSame(['Cà chua'], $burger->toppings()->pluck('name')->all());

        $tomato = $burger->toppings()->sole();
        $this->actingAs($this->makeUser('manager', $branch))
            ->put(route('manager.menu.update'), ['toppings' => [$tomato->id => 0]])->assertSessionHasNoErrors();
        $this->assertTrue(DB::table('branch_unavailable_toppings')
            ->where(['branch_id' => $branch->id, 'menu_item_topping_id' => $tomato->id])->exists());

        $this->put(route('manager.menu.update'), ['toppings' => [$tomato->id => 1]]);
        $this->assertSame(0, DB::table('branch_unavailable_toppings')->count());
    }

    public function test_card_add_button_opens_modal_only_for_items_with_toppings(): void
    {
        $branch = $this->makeBranch();
        $this->burgerWithToppings();
        $this->makeMenuItem('Pizza');

        $html = $this->withSession(['selected_branch_id' => $branch->id])->get('/category/test')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'data-has-toppings="1"'));
        $this->assertStringContainsString('Có topping', $html);
    }
}
