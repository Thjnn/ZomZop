<?php

namespace Tests\Feature\Manager;

use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class OrderStatusServiceTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private OrderStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderStatusService();
    }

    public function test_allowed_next_follows_the_flow(): void
    {
        $branch = $this->makeBranch();

        $this->assertSame(['confirmed', 'cancelled'], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'pending'])));
        $this->assertSame(['cooking', 'cancelled'], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'confirmed'])));
        $this->assertSame(['ready'], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'cooking'])));
        $this->assertSame(['completed'], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'ready'])));
        $this->assertSame([], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'completed'])));
        $this->assertSame([], $this->service->allowedNext($this->makeOrder($branch, ['status' => 'cancelled'])));
    }

    public function test_transition_updates_status_and_writes_history(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $order   = $this->makeOrder($branch);

        $updated = $this->service->transition($order, 'confirmed', $manager, 'OK');

        $this->assertSame('confirmed', $updated->status);
        $this->assertDatabaseHas('order_histories', [
            'order_id'    => $order->id,
            'from_status' => 'pending',
            'to_status'   => 'confirmed',
            'changed_by'  => $manager->id,
            'note'        => 'OK',
        ]);
    }

    public function test_skipping_steps_is_rejected(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch);

        try {
            $this->service->transition($order, 'completed', $this->makeUser('manager', $branch));
            $this->fail('Phải ném InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Không thể chuyển đơn', $e->getMessage());
        }

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseCount('order_histories', 0);
    }

    public function test_unknown_status_is_rejected(): void
    {
        $branch = $this->makeBranch();

        $this->expectException(InvalidArgumentException::class);
        $this->service->transition($this->makeOrder($branch), 'abc', $this->makeUser('manager', $branch));
    }

    /** Review Focus #2: bấm 2 lần với object cũ trong bộ nhớ */
    public function test_double_submit_with_stale_object_is_rejected(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $order   = $this->makeOrder($branch);  // object vẫn giữ status = pending

        $this->service->transition($order, 'confirmed', $manager);

        $this->expectException(InvalidArgumentException::class);
        $this->service->transition($order, 'confirmed', $manager); // DB đã là confirmed
    }

    public function test_completing_cash_order_marks_it_paid(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['status' => 'ready', 'payment_method' => 'cash']);

        $updated = $this->service->transition($order, 'completed', $this->makeUser('manager', $branch));

        $this->assertSame('paid', $updated->payment_status);
    }

    public function test_completing_momo_order_keeps_payment_status(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['status' => 'ready', 'payment_method' => 'momo']);

        $updated = $this->service->transition($order, 'completed', $this->makeUser('manager', $branch));

        $this->assertSame('unpaid', $updated->payment_status);
    }
}
