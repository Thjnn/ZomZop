<?php

namespace Tests\Feature\Customer;

use App\Models\BranchMenuItem;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** Giỏ có $qty phần món giá 50.000đ */
    private function order($branch, $item, int $qty)
    {
        return $this->actingAs($this->makeUser('customer'))
            ->withSession(['cart' => ['branch_id' => $branch->id, 'items' => [
                ['id' => $item->id, 'name' => $item->name, 'price' => 50000, 'quantity' => $qty, 'image' => ''],
            ]]])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash']);
    }

    public function test_order_takes_stock_and_cancel_returns_it(): void
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $row    = BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $item->id, 'stock_qty' => 5]);

        $this->order($branch, $item, 3)->assertRedirect();
        $this->assertSame(2, $row->fresh()->stock_qty);

        $order = Order::sole();
        app(OrderStatusService::class)->transition($order, 'cancelled', $this->makeUser('manager', $branch));
        $this->assertSame(5, $row->fresh()->stock_qty);
    }

    public function test_not_enough_stock_blocks_order(): void
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem('Gà Rán');
        $row    = BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $item->id, 'stock_qty' => 2]);

        $this->order($branch, $item, 3)->assertSessionHasErrors(['stock' => 'Gà Rán chỉ còn 2 phần, vui lòng giảm số lượng.']);
        $this->assertSame(0, Order::count());
        $this->assertSame(2, $row->fresh()->stock_qty);
    }

    public function test_unlimited_stock_is_not_touched_and_zero_hides_item(): void
    {
        $branch = $this->makeBranch();
        $free   = $this->makeMenuItem('Nước Suối');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $free->id]);
        $out    = $this->makeMenuItem('Gà Hết Hàng');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $out->id, 'stock_qty' => 0]);

        $this->order($branch, $free, 10)->assertRedirect();
        $this->assertNull(BranchMenuItem::where('menu_item_id', $free->id)->value('stock_qty'));

        $this->withSession(['selected_branch_id' => $branch->id])->get('/thuc-don')
            ->assertSee('Nước Suối')->assertDontSee('Gà Hết Hàng');
    }

    public function test_manager_sets_and_clears_stock(): void
    {
        $branch  = $this->makeBranch();
        $item    = $this->makeMenuItem();
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->put('/manager/menu', ['items' => [$item->id => ['is_available' => 1, 'stock_qty' => 30]]])
            ->assertSessionHas('success');
        $this->assertSame(30, BranchMenuItem::sole()->stock_qty);

        $this->actingAs($manager)->put('/manager/menu', ['items' => [$item->id => ['is_available' => 1, 'stock_qty' => '']]]);
        $this->assertNull(BranchMenuItem::sole()->stock_qty);

        $this->actingAs($manager)->put('/manager/menu', ['items' => [$item->id => ['is_available' => 1, 'stock_qty' => -1]]])
            ->assertSessionHasErrors("items.{$item->id}.stock_qty");
    }
}
