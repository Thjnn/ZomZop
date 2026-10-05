<?php

namespace Tests\Feature\Manager;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BranchManagersTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    public function test_seeder_gives_every_branch_two_managers_and_staff(): void
    {
        $this->seed([BranchSeeder::class, UserSeeder::class]);

        foreach (Branch::all() as $branch) {
            $count = fn ($role) => User::where('branch_id', $branch->id)->where('role', $role)->count();
            $this->assertSame(2, $count('manager'), "Chi nhánh {$branch->name} cần 2 quản lý");
            $this->assertSame(2, $count('staff'));
            $this->assertSame(1, $count('kitchen'));
        }

        // Tài khoản cũ vẫn giữ để không đổi thói quen đăng nhập
        $first = Branch::orderBy('id')->first();
        foreach (['manager@zomzop.com', 'staff@zomzop.com', 'kitchen@zomzop.com'] as $email) {
            $this->assertSame($first->id, (int) User::where('email', $email)->value('branch_id'));
        }
        $this->assertTrue(Hash::check('12345678', User::where('email', 'manager@zomzop.com')->value('password')));
        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(1, User::where('email', 'customer@zomzop.com')->count());
    }

    public function test_command_creates_manager_by_branch_id_or_name(): void
    {
        $a = $this->makeBranch('ZomZop - Bến Tre');
        $this->makeBranch('ZomZop - Mỹ Tho 2');

        $this->artisan('zomzop:manager', ['branch' => 'bến tre', 'email' => 'ql.bentre@zomzop.com', '--password' => 'matkhau123'])
            ->expectsOutputToContain('ZomZop - Bến Tre')
            ->assertSuccessful();

        $u = User::where('email', 'ql.bentre@zomzop.com')->first();
        $this->assertSame('manager', $u->role);
        $this->assertSame($a->id, (int) $u->branch_id);
        $this->assertTrue((bool) $u->is_active);
        $this->assertTrue(Hash::check('matkhau123', $u->password));
        $this->assertSame('Quản lý ZomZop - Bến Tre', $u->name);

        $this->artisan('zomzop:manager', ['branch' => (string) $a->id, 'email' => 'ql2@zomzop.com', '--name' => 'Trần Quản Lý'])
            ->expectsOutputToContain('Mật khẩu')
            ->assertSuccessful();
        $this->assertSame('Trần Quản Lý', User::where('email', 'ql2@zomzop.com')->value('name'));
    }

    public function test_command_rejects_bad_input(): void
    {
        $this->makeBranch('ZomZop - Mỹ Tho 1');
        $this->makeBranch('ZomZop - Mỹ Tho 2');
        $this->makeUser('customer')->update(['email' => 'trung@zomzop.com']);

        $this->artisan('zomzop:manager', ['branch' => 'Cần Thơ', 'email' => 'a@zomzop.com'])->assertFailed();
        $this->artisan('zomzop:manager', ['branch' => 'mỹ tho', 'email' => 'a@zomzop.com'])->assertFailed();   // khớp 2 chi nhánh
        $this->artisan('zomzop:manager', ['branch' => 'mỹ tho 1', 'email' => 'khong-phai-email'])->assertFailed();
        $this->artisan('zomzop:manager', ['branch' => 'mỹ tho 1', 'email' => 'trung@zomzop.com'])->assertFailed();
        $this->artisan('zomzop:manager', ['branch' => 'mỹ tho 1', 'email' => 'b@zomzop.com', '--password' => 'ngan'])->assertFailed();

        $this->assertSame(0, User::where('role', 'manager')->count());
    }
}
