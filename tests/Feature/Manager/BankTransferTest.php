<?php

namespace Tests\Feature\Manager;

use App\Support\PaymentMethods;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankTransferTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.bank' => ['id' => 'vcb', 'name' => 'Vietcombank', 'account' => '0123456789', 'holder' => 'ZOMZOP']]);
    }

    public function test_bank_enabled_only_when_account_configured(): void
    {
        $this->assertContains('bank', PaymentMethods::enabled());
        config(['services.bank.account' => null]);
        $this->assertNotContains('bank', PaymentMethods::enabled());
    }

    public function test_customer_sees_vietqr_with_amount_and_note(): void
    {
        $order = $this->makeOrder($this->makeBranch(), ['order_code' => 'ZMZ-AB12CD', 'payment_method' => 'bank', 'total' => 125000]);

        $this->actingAs($order->user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('img.vietqr.io/image/vcb-0123456789-compact2.png?amount=125000&amp;addInfo=ZMZAB12CD', false);
    }

    public function test_manager_marks_bank_order_paid_and_qr_disappears(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['payment_method' => 'bank']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->patch(route('manager.orders.paid', $order))->assertRedirect();
        $this->assertSame('paid', $order->fresh()->payment_status);

        $this->actingAs($order->user)->get(route('orders.show', $order))->assertDontSee('img.vietqr.io');
    }

    public function test_cannot_mark_cash_or_other_branch_order_paid(): void
    {
        $branch = $this->makeBranch();
        $cash   = $this->makeOrder($branch);
        $other  = $this->makeOrder($this->makeBranch('Other'), ['payment_method' => 'bank']);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->patch(route('manager.orders.paid', $cash));
        $this->actingAs($manager)->patch(route('manager.orders.paid', $other));

        $this->assertSame('unpaid', $cash->fresh()->payment_status);
        $this->assertSame('unpaid', $other->fresh()->payment_status);
    }
}
