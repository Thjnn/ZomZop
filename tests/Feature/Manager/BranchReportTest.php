<?php

namespace Tests\Feature\Manager;

use App\Services\BranchReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BranchReportTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private function at(string $time, callable $fn)
    {
        $this->travelTo(Carbon::parse($time));
        return $fn();
    }

    private function range(): array
    {
        return [Carbon::parse('2026-10-01'), Carbon::parse('2026-10-03')];
    }

    /** Review Focus #2, #3 */
    public function test_summary_counts_range_inclusive_and_own_branch_only(): void
    {
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');
        $this->at('2026-09-30 23:59', fn () => $this->makeOrder($mine, ['status' => 'completed', 'total' => 999000]));
        $this->at('2026-10-01 00:01', fn () => $this->makeOrder($mine, ['status' => 'completed', 'total' => 100000]));
        $this->at('2026-10-03 23:30', fn () => $this->makeOrder($mine, ['status' => 'completed', 'total' => 50000]));
        $this->at('2026-10-02 12:00', fn () => $this->makeOrder($mine, ['status' => 'cancelled', 'total' => 70000]));
        $this->at('2026-10-02 12:00', fn () => $this->makeOrder($mine, ['status' => 'pending', 'total' => 30000]));
        $this->at('2026-10-02 12:00', fn () => $this->makeOrder($other, ['status' => 'completed', 'total' => 888000]));

        [$from, $to] = $this->range();
        $this->assertSame([
            'revenue' => 150000, 'orders' => 4, 'completed' => 2, 'cancelled' => 1,
            'avg_order' => 75000, 'cancel_rate' => 25.0,
        ], (new BranchReport())->summary($mine->id, $from, $to));
    }

    /** Review Focus #5 */
    public function test_empty_range_has_zeros(): void
    {
        $branch = $this->makeBranch();
        [$from, $to] = $this->range();

        $this->assertSame([
            'revenue' => 0, 'orders' => 0, 'completed' => 0, 'cancelled' => 0, 'avg_order' => 0, 'cancel_rate' => 0.0,
        ], (new BranchReport())->summary($branch->id, $from, $to));
    }

    public function test_daily_fills_every_day(): void
    {
        $branch = $this->makeBranch();
        $this->at('2026-10-01 09:00', fn () => $this->makeOrder($branch, ['status' => 'completed', 'total' => 40000]));
        $this->at('2026-10-03 09:00', fn () => $this->makeOrder($branch, ['status' => 'cancelled', 'total' => 40000]));

        [$from, $to] = $this->range();
        $this->assertSame([
            '2026-10-01' => ['revenue' => 40000, 'orders' => 1],
            '2026-10-02' => ['revenue' => 0, 'orders' => 0],
            '2026-10-03' => ['revenue' => 0, 'orders' => 1],
        ], (new BranchReport())->daily($branch->id, $from, $to));
    }

    public function test_by_type_and_payment_count_completed_only(): void
    {
        $branch = $this->makeBranch();
        $this->at('2026-10-02 09:00', function () use ($branch) {
            $this->makeOrder($branch, ['status' => 'completed', 'type' => 'delivery', 'payment_method' => 'momo', 'total' => 60000]);
            $this->makeOrder($branch, ['status' => 'completed', 'type' => 'takeaway', 'payment_method' => 'cash', 'total' => 40000]);
            $this->makeOrder($branch, ['status' => 'cancelled', 'type' => 'delivery', 'payment_method' => 'momo', 'total' => 99000]);
        });

        [$from, $to] = $this->range();
        $report = new BranchReport();
        $this->assertSame([
            'takeaway' => ['orders' => 1, 'revenue' => 40000],
            'delivery' => ['orders' => 1, 'revenue' => 60000],
        ], $report->byType($branch->id, $from, $to));
        $this->assertSame([
            'cash'  => ['orders' => 1, 'revenue' => 40000],
            'momo'  => ['orders' => 1, 'revenue' => 60000],
            'vnpay' => ['orders' => 0, 'revenue' => 0],
        ], $report->byPayment($branch->id, $from, $to));
    }

    public function test_top_items_in_range(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $coke   = $this->makeMenuItem('Coca');
        $this->at('2026-10-02 09:00', function () use ($branch, $burger, $coke) {
            $o = $this->makeOrder($branch, ['status' => 'completed']);
            $this->addItem($o, $burger, 3);
            $this->addItem($o, $coke, 5, 10000);
            $x = $this->makeOrder($branch, ['status' => 'cancelled']);
            $this->addItem($x, $burger, 50);
        });
        $this->at('2026-10-09 09:00', fn () => $this->addItem($this->makeOrder($branch, ['status' => 'completed']), $burger, 99));

        [$from, $to] = $this->range();
        $top = (new BranchReport())->topItems($branch->id, $from, $to);

        $this->assertSame(['Coca', 'Burger'], $top->pluck('name')->all());
        $this->assertSame([5, 3], $top->pluck('qty')->map(fn ($v) => (int) $v)->all());
    }
}
