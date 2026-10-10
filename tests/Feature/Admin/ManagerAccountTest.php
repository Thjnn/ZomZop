<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ManagerAccountTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(int $branchId, array $over = []): array
    {
        return array_merge([
            'name' => 'Trần Quản Lý', 'email' => 'ql.mytho@zomzop.com', 'phone' => '0901234567',
            'branch_id' => $branchId, 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123',
        ], $over);
    }

    public function test_admin_creates_manager(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/managers', $this->validData($branch->id))
            ->assertRedirect(route('admin.managers.index'));

        $user = User::where('email', 'ql.mytho@zomzop.com')->sole();
        $this->assertSame('manager', $user->role);
        $this->assertSame($branch->id, (int) $user->branch_id);
        $this->assertTrue(Hash::check('matkhau123', $user->password));
        $log = AdminLog::where('action', 'manager.create')->sole();
        $this->assertStringNotContainsString('matkhau123', json_encode($log->changes));
    }

    public function test_role_sent_by_client_is_ignored(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/managers', $this->validData($branch->id, ['role' => 'admin']));

        $this->assertSame('manager', User::where('email', 'ql.mytho@zomzop.com')->value('role'));
    }

    public function test_validation_errors(): void
    {
        $this->makeUser('customer')->update(['email' => 'trung@zomzop.com']);

        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/managers', $this->validData(999, ['email' => 'trung@zomzop.com', 'password_confirmation' => 'khac']))
            ->assertSessionHasErrors(['email', 'branch_id', 'password']);
    }

    public function test_transfer_manager_to_other_branch_is_logged(): void
    {
        $a = $this->makeBranch('Chi nhánh Alpha');
        $b = $this->makeBranch('Chi nhánh Beta');
        $manager = $this->makeUser('manager', $a);

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/managers/{$manager->id}", [
                'name' => $manager->name, 'email' => $manager->email, 'phone' => null, 'branch_id' => $b->id,
            ])
            ->assertRedirect(route('admin.managers.index'));

        $this->assertSame($b->id, (int) $manager->fresh()->branch_id);
        $log = AdminLog::where('action', 'manager.update')->sole();
        $this->assertEquals($a->id, $log->changes['before']['branch_id']);
        $this->assertEquals($b->id, $log->changes['after']['branch_id']);

        // Manager thấy chi nhánh mới ngay ở lần tải trang sau
        $this->actingAs($manager->fresh())->get('/manager')->assertOk()->assertSee('Chi nhánh Beta');
    }

    public function test_lock_blocks_manager_immediately(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($this->makeUser('admin'))->patch("/admin/managers/{$manager->id}/lock")->assertRedirect();

        $this->assertFalse((bool) $manager->fresh()->is_active);
        $this->actingAs($manager->fresh())->get('/manager')->assertForbidden();
    }

    public function test_reset_password(): void
    {
        $manager = $this->makeUser('manager', $this->makeBranch());

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/managers/{$manager->id}/password", ['password' => 'moi12345', 'password_confirmation' => 'moi12345'])
            ->assertRedirect();

        $this->assertTrue(Hash::check('moi12345', $manager->fresh()->password));
    }

    public function test_cannot_touch_non_manager_accounts(): void
    {
        $customer = $this->makeUser('customer');
        $admin    = $this->makeUser('admin');

        $this->actingAs($admin)->get("/admin/managers/{$customer->id}/edit")->assertNotFound();
        $this->actingAs($admin)->patch("/admin/managers/{$customer->id}/lock")->assertNotFound();
        $this->actingAs($admin)->patch("/admin/managers/{$admin->id}/lock")->assertNotFound();
    }

    public function test_list_filters_by_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $inA = $this->makeUser('manager', $a);
        $inB = $this->makeUser('manager', $b);

        $this->actingAs($this->makeUser('admin'))
            ->get("/admin/managers?branch_id={$a->id}")
            ->assertOk()->assertSee($inA->email)->assertDontSee($inB->email);
    }
}
