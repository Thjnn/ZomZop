<?php

namespace Tests\Feature\Customer;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CheckoutAddressVoucherTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function cart(): array
    {
        $branch = $this->makeBranch();
        $item = $this->makeMenuItem();
        return ['branch_id' => $branch->id, 'items' => [['id' => $item->id, 'name' => $item->name, 'price' => 50000, 'quantity' => 3, 'image' => '/images/test.jpg']]];
    }

    private function address($user, string $name = 'Người nhận'): Address
    {
        return Address::create(['user_id' => $user->id, 'label' => 'Nhà', 'name' => $name, 'phone' => '0901234567', 'address' => '123 Đường Kiểm Thử', 'is_default' => true]);
    }

    private function coupon(string $code, array $attributes = []): Coupon
    {
        return Coupon::create(array_merge(['code' => $code, 'type' => 'fixed', 'scope' => 'food', 'value' => 10000, 'min_order_value' => 0, 'max_uses' => 0, 'max_uses_per_user' => 1, 'is_active' => true, 'is_public' => true], $attributes));
    }

    public function test_delivery_without_address_redirects_and_returns_after_save(): void
    {
        $user = $this->makeUser('customer');
        $cart = $this->cart();
        $this->actingAs($user)->withSession(['cart' => $cart])
            ->get('/checkout?type=delivery&delivery_speed=express')
            ->assertRedirect(route('addresses.index', ['from' => 'checkout']))->assertSessionHas('cart', $cart);
        $this->get('/addresses?from=checkout')->assertOk()->assertSee('name="from"', false);
        $this->post('/addresses?from=checkout', ['from' => 'checkout', 'label' => 'Nhà', 'name' => 'Khách thử', 'phone' => '0901234567', 'address' => '123 Test'])
            ->assertRedirectContains('/checkout');
        $address = Address::sole();
        $this->assertSame($user->id, $address->user_id);
        $this->get('/checkout?type=delivery&delivery_speed=express&address_id='.$address->id)
            ->assertOk()->assertViewHas('selectedAddress', fn ($value) => $value->id === $address->id)
            ->assertViewHas('shippingFee', 30000)->assertDontSee('name="delivery_address"', false);
    }

    public function test_checkout_does_not_show_other_users_addresses(): void
    {
        $user = $this->makeUser('customer');
        $own = $this->address($user, 'Địa chỉ của tôi');
        $other = $this->address($this->makeUser('customer'), 'Bí mật người khác');
        $this->actingAs($user)->withSession(['cart' => $this->cart()])
            ->get('/checkout?type=delivery&address_id='.$other->id)
            ->assertOk()->assertDontSee('Bí mật người khác')
            ->assertViewHas('selectedAddress', fn ($value) => $value->id === $own->id);
        $this->post('/checkout/store', ['type' => 'delivery', 'address_id' => $other->id, 'payment_method' => 'cash'])
            ->assertSessionHasErrors('address_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_snapshots_saved_address_and_ignores_free_text(): void
    {
        $user = $this->makeUser('customer');
        $address = $this->address($user, 'Khách thử');
        $this->actingAs($user)->withSession(['cart' => $this->cart()])
            ->post('/checkout/store', ['type' => 'delivery', 'address_id' => $address->id, 'delivery_address' => 'Dữ liệu giả', 'payment_method' => 'cash'])
            ->assertRedirect();
        $order = Order::sole();
        $this->assertStringContainsString('Khách thử', $order->delivery_address);
        $this->assertStringContainsString('0901234567', $order->delivery_address);
        $this->assertStringNotContainsString('Dữ liệu giả', $order->delivery_address);
        $snapshot = $order->delivery_address;
        $address->delete();
        $this->assertSame($snapshot, $order->fresh()->delivery_address);
    }

    public function test_voucher_picker_explains_ineligible_codes_and_hides_private_codes(): void
    {
        $this->coupon('PUBLIC10');
        $this->coupon('BIGORDER', ['min_order_value' => 200000]);
        $this->coupon('PRIVATE10', ['is_public' => false]);
        $this->actingAs($this->makeUser('customer'))->withSession(['cart' => $this->cart()])
            ->get('/checkout?type=takeaway')->assertOk()->assertSee('PUBLIC10')->assertSee('BIGORDER')
            ->assertSee('Đơn tối thiểu 200.000đ')->assertDontSee('PRIVATE10');
        $this->get('/checkout?type=takeaway&coupon=PRIVATE10')
            ->assertOk()->assertViewHas('couponCode', 'PRIVATE10')->assertViewHas('discount', 10000);
    }

    public function test_editing_an_address_from_checkout_returns_to_checkout(): void
    {
        $user = $this->makeUser('customer');
        $address = $this->address($user);
        $this->actingAs($user)->put('/addresses/'.$address->id.'?from=checkout', ['from' => 'checkout', 'label' => 'Nhà', 'name' => 'Người mới', 'phone' => '0901234567', 'address' => 'Địa chỉ mới'])
            ->assertRedirectContains('/checkout');
        $this->assertSame('Địa chỉ mới', $address->fresh()->address);
    }

    public function test_takeaway_does_not_require_an_address(): void
    {
        $this->actingAs($this->makeUser('customer'))->withSession(['cart' => $this->cart()])
            ->get('/checkout?type=takeaway')->assertOk();
        $this->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash'])->assertRedirect();
        $this->assertNull(Order::sole()->delivery_address);
    }
}
