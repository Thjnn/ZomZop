<?php

namespace Tests\Feature\Menu;

use App\Models\BranchMenuItem;
use App\Models\MenuItem;
use App\Services\BranchMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class BranchMenuTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private function setRow(int $branchId, MenuItem $item, ?int $price, bool $available = true): void
    {
        BranchMenuItem::updateOrCreate(
            ['branch_id' => $branchId, 'menu_item_id' => $item->id],
            ['price' => $price, 'is_available' => $available]
        );
    }

    public function test_filter_hides_items_turned_off_at_branch_only(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $burger = $this->makeMenuItem('Burger');
        $pizza  = $this->makeMenuItem('Pizza');
        $this->setRow($a->id, $pizza, null, false);

        $menu = new BranchMenu();
        $names = fn ($branchId) => $menu->filterAvailable(MenuItem::query(), $branchId)->orderBy('name')->pluck('name')->all();

        $this->assertSame(['Burger'], $names($a->id));
        $this->assertSame(['Burger', 'Pizza'], $names($b->id));
        $this->assertSame(['Burger', 'Pizza'], $names(null));
    }

    public function test_filter_hides_items_turned_off_chain_wide(): void
    {
        $a = $this->makeBranch();
        $item = $this->makeMenuItem('Ngừng bán');
        $item->update(['is_available' => false]);

        $this->assertSame(0, (new BranchMenu())->filterAvailable(MenuItem::query(), $a->id)->count());
        $this->assertFalse((new BranchMenu())->isAvailable($item, $a->id));
    }

    /** Review Focus #5 */
    public function test_branch_price_replaces_base_and_discount_still_applies(): void
    {
        $a = $this->makeBranch();
        $item = $this->makeMenuItem('Burger');          // base 50.000
        $item->update(['discount_percent' => 10]);
        $this->setRow($a->id, $item, 42000);

        $menu = new BranchMenu();
        $this->assertSame(37800, $menu->branchPrice($item->fresh(), $a->id));

        $items = MenuItem::whereKey($item->id)->get();
        $menu->applyPrices($items, $a->id);
        $this->assertSame(42000, $items[0]->base_price);
        $this->assertSame('37.800 đ', $items[0]->display_price);
    }

    public function test_no_branch_row_means_base_price(): void
    {
        $a = $this->makeBranch();
        $item = $this->makeMenuItem('Burger');

        $this->assertSame(50000, (new BranchMenu())->branchPrice($item, $a->id));
    }

    public function test_refresh_cart_updates_price_and_drops_unavailable(): void
    {
        $a = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $pizza  = $this->makeMenuItem('Pizza');
        $this->setRow($a->id, $burger, 60000);
        $this->setRow($a->id, $pizza, null, false);

        $cart = ['branch_id' => $a->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 50000, 'quantity' => 2, 'note' => ''],
            ['id' => $pizza->id,  'name' => 'Pizza',  'image' => '', 'price' => 50000, 'quantity' => 1, 'note' => ''],
        ]];

        $result = (new BranchMenu())->refreshCart($cart);

        $this->assertCount(1, $result['cart']['items']);
        $this->assertSame(60000, $result['cart']['items'][0]['price']);
        $this->assertSame([
            'Burger: giá đổi từ 50.000đ thành 60.000đ.',
            'Pizza: tạm hết tại chi nhánh, đã bỏ khỏi giỏ.',
        ], $result['changed']);
    }

    public function test_refresh_cart_without_changes_reports_nothing(): void
    {
        $a = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $cart = ['branch_id' => $a->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 50000, 'quantity' => 1, 'note' => ''],
        ]];

        $this->assertSame([], (new BranchMenu())->refreshCart($cart)['changed']);
    }
}
