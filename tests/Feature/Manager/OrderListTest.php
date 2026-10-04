<?php

namespace Tests\Feature\Manager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderListTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_only_own_branch_orders(): void
    {
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');
        $this->makeOrder($mine, ['order_code' => 'ZZMINE0001']);
        $this->makeOrder($other, ['order_code' => 'ZZOTHER001']);

        $this->actingAs($this->makeUser('manager', $mine))
            ->get('/manager/orders')
            ->assertOk()
            ->assertSee('ZZMINE0001')
            ->assertDontSee('ZZOTHER001');
    }

    public function test_filters_by_status(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['order_code' => 'ZZPEND0001', 'status' => 'pending']);
        $this->makeOrder($branch, ['order_code' => 'ZZDONE0001', 'status' => 'completed']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/orders?status=completed')
            ->assertSee('ZZDONE0001')
            ->assertDontSee('ZZPEND0001');
    }

    public function test_filters_by_date(): void
    {
        $branch = $this->makeBranch();
        $this->travelTo(Carbon::parse('2026-10-01 10:00'));
        $this->makeOrder($branch, ['order_code' => 'ZZOLD00001']);
        $this->travelTo(Carbon::parse('2026-10-04 10:00'));
        $this->makeOrder($branch, ['order_code' => 'ZZNEW00001']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/orders?date=2026-10-01')
            ->assertSee('ZZOLD00001')
            ->assertDontSee('ZZNEW00001');
    }

    public function test_searches_by_order_code_or_pickup_code(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['order_code' => 'ZZFIND0001', 'pickup_code' => 'B07']);
        $this->makeOrder($branch, ['order_code' => 'ZZSKIP0001', 'pickup_code' => 'C01']);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->get('/manager/orders?q=FIND')
            ->assertSee('ZZFIND0001')->assertDontSee('ZZSKIP0001');

        $this->actingAs($manager)->get('/manager/orders?q=B07')
            ->assertSee('ZZFIND0001')->assertDontSee('ZZSKIP0001');
    }

    /** Review Focus #5 + lỗi #3: lọc bậy → ở lại trang Đơn hàng, báo lỗi, bỏ qua giá trị sai */
    public function test_invalid_filters_stay_on_list_with_errors(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['order_code' => 'ZZKEEP0001', 'pickup_code' => 'K01']);
        $this->makeOrder($branch, ['order_code' => 'ZZDROP0001', 'pickup_code' => 'D01']);

        $this->actingAs($this->makeUser('manager', $branch))
            ->from('/manager')
            ->get('/manager/orders?status=xyz&date=khong-phai-ngay&q=KEEP')
            ->assertOk()
            ->assertSee('Trạng thái lọc không hợp lệ.')
            ->assertSee('Ngày lọc không hợp lệ.')
            ->assertSee('ZZKEEP0001')        // bộ lọc hợp lệ (q) vẫn được áp dụng
            ->assertDontSee('ZZDROP0001');
    }
}
