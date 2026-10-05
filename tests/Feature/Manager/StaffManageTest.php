<?php

namespace Tests\Feature\Manager;

use App\Models\SalaryConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffManageTest extends TestCase
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
            'name' => 'Nguyễn Văn Bếp', 'email' => 'bep1@zomzop.com', 'phone' => '0901111222',
            'role' => 'kitchen', 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123',
            'started_at' => '2026-10-01', 'probation_rate' => 20000, 'salary_rate' => 25000,
        ], $over);
    }

    public function test_lists_only_own_branch_staff(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $mine  = $this->makeUser('staff', $a);
        $other = $this->makeUser('staff', $b);
        $cust  = $this->makeUser('customer');

        $this->actingAs($this->makeUser('manager', $a))
            ->get('/manager/staff')
            ->assertOk()
            ->assertSee($mine->email)
            ->assertDontSee($other->email)
            ->assertDontSee($cust->email);
    }

    public function test_create_staff_account_for_own_branch(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))
            ->post('/manager/staff', $this->validData())
            ->assertRedirect(route('manager.staff.index'))
            ->assertSessionHas('success');

        $user = User::where('email', 'bep1@zomzop.com')->first();
        $this->assertSame('kitchen', $user->role);
        $this->assertSame($branch->id, (int) $user->branch_id);
        $this->assertTrue((bool) $user->is_active);
        $this->assertTrue(Hash::check('matkhau123', $user->password));
    }

    /** Review Focus #2 */
    public function test_cannot_create_manager_or_admin_or_other_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $manager = $this->makeUser('manager', $a);

        foreach (['manager', 'admin', 'customer'] as $role) {
            $this->actingAs($manager)->from('/manager/staff/create')
                ->post('/manager/staff', $this->validData(['role' => $role, 'email' => "$role@x.com"]))
                ->assertSessionHasErrors('role');
        }

        $this->actingAs($manager)->post('/manager/staff', $this->validData(['branch_id' => $b->id]));
        $this->assertSame($a->id, (int) User::where('email', 'bep1@zomzop.com')->value('branch_id'));
    }

    public function test_validation_messages(): void
    {
        $branch = $this->makeBranch();
        $taken  = $this->makeUser('customer');

        $this->actingAs($this->makeUser('manager', $branch))->from('/manager/staff/create')
            ->post('/manager/staff', $this->validData(['email' => $taken->email, 'password_confirmation' => 'khac12345', 'password' => 'ngan']))
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_update_lock_and_reset_password(): void
    {
        $branch  = $this->makeBranch();
        $staff   = $this->makeUser('staff', $branch);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)
            ->put("/manager/staff/{$staff->id}", ['name' => 'Tên Mới', 'email' => $staff->email, 'phone' => '', 'role' => 'kitchen', 'started_at' => '2026-10-01', 'probation_rate' => 20000, 'salary_rate' => 25000])
            ->assertRedirect(route('manager.staff.index'));
        $this->assertSame('Tên Mới', $staff->fresh()->name);
        $this->assertSame('kitchen', $staff->fresh()->role);

        $this->actingAs($manager)->patch("/manager/staff/{$staff->id}/lock");
        $this->assertFalse((bool) $staff->fresh()->is_active);
        $this->actingAs($manager)->patch("/manager/staff/{$staff->id}/lock");
        $this->assertTrue((bool) $staff->fresh()->is_active);

        $this->actingAs($manager)
            ->put("/manager/staff/{$staff->id}/password", ['password' => 'moi12345678', 'password_confirmation' => 'moi12345678'])
            ->assertSessionHas('success');
        $this->assertTrue(Hash::check('moi12345678', $staff->fresh()->password));
    }

    public function test_create_saves_start_date_and_salary(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))->post('/manager/staff', $this->validData());

        $user = User::where('email', 'bep1@zomzop.com')->first();
        $this->assertSame('2026-10-01', $user->started_at->toDateString());
        $config = $user->latestSalary;
        $this->assertSame(25000, (int) $config->rate);
        $this->assertSame(20000, (int) $config->probation_rate);
        $this->assertSame('hourly', $config->type);
        $this->assertSame(today()->toDateString(), $config->effective_from->toDateString());
    }

    public function test_update_creates_new_salary_only_when_changed(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $staff   = $this->makeUser('staff', $branch);
        SalaryConfig::create(['user_id' => $staff->id, 'type' => 'hourly', 'rate' => 25000, 'probation_rate' => 20000, 'effective_from' => '2026-01-01']);
        $data = ['name' => $staff->name, 'email' => $staff->email, 'role' => 'staff', 'started_at' => '2026-01-01'];

        $this->actingAs($manager)->put("/manager/staff/{$staff->id}", $data + ['probation_rate' => 20000, 'salary_rate' => 25000]);
        $this->assertSame(1, SalaryConfig::where('user_id', $staff->id)->count());

        $this->actingAs($manager)->put("/manager/staff/{$staff->id}", $data + ['probation_rate' => 20000, 'salary_rate' => 30000]);
        $this->assertSame(2, SalaryConfig::where('user_id', $staff->id)->count());
        $this->assertSame(30000, (int) $staff->fresh()->latestSalary->rate);

        $this->actingAs($manager)->from("/manager/staff/{$staff->id}/edit")
            ->put("/manager/staff/{$staff->id}", $data + ['probation_rate' => 0, 'salary_rate' => 'abc'])
            ->assertSessionHasErrors(['probation_rate', 'salary_rate']);
    }

    public function test_index_shows_rate_and_probation_badge(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $staff->update(['started_at' => today()->subDays(2)]);
        SalaryConfig::create(['user_id' => $staff->id, 'type' => 'hourly', 'rate' => 25000, 'probation_rate' => 20000, 'effective_from' => today()]);

        $this->actingAs($this->makeUser('manager', $branch))->get('/manager/staff')
            ->assertSee('25.000đ/giờ')
            ->assertSee('Thử việc đến ' . today()->addDays(4)->format('d/m'));
    }

    /** Review Focus #1 */
    public function test_cannot_touch_users_outside_own_staff(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $manager = $this->makeUser('manager', $a);
        $targets = [$this->makeUser('staff', $b), $this->makeUser('customer'), $this->makeUser('manager', $a)];

        foreach ($targets as $t) {
            $this->actingAs($manager)->get("/manager/staff/{$t->id}/edit")->assertNotFound();
            $this->actingAs($manager)->put("/manager/staff/{$t->id}", ['name' => 'Hack', 'email' => $t->email, 'role' => 'staff'])->assertNotFound();
            $this->actingAs($manager)->patch("/manager/staff/{$t->id}/lock")->assertNotFound();
            $this->actingAs($manager)->put("/manager/staff/{$t->id}/password", ['password' => 'hack12345', 'password_confirmation' => 'hack12345'])->assertNotFound();
            $this->assertNotSame('Hack', $t->fresh()->name);
            $this->assertTrue((bool) $t->fresh()->is_active);
        }
    }
}
