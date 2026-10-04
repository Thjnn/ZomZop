<?php

namespace Tests\Feature\Manager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDetailTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_shows_order_items_and_allowed_actions(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['order_code' => 'ZZSHOW0001']);
        $this->addItem($order, $this->makeMenuItem('Burger Gà Giòn'), 2);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get("/manager/orders/{$order->id}")
            ->assertOk()
            ->assertSee('ZZSHOW0001')
            ->assertSee('Burger Gà Giòn')
            ->assertSee('Xác nhận đơn')
            ->assertSee('Huỷ đơn');
    }

    /** Review Focus #1 */
    public function test_other_branch_order_returns_404(): void
    {
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');
        $order = $this->makeOrder($other);
        $manager = $this->makeUser('manager', $mine);

        $this->actingAs($manager)->get("/manager/orders/{$order->id}")->assertNotFound();
        $this->actingAs($manager)->patch("/manager/orders/{$order->id}/status", ['status' => 'confirmed'])->assertNotFound();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_confirm_order(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);

        $this->actingAs($this->makeUser('manager', $branch))
            ->from("/manager/orders/{$order->id}")
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertRedirect("/manager/orders/{$order->id}")
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_cancel_requires_reason(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);

        $this->actingAs($this->makeUser('manager', $branch))
            ->from("/manager/orders/{$order->id}")
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertSessionHasErrors('note');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancel_with_reason_saves_history_note(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);

        $this->actingAs($this->makeUser('manager', $branch))
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'cancelled', 'note' => 'Hết nguyên liệu']);

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertDatabaseHas('order_histories', ['order_id' => $order->id, 'to_status' => 'cancelled', 'note' => 'Hết nguyên liệu']);
    }

    /** Review Focus #3 */
    public function test_invalid_transition_shows_error_and_keeps_status(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)
            ->from("/manager/orders/{$order->id}")
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->actingAs($manager)
            ->from("/manager/orders/{$order->id}")
            ->patch("/manager/orders/{$order->id}/status", ['status' => 'abc'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->fresh()->status);
    }
}
