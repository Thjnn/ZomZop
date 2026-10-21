<?php

namespace Tests\Feature\Customer;

use App\Models\Branch;
use App\Models\BranchMenuItem;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerOrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function order(array $attrs = []): Order
    {
        $user = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $branch = Branch::create(['name' => 'Test branch', 'address' => 'Test address']);
        return Order::create(array_merge([
            'order_code' => 'TEST-' . Str::uuid(), 'user_id' => $user->id, 'branch_id' => $branch->id,
            'type' => 'takeaway', 'status' => 'pending', 'subtotal' => 100000,
            'discount' => 0, 'total' => 100000, 'payment_method' => 'cash', 'payment_status' => 'unpaid',
        ], $attrs));
    }

    public function test_guest_must_log_in(): void
    {
        $order = $this->order();
        $this->get(route('orders.cancel.create', $order))->assertRedirect(route('login'));
        $this->post(route('orders.cancel.store', $order), [])->assertRedirect(route('login'));
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_customer_cannot_cancel_another_customers_order(): void
    {
        $order = $this->order();
        $other = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($other)->get(route('orders.cancel.create', $order))->assertNotFound();
        $this->post(route('orders.cancel.store', $order), ['cancel_reason' => 'duplicate'])->assertNotFound();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_customer_must_choose_a_valid_reason(): void
    {
        $order = $this->order();
        $this->actingAs($order->user)->post(route('orders.cancel.store', $order), [])->assertSessionHasErrors('cancel_reason');
        $this->post(route('orders.cancel.store', $order), ['cancel_reason' => 'forged'])->assertSessionHasErrors('cancel_reason');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_other_requires_a_non_blank_description(): void
    {
        $order = $this->order();
        $this->actingAs($order->user)->post(route('orders.cancel.store', $order), [
            'cancel_reason' => 'other', 'cancel_detail' => '   ',
        ])->assertSessionHasErrors('cancel_detail');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_server_rejects_orders_that_are_no_longer_pending_or_unpaid(): void
    {
        foreach (['confirmed', 'cooking', 'ready', 'completed', 'cancelled'] as $status) {
            $order = $this->order(['status' => $status]);
            $this->actingAs($order->user)->post(route('orders.cancel.store', $order), ['cancel_reason' => 'duplicate'])
                ->assertSessionHasErrors('cancel');
            $this->assertSame($status, $order->fresh()->status);
        }
        $paid = $this->order(['payment_status' => 'paid']);
        $this->actingAs($paid->user)->post(route('orders.cancel.store', $paid), ['cancel_reason' => 'duplicate'])
            ->assertSessionHasErrors('cancel');
        $this->assertSame('pending', $paid->fresh()->status);
    }

    public function test_cancellation_records_customer_reason_and_preserves_the_order(): void
    {
        $order = $this->order();
        $this->actingAs($order->user)->get(route('orders.cancel.create', $order))->assertOk();
        $this->post(route('orders.cancel.store', $order), [
            'cancel_reason' => 'other', 'cancel_detail' => 'Tôi không thể đến nhận món.',
        ])->assertRedirect(route('orders.show', $order));
        $this->assertSame('cancelled', $order->fresh()->status);
        $history = OrderHistory::where('order_id', $order->id)->sole();
        $this->assertSame((int) $order->user_id, (int) $history->changed_by);
        $this->assertSame('pending', $history->from_status);
        $this->assertStringContainsString('Tôi không thể đến nhận món.', $history->note);
    }

    public function test_stock_and_coupon_are_returned_only_once(): void
    {
        $order = $this->order();
        $category = Category::create(['name' => 'Test', 'slug' => 'test-' . Str::random(8)]);
        $item = MenuItem::create(['category_id' => $category->id, 'name' => 'Test meal', 'slug' => 'meal-' . Str::random(8), 'base_price' => 50000]);
        $stock = BranchMenuItem::create(['branch_id' => $order->branch_id, 'menu_item_id' => $item->id, 'is_available' => true, 'stock_qty' => 8]);
        $order->items()->create(['menu_item_id' => $item->id, 'name_snapshot' => $item->name, 'price_snapshot' => 50000, 'quantity' => 2, 'subtotal' => 100000]);
        $coupon = DB::table('coupons')->insertGetId(['code' => 'TEST-' . Str::random(8), 'type' => 'fixed', 'value' => 10000, 'used_count' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $order->update(['coupon_id' => $coupon]);
        DB::table('coupon_usages')->insert(['coupon_id' => $coupon, 'user_id' => $order->user_id, 'order_id' => $order->id, 'used_at' => now()]);
        $this->actingAs($order->user)->post(route('orders.cancel.store', $order), ['cancel_reason' => 'duplicate'])
            ->assertRedirect(route('orders.show', $order));
        $this->assertSame(10, $stock->fresh()->stock_qty);
        $this->assertSame(0, (int) DB::table('coupons')->where('id', $coupon)->value('used_count'));
        $this->assertDatabaseMissing('coupon_usages', ['order_id' => $order->id]);
        $this->post(route('orders.cancel.store', $order), ['cancel_reason' => 'duplicate'])->assertSessionHasErrors('cancel');
        $this->assertSame(10, $stock->fresh()->stock_qty);
        $this->assertSame(1, OrderHistory::where('order_id', $order->id)->count());
    }
}
