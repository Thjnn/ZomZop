<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class BranchManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(array $over = []): array
    {
        return array_merge([
            'name' => 'ZomZop Mỹ Tho', 'address' => '12 Ấp Bắc, Mỹ Tho', 'phone' => '0273123456',
            'open_time' => '08:00', 'close_time' => '22:00',
        ], $over);
    }

    public function test_manager_cannot_create_branch(): void
    {
        $branch = $this->makeBranch();
        $this->actingAs($this->makeUser('manager', $branch))
            ->post('/admin/branches', $this->validData())
            ->assertForbidden();
    }

    public function test_admin_lists_branches(): void
    {
        $this->makeBranch('ZomZop Bến Tre');
        $this->actingAs($this->makeUser('admin'))
            ->get('/admin/branches')->assertOk()->assertSee('ZomZop Bến Tre');
    }

    public function test_admin_creates_branch_and_it_is_logged(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/branches', $this->validData())
            ->assertRedirect(route('admin.branches.index'));

        $branch = Branch::where('name', 'ZomZop Mỹ Tho')->sole();
        $this->assertTrue((bool) $branch->is_active);
        $this->assertSame('branch.create', AdminLog::sole()->action);
    }

    public function test_validation_rejects_duplicate_name_and_same_hours(): void
    {
        $this->makeBranch('ZomZop Mỹ Tho');

        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/branches', $this->validData(['close_time' => '08:00']))
            ->assertSessionHasErrors(['name', 'close_time']);
    }

    public function test_update_logs_before_and_after(): void
    {
        $branch = $this->makeBranch('Tên cũ');

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/branches/{$branch->id}", $this->validData(['name' => 'Tên mới']))
            ->assertRedirect(route('admin.branches.index'));

        $this->assertSame('Tên mới', $branch->fresh()->name);
        $log = AdminLog::where('action', 'branch.update')->sole();
        $this->assertSame('Tên cũ', $log->changes['before']['name']);
        $this->assertSame('Tên mới', $log->changes['after']['name']);
    }

    public function test_toggle_closes_and_reopens_branch(): void
    {
        $branch = $this->makeBranch();
        $admin  = $this->makeUser('admin');

        $this->actingAs($admin)->patch("/admin/branches/{$branch->id}/toggle")->assertRedirect();
        $this->assertFalse((bool) $branch->fresh()->is_active);

        $this->actingAs($admin)->patch("/admin/branches/{$branch->id}/toggle")->assertRedirect();
        $this->assertTrue((bool) $branch->fresh()->is_active);
    }

    public function test_closing_branch_warns_about_open_orders(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['status' => 'cooking']);

        $this->actingAs($this->makeUser('admin'))
            ->patch("/admin/branches/{$branch->id}/toggle")
            ->assertSessionHas('warning');
    }

    public function test_customer_cannot_select_closed_branch(): void
    {
        $branch = $this->makeBranch();
        $branch->update(['is_active' => false]);

        $this->post('/branches/confirm', ['branch_id' => $branch->id])->assertSessionHasErrors('branch_id');
        $this->assertNull(session('selected_branch_id'));
    }

    public function test_checkout_blocked_when_cart_branch_closed(): void
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $branch->update(['is_active' => false]);

        $this->actingAs($this->makeUser('customer'))
            ->withSession(['cart' => ['branch_id' => $branch->id, 'items' => [
                ['id' => $item->id, 'name' => $item->name, 'price' => 50000, 'quantity' => 1],
            ]]])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash'])
            ->assertRedirect(route('branches.select'));

        $this->assertDatabaseCount('orders', 0);
    }
}
