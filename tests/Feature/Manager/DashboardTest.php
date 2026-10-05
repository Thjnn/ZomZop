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

    public function test_status_counts_only_own_branch_and_completed_revenue(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');

        $this->makeOrder($mine, ['status' => 'completed', 'total' => 120000]);
        $this->makeOrder($mine, ['status' => 'completed', 'total' => 80000]);
        $this->makeOrder($mine, ['status' => 'pending',   'total' => 999000]);
        $this->makeOrder($mine, ['status' => 'cooking']);
        $this->makeOrder($mine, ['status' => 'cancelled', 'total' => 50000]);
        $this->makeOrder($other, ['status' => 'completed', 'total' => 777000]);

        $this->assertSame([
            'pending' => 1, 'confirmed' => 0, 'cooking' => 1, 'ready' => 0,
            'completed' => 2, 'cancelled' => 1,
            'total' => 5, 'revenue' => 200000,
        ], (new BranchStats())->statusCounts($mine->id, 'all'));
    }

    public function test_status_counts_filter_by_period(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-09-20 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 10000]);
        $this->travelTo(Carbon::parse('2026-10-01 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 20000]);
        $this->travelTo(Carbon::parse('2026-10-04 06:30')); // sáng sớm giờ VN vẫn là hôm nay
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 40000]);
        $this->travelTo(Carbon::parse('2026-10-04 20:00'));

        $stats = new BranchStats();
        $this->assertSame(40000, $stats->statusCounts($branch->id, 'today')['revenue']);
        $this->assertSame(60000, $stats->statusCounts($branch->id, 'month')['revenue']);
        $this->assertSame(70000, $stats->statusCounts($branch->id, 'all')['revenue']);
    }

    public function test_series_week_fills_missing_days_with_zero(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-09-28 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 50000]);
        $this->makeOrder($branch, ['status' => 'cancelled', 'total' => 99000]);
        $this->travelTo(Carbon::parse('2026-10-04 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 70000]);

        $week = (new BranchStats())->series($branch->id, 'week');

        $this->assertSame(['28/09', '29/09', '30/09', '01/10', '02/10', '03/10', '04/10'], $week['labels']);
        $this->assertSame([2, 0, 0, 0, 0, 0, 1], $week['orders']);
        $this->assertSame([50000, 0, 0, 0, 0, 0, 70000], $week['revenue']);
    }

    public function test_series_year_groups_by_month(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2025-12-31 10:00')); // năm trước, không tính
        $this->makeOrder($branch, ['status' => 'completed']);
        $this->travelTo(Carbon::parse('2026-02-03 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 30000]);
        $this->travelTo(Carbon::parse('2026-02-25 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 45000]);
        $this->travelTo(Carbon::parse('2026-10-04 10:00'));

        $year = (new BranchStats())->series($branch->id, 'year');

        $this->assertCount(12, $year['labels']);
        $this->assertSame('T2', $year['labels'][1]);
        $this->assertSame(2, $year['orders'][1]);
        $this->assertSame(75000, $year['revenue'][1]);
        $this->assertSame(2, array_sum($year['orders']));
    }

    public function test_series_month_has_one_point_per_day(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-09-15 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 30000]);

        $month = (new BranchStats())->series($branch->id, 'month');

        $this->assertCount(30, $month['labels']);
        $this->assertSame(1, $month['orders'][14]);
        $this->assertSame(30000, $month['revenue'][14]);
    }

    public function test_dashboard_page_shows_numbers_and_recent_orders(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $branch = $this->makeBranch('Quận 1');
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 125000]);
        $this->makeOrder($branch, ['status' => 'pending', 'order_code' => 'ZZPENDING1']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager')
            ->assertOk()
            ->assertSee('Quận 1')
            ->assertSee('125.000đ')
            ->assertSee('ZZPENDING1');
    }

    public function test_dashboard_lists_pending_orders_with_approve_action(): void
    {
        $branch = $this->makeBranch();
        $other  = $this->makeBranch('Other');
        $mine   = $this->makeOrder($branch, ['status' => 'pending', 'order_code' => 'ZZWAIT0001']);
        $this->makeOrder($branch, ['status' => 'confirmed', 'order_code' => 'ZZCONF0001']);
        $this->makeOrder($other, ['status' => 'pending', 'order_code' => 'ZZOTHER001']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager')
            ->assertViewHas('pendingOrders', fn ($orders) => $orders->pluck('order_code')->all() === ['ZZWAIT0001'])
            ->assertSee(route('manager.orders.status', $mine), false)
            ->assertSee('Duyệt');
    }

    public function test_approving_from_dashboard_returns_to_dashboard(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['status' => 'pending']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->from('/manager')
            ->patch(route('manager.orders.status', $order), ['status' => 'confirmed'])
            ->assertRedirect('/manager')
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_dashboard_period_filter(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-09-01 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 111000]);
        $this->travelTo(Carbon::parse('2026-10-04 12:00'));
        $manager = $this->makeUser('manager', $branch);

        $revenue = fn (int $expected) => fn ($counts) => $counts['revenue'] === $expected;

        $this->actingAs($manager)->get('/manager')->assertViewHas('counts', $revenue(111000));
        $this->actingAs($manager)->get('/manager?period=today')->assertViewHas('counts', $revenue(0));
        $this->actingAs($manager)->get('/manager?period=bogus')
            ->assertOk()
            ->assertViewHas('period', 'all')
            ->assertViewHas('counts', $revenue(111000));
    }
}
