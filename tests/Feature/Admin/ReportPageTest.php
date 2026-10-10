<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_chain_report_with_branch_comparison(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $this->makeOrder($a, ['status' => 'completed', 'total' => 100000]);
        $this->makeOrder($b, ['status' => 'completed', 'total' => 250000]);

        $this->actingAs($this->makeUser('admin'))->get('/admin/reports')
            ->assertOk()
            ->assertSee('350.000')
            ->assertSeeInOrder(['So sánh chi nhánh', 'Chi nhánh Beta', 'Chi nhánh Alpha']);
    }

    public function test_filter_one_branch(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $this->makeOrder($a, ['status' => 'completed', 'total' => 100000]);
        $this->makeOrder($b, ['status' => 'completed', 'total' => 250000]);

        $this->actingAs($this->makeUser('admin'))->get("/admin/reports?branch_id={$a->id}")
            ->assertOk()->assertSee('100.000')->assertDontSee('350.000')->assertDontSee('So sánh chi nhánh');
    }

    public function test_reversed_range_shows_error_and_default_range(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/admin/reports?from=2026-10-10&to=2026-10-01')
            ->assertOk()
            ->assertSee('Ngày kết thúc phải từ ngày bắt đầu trở đi.');
    }

    public function test_export_downloads_xlsx(): void
    {
        $this->makeOrder($this->makeBranch(), ['status' => 'completed']);

        $this->actingAs($this->makeUser('admin'))->get('/admin/reports/export')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_manager_report_still_works(): void
    {
        $branch = $this->makeBranch();
        $this->actingAs($this->makeUser('manager', $branch))->get('/manager/reports?from=2026-10-10&to=2026-10-01')
            ->assertOk()->assertSee('Ngày kết thúc phải từ ngày bắt đầu trở đi.');
    }
}
