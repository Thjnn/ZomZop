<?php

namespace Tests\Feature\Manager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));
    }

    public function test_default_range_is_last_7_days(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-09-29 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 123000]);
        $this->travelTo(Carbon::parse('2026-09-28 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 777000]);
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/reports')
            ->assertOk()
            ->assertSee('value="2026-09-29"', false)
            ->assertSee('value="2026-10-05"', false)
            ->assertSee('123.000đ')
            ->assertDontSee('777.000đ');
    }

    public function test_custom_range(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-09-01 10:00'));
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 777000]);
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/reports?from=2026-09-01&to=2026-09-30')
            ->assertSee('777.000đ');
    }

    /** Review Focus #1 */
    public function test_bad_ranges_show_error_and_fall_back(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->get('/manager/reports?from=abc&to=2026-10-01')
            ->assertOk()->assertSee('Ngày bắt đầu không hợp lệ.')->assertSee('value="2026-09-29"', false);

        $this->actingAs($manager)->get('/manager/reports?from=2026-10-05&to=2026-10-01')
            ->assertOk()->assertSee('Ngày kết thúc phải từ ngày bắt đầu trở đi.');

        $this->actingAs($manager)->get('/manager/reports?from=2020-01-01&to=2026-10-01')
            ->assertOk()->assertSee('Khoảng ngày tối đa 366 ngày.');
    }
}
