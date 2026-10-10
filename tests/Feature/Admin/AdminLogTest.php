<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class AdminLogTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    public function test_record_saves_actor_subject_and_changes(): void
    {
        $admin  = $this->makeUser('admin');
        $branch = $this->makeBranch('Cũ');
        $this->actingAs($admin);

        $branch->fill(['name' => 'Mới']);
        $changes = AdminLog::diff($branch);
        $branch->save();
        AdminLog::record('branch.update', $branch, $changes);

        $log = AdminLog::sole();
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('branch.update', $log->action);
        $this->assertSame('Branch', $log->subject_type);
        $this->assertSame($branch->id, $log->subject_id);
        $this->assertSame(['before' => ['name' => 'Cũ'], 'after' => ['name' => 'Mới']], $log->changes);
    }

    public function test_diff_never_contains_password(): void
    {
        $user = $this->makeUser('manager');
        $user->fill(['password' => 'matkhaumoi123', 'name' => 'Tên mới']);

        $changes = AdminLog::diff($user);

        $this->assertArrayNotHasKey('password', $changes['after']);
        $this->assertArrayNotHasKey('password', $changes['before']);
        $this->assertSame('Tên mới', $changes['after']['name']);
    }
}
