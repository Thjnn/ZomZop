<?php

namespace Tests\Feature\Manager;

use App\Services\BranchStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_today_counts_only_own_branch_and_completed_revenue(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');

        $this->makeOrder($mine, ['status' => 'completed', 'total' => 120000]);
        $this->makeOrder($mine, ['status' => 'completed', 'total' => 80000]);
        $this->makeOrder($mine, ['status' => 'pending',   'total' => 999000]);
        $this->makeOrder($mine, ['status' => 'cancelled', 'total' => 50000]);
        $this->makeOrder($other, ['status' => 'completed', 'total' => 777000]);

        $this->assertSame(
            ['revenue' => 200000, 'orders' => 4, 'pending' => 1, 'cancelled' => 1],
            (new BranchStats())->today($mine->id)
        );
    }

    /** Ô "Chờ xác nhận" phải khớp danh sách đơn chờ bên dưới (gồm cả đơn tồn từ hôm trước) */
    public function test_pending_count_includes_leftover_orders_from_previous_days(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-10-03 21:00'));
        $this->makeOrder($branch, ['status' => 'pending']);
        $this->travelTo(Carbon::parse('2026-10-04 09:00'));
        $this->makeOrder($branch, ['status' => 'pending']);

        $this->assertSame(2, (new BranchStats())->today($branch->id)['pending']);
    }

    /** Review Focus #4: đơn lúc 6h sáng giờ VN vẫn là "hôm nay" */
    public function test_early_morning_vietnam_time_counts_as_today(): void
    {
        $branch = $this->makeBranch();

        $this->travelTo(Carbon::parse('2026-10-04 06:30', 'Asia/Ho_Chi_Minh'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 100000]);

        $this->travelTo(Carbon::parse('2026-10-04 20:00', 'Asia/Ho_Chi_Minh'));
        $this->assertSame(100000, (new BranchStats())->today($branch->id)['revenue']);
    }

    public function test_revenue_last_days_fills_missing_days_with_zero(): void
    {
        $branch = $this->makeBranch();

        $this->travelTo(Carbon::parse('2026-10-02 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 50000]);
        $this->travelTo(Carbon::parse('2026-10-04 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 70000]);

        $this->assertSame([
            '2026-10-02' => 50000,
            '2026-10-03' => 0,
            '2026-10-04' => 70000,
        ], (new BranchStats())->revenueLastDays($branch->id, 3));
    }

    public function test_top_items_today_sums_quantity(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Bò');
        $coke   = $this->makeMenuItem('Coca');

        $o1 = $this->makeOrder($branch, ['status' => 'completed']);
        $this->addItem($o1, $burger, 2);
        $this->addItem($o1, $coke, 1, 15000);
        $o2 = $this->makeOrder($branch, ['status' => 'completed']);
        $this->addItem($o2, $burger, 3);
        $cancelled = $this->makeOrder($branch, ['status' => 'cancelled']);
        $this->addItem($cancelled, $coke, 10, 15000);

        $top = (new BranchStats())->topItemsToday($branch->id);

        $this->assertSame('Burger Bò', $top[0]->name);
        $this->assertSame(5, (int) $top[0]->qty);
        $this->assertSame(250000, (int) $top[0]->revenue);
        $this->assertSame(1, (int) $top[1]->qty); // đơn huỷ không tính
    }

    public function test_dashboard_page_shows_numbers(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 125000]);
        $this->makeOrder($branch, ['status' => 'pending', 'order_code' => 'ZZPENDING1']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager')
            ->assertOk()
            ->assertSee('125.000đ')
            ->assertSee('ZZPENDING1');
    }
}
