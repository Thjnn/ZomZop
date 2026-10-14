<?php

namespace Tests\Feature\Customer;

use App\Models\Coupon;
use App\Models\Address;
use App\Models\Order;
use App\Models\Review;
use Database\Seeders\CustomerCouponSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CustomerOrderFlowTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_orders_are_private_and_pages_render(): void
    {
        $order = $this->makeOrder($this->makeBranch());
        $this->addItem($order, $this->makeMenuItem(), 2);
        $this->actingAs($order->user)->get('/orders')->assertOk()->assertSee($order->order_code);
        $this->get('/orders/'.$order->id)->assertOk()->assertSee($order->order_code);
        $this->get('/orders/'.$order->id.'/reorder')->assertOk();
        $this->actingAs($this->makeUser('customer'))->get('/orders')->assertDontSee($order->order_code);
        foreach (['', '/reorder', '/review'] as $path) {
            $this->get('/orders/'.$order->id.$path)->assertNotFound();
        }
        $this->post('/orders/'.$order->id.'/reorder', ['replace_cart' => 1])->assertNotFound();
        $this->post('/orders/'.$order->id.'/review', ['rating' => 5])->assertNotFound();
    }

    public function test_only_completed_orders_can_be_reviewed_once(): void
    {
        $order = $this->makeOrder($this->makeBranch());
        $url = '/orders/'.$order->id.'/review';
        $this->actingAs($order->user)->post($url, ['rating' => 5])->assertForbidden();
        $order->update(['status' => 'completed']);
        $this->get($url)->assertOk();
        $this->post($url, ['rating' => 6])->assertSessionHasErrors('rating');
        $this->post($url, ['rating' => 5, 'comment' => 'Rất ngon'])->assertRedirect();
        $this->post($url, ['rating' => 1])->assertSessionHasErrors('rating');
        $this->assertSame(5, Review::sole()->rating);
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_reorder_uses_current_prices_and_skips_unavailable_items(): void
    {
        $order = $this->makeOrder($this->makeBranch());
        $item = $this->makeMenuItem();
        $hidden = $this->makeMenuItem('Món cũ');
        $this->addItem($order, $item, 2, 30000);
        $this->addItem($order, $hidden, 1);
        $hidden->update(['is_available' => false]);
        $this->actingAs($order->user)->post('/orders/'.$order->id.'/reorder', ['replace_cart' => 1])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('cart.items', fn ($items) => count($items) === 1 && $items[0]['price'] === 50000 && $items[0]['quantity'] === 2);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_closed_branch_does_not_replace_cart(): void
    {
        $order = $this->makeOrder($this->makeBranch());
        $order->branch->update(['is_active' => false]);
        $cart = ['branch_id' => 999, 'items' => []];
        $this->actingAs($order->user)->withSession(['cart' => $cart])
            ->post('/orders/'.$order->id.'/reorder', ['replace_cart' => 1])
            ->assertSessionHasErrors('order')->assertSessionHas('cart', $cart);
    }

    public function test_shipping_preview_matches_persisted_order_and_ignores_client_totals(): void
    {
        $this->seed(CustomerCouponSeeder::class);
        $branch = $this->makeBranch();
        $item = $this->makeMenuItem();
        $customer = $this->makeUser('customer');
        $address = Address::create(['user_id' => $customer->id, 'label' => 'Nhà', 'name' => 'Khách test', 'phone' => '0901234567', 'address' => '123 Test']);
        $cart = ['branch_id' => $branch->id, 'items' => [['id' => $item->id, 'name' => $item->name, 'price' => 50000, 'quantity' => 2, 'image' => '']]];
        $this->actingAs($customer)->withSession(['cart' => $cart])
            ->get('/checkout?type=delivery&delivery_speed=standard&coupon=SHIPFREE')
            ->assertOk()->assertViewHas('shippingFee', 15000)->assertViewHas('discount', 15000);
        $this->post('/checkout/store', ['type' => 'delivery', 'delivery_speed' => 'standard', 'payment_method' => 'cash', 'address_id' => $address->id, 'coupon_code' => 'SHIPFREE', 'total' => 1, 'shipping_fee' => 0])->assertRedirect();
        $order = Order::sole();
        $this->assertSame(100000, $order->total);
        $this->assertSame(15000, $order->shipping_fee);
        $this->assertSame(15000, $order->discount);
        $this->assertSame(1, $order->histories()->count());
    }

    public function test_shipping_coupon_rejects_takeaway_and_seeder_preserves_usage(): void
    {
        $this->seed(CustomerCouponSeeder::class);
        $coupon = Coupon::where('code', 'SHIPFREE')->firstOrFail();
        $coupon->update(['used_count' => 7]);
        $this->seed(CustomerCouponSeeder::class);
        $this->assertSame(7, $coupon->fresh()->used_count);
        $item = $this->makeMenuItem();
        $branch = $this->makeBranch();
        $this->actingAs($this->makeUser('customer'))->withSession(['cart' => ['branch_id' => $branch->id, 'items' => [['id' => $item->id, 'name' => $item->name, 'price' => 50000, 'quantity' => 2, 'image' => '']]]])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash', 'coupon_code' => 'SHIPFREE'])
            ->assertSessionHasErrors('coupon_code');
        $this->assertDatabaseCount('orders', 0);
    }
}
