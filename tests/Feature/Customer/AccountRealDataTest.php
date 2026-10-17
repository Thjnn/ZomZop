<?php

namespace Tests\Feature\Customer;

use App\Models\Coupon;
use App\Models\CouponNotification;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class AccountRealDataTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_profile_shows_real_order_and_favorite_counts(): void
    {
        $branch   = $this->makeBranch();
        $customer = $this->makeUser('customer');
        foreach (range(1, 3) as $_) {
            $this->makeOrder($branch, ['user_id' => $customer->id]);
        }
        $customer->favoriteItems()->attach($this->makeMenuItem()->id);

        $this->actingAs($customer)->get('/profile')->assertOk()
            ->assertSeeInOrder(['3', 'Đơn hàng', '1', 'Món yêu thích']);
    }

    public function test_notifications_list_my_order_updates_and_coupons_only(): void
    {
        $branch   = $this->makeBranch();
        $customer = $this->makeUser('customer');
        $manager  = $this->makeUser('manager', $branch);
        $mine     = $this->makeOrder($branch, ['user_id' => $customer->id, 'order_code' => 'ZMZ-MINE01']);
        $other    = $this->makeOrder($branch, ['order_code' => 'ZMZ-OTHER1']);
        app(OrderStatusService::class)->transition($mine, 'confirmed', $manager);
        app(OrderStatusService::class)->transition($other, 'confirmed', $manager);

        $coupon = Coupon::create(['code' => 'GIAM20K', 'type' => 'fixed', 'value' => 20000, 'is_active' => true]);
        CouponNotification::create(['user_id' => $customer->id, 'coupon_id' => $coupon->id, 'channel' => 'email', 'status' => 'sent', 'sent_at' => now()]);

        $this->actingAs($customer)->get('/notifications')->assertOk()
            ->assertSee('Đơn ZMZ-MINE01: Đã xác nhận')
            ->assertSee('GIAM20K')
            ->assertDontSee('ZMZ-OTHER1');
    }

    public function test_notifications_need_login(): void
    {
        $this->get('/notifications')->assertRedirect(route('login'));
    }

    public function test_chatbot_is_for_customers_only(): void
    {
        $this->actingAs($this->makeUser('staff', $this->makeBranch()))
            ->postJson('/chatbot/message', ['message' => 'xin chào'])
            ->assertForbidden();
    }
}
