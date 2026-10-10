<?php

namespace Tests\Feature\Admin;

use App\Services\BranchReport;
use App\Services\BranchStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ChainReportTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function seedOrders(): array
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $this->makeBranch('Chi nhánh Rỗng');
        $this->makeOrder($a, ['status' => 'completed', 'total' => 100000]);
        $this->makeOrder($a, ['status' => 'cancelled', 'total' => 500000]);
        $this->makeOrder($b, ['status' => 'completed', 'total' => 300000]);

        return [$a, $b];
    }

    public function test_summary_null_branch_sums_whole_chain_excluding_cancelled(): void
    {
        [$a] = $this->seedOrders();
        $report = app(BranchReport::class);

        $chain = $report->summary(null, today(), today());
        $this->assertSame(400000, $chain['revenue']);
        $this->assertSame(3, $chain['orders']);
        $this->assertSame(1, $chain['cancelled']);

        $this->assertSame(100000, $report->summary($a->id, today(), today())['revenue']); // manager vẫn như cũ
    }

    public function test_by_branch_sorted_by_revenue_and_includes_empty_branch(): void
    {
        $this->seedOrders();

        $rows = app(BranchReport::class)->byBranch(today(), today());

        $this->assertSame(['Chi nhánh Beta', 'Chi nhánh Alpha', 'Chi nhánh Rỗng'], $rows->pluck('branch.name')->all());
        $this->assertSame([300000, 100000, 0], $rows->pluck('revenue')->all());
        $this->assertSame(1, $rows[1]['cancelled']);
    }

    public function test_stats_null_branch(): void
    {
        $this->seedOrders();

        $counts = app(BranchStats::class)->statusCounts(null, 'today');
        $this->assertSame(3, $counts['total']);
        $this->assertSame(400000, $counts['revenue']);
    }

    public function test_dashboard_shows_chain_numbers_and_branch_ranking(): void
    {
        $this->seedOrders();

        $this->actingAs($this->makeUser('admin'))->get('/admin')
            ->assertOk()
            ->assertSee('400.000')
            ->assertSeeInOrder(['Chi nhánh Beta', 'Chi nhánh Alpha']);
    }
}
