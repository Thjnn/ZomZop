<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CheckoutCouponTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** Giỏ 2 món × 60.000 = 120.000đ */
    private function checkout($customer, ?string $code)
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $item->update(['base_price' => 60000]);

        return $this->actingAs($customer)
            ->withSession(['cart' => ['branch_id' => $branch->id, 'items' => [
                ['id' => $item->id, 'name' => $item->name, 'price' => 60000, 'quantity' => 2, 'image' => ''],
            ]]])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash', 'coupon_code' => $code]);
    }

    private function coupon(array $attrs = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'GIAM10', 'type' => 'percent', 'value' => 10, 'min_order_value' => 0,
            'max_uses' => 0, 'max_uses_per_user' => 1, 'is_active' => true,
        ], $attrs));
    }

    public function test_valid_coupon_discounts_order_and_records_usage(): void
    {
        $coupon   = $this->coupon();
        $customer = $this->makeUser('customer');

        $this->checkout($customer, 'giam10')->assertRedirect();

        $order = Order::sole();
        $this->assertSame($coupon->id, (int) $order->coupon_id);
        $this->assertSame(12000, (int) $order->discount);
        $this->assertSame(108000, (int) $order->total);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertSame(1, CouponUsage::where('order_id', $order->id)->count());
    }

    public function test_no_coupon_keeps_old_behaviour(): void
    {
        $this->checkout($this->makeUser('customer'), null)->assertRedirect();

        $this->assertSame(120000, (int) Order::sole()->total);
        $this->assertSame(0, (int) Order::sole()->discount);
    }

    public function test_invalid_cases_do_not_create_order(): void
    {
        $customer = $this->makeUser('customer');
        $cases = [
            ['KHONGCO', []],
            ['TATROI', ['code' => 'TATROI', 'is_active' => false]],
            ['HETHAN', ['code' => 'HETHAN', 'expired_at' => now()->subDay()]],
            ['HETLUOT', ['code' => 'HETLUOT', 'max_uses' => 5, 'used_count' => 5]],
            ['DONTOITHIEU', ['code' => 'DONTOITHIEU', 'min_order_value' => 200000]],
        ];

        foreach ($cases as [$code, $attrs]) {
            if ($attrs) {
                $this->coupon($attrs);
            }
            $this->checkout($customer, $code)->assertSessionHasErrors('coupon_code');
        }

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_per_user_limit(): void
    {
        $this->coupon(['max_uses_per_user' => 1]);
        $customer = $this->makeUser('customer');

        $this->checkout($customer, 'GIAM10')->assertRedirect();
        $this->checkout($customer, 'GIAM10')->assertSessionHasErrors('coupon_code');

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_cancelled_order_releases_coupon_usage(): void
    {
        $coupon   = $this->coupon();
        $customer = $this->makeUser('customer');
        $this->checkout($customer, 'GIAM10');
        $order = Order::sole();

        app(OrderStatusService::class)->transition($order, 'cancelled', $this->makeUser('manager', $order->branch));

        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertSame(0, CouponUsage::count());
        // Khách dùng lại được
        $this->checkout($customer, 'GIAM10')->assertRedirect();
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_checkout_page_previews_discount(): void
    {
        $this->coupon();
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $item->update(['base_price' => 60000]);

        $this->actingAs($this->makeUser('customer'))
            ->withSession(['cart' => ['branch_id' => $branch->id, 'items' => [
                ['id' => $item->id, 'name' => $item->name, 'price' => 60000, 'quantity' => 2, 'image' => ''],
            ]]])
            ->get('/checkout?coupon=GIAM10')
            ->assertOk()
            ->assertSee('-12.000')
            ->assertSee('108.000');
    }

    public function test_coupons_page_shows_only_public_active(): void
    {
        $this->coupon(['code' => 'CONGKHAI']);
        $this->coupon(['code' => 'RIENGTU', 'is_public' => false]);
        $this->coupon(['code' => 'DATAT', 'is_active' => false]);

        $this->get('/coupons')->assertOk()->assertSee('CONGKHAI')->assertDontSee('RIENGTU')->assertDontSee('DATAT');
    }
}
