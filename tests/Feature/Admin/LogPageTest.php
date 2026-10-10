<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class LogPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_logs_newest_first_with_filter(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);
        AdminLog::record('branch.create', null, ['after' => ['name' => 'Chi nhánh Cũ']]);
        AdminLog::record('coupon.create', null, ['after' => ['code' => 'MAMOI']]);

        $this->get('/admin/logs')->assertOk()->assertSeeInOrder(['coupon.create', 'branch.create']);
        $this->get('/admin/logs?action=coupon')->assertOk()->assertSee('MAMOI')->assertDontSee('Chi nhánh Cũ');
    }

    public function test_manager_cannot_see_logs(): void
    {
        $this->actingAs($this->makeUser('manager', $this->makeBranch()))->get('/admin/logs')->assertForbidden();
    }
}
