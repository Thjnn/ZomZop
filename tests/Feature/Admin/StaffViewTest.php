<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class StaffViewTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_staff_of_all_branches_with_filter(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $s1 = $this->makeUser('staff', $a);
        $s2 = $this->makeUser('kitchen', $b);
        $customer = $this->makeUser('customer');
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/staff')
            ->assertOk()->assertSee($s1->email)->assertSee($s2->email)->assertDontSee($customer->email);

        $this->actingAs($admin)->get("/admin/staff?branch_id={$b->id}")
            ->assertOk()->assertDontSee($s1->email)->assertSee($s2->email);
    }

    public function test_page_has_no_edit_actions(): void
    {
        $this->makeUser('staff', $this->makeBranch());

        $this->actingAs($this->makeUser('admin'))->get('/admin/staff')
            ->assertOk()->assertDontSee('Sửa')->assertDontSee('Khoá');
    }
}
