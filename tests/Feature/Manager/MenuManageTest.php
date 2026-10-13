<?php

namespace Tests\Feature\Manager;

use App\Models\BranchMenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_items_with_branch_price(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Liệt Kê');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/menu')
            ->assertOk()
            ->assertSee('Burger Liệt Kê')
            ->assertSee('value="61000"', false);
    }

    private function save(array $items)
    {
        return $this->from('/manager/menu')->put('/manager/menu', ['items' => $items]);
    }

    public function test_update_creates_row_and_sets_price_and_availability(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');

        $this->actingAs($this->makeUser('manager', $branch));
        $this->save([$burger->id => ['price' => '45000', 'is_available' => '0']])
            ->assertRedirect('/manager/menu')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('branch_menu_items', [
            'branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 45000, 'is_available' => 0,
        ]);
    }

    public function test_saves_many_items_at_once_and_skips_untouched_defaults(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $pizza  = $this->makeMenuItem('Pizza');
        $khoai  = $this->makeMenuItem('Khoai');

        $this->actingAs($this->makeUser('manager', $branch));
        $this->save([
            $burger->id => ['price' => '', 'is_available' => '1', 'is_bestseller' => '1'],
            $pizza->id  => ['price' => '99000', 'is_available' => '1', 'is_bestseller' => '1'],
            $khoai->id  => ['price' => '', 'is_available' => '1', 'is_bestseller' => '0'],
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('branch_menu_items', ['menu_item_id' => $burger->id, 'price' => null, 'is_bestseller' => 1]);
        $this->assertDatabaseHas('branch_menu_items', ['menu_item_id' => $pizza->id, 'price' => 99000, 'is_bestseller' => 1]);
        $this->assertDatabaseMissing('branch_menu_items', ['menu_item_id' => $khoai->id]);
    }

    public function test_empty_price_resets_to_base(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->actingAs($this->makeUser('manager', $branch));
        $this->save([$burger->id => ['price' => '', 'is_available' => '1']]);

        $this->assertNull(BranchMenuItem::where('branch_id', $branch->id)->value('price'));
    }

    /** Review Focus #3 — một dòng sai thì không dòng nào được lưu */
    public function test_invalid_price_is_rejected_and_nothing_saved(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $pizza  = $this->makeMenuItem('Pizza');
        $this->actingAs($this->makeUser('manager', $branch));

        foreach (['-5', 'abc', '999999999999', '500'] as $bad) {
            $this->save([
                $pizza->id  => ['price' => '50000', 'is_available' => '1'],
                $burger->id => ['price' => $bad, 'is_available' => '1'],
            ])->assertSessionHasErrors("items.{$burger->id}.price");
        }
        $this->assertDatabaseCount('branch_menu_items', 0);
    }

    public function test_unknown_item_is_ignored(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch));
        $this->save([99999 => ['price' => '45000', 'is_available' => '1']])->assertSessionHas('success');
        $this->assertDatabaseCount('branch_menu_items', 0);
    }

    /** Review Focus #4 */
    public function test_update_only_touches_own_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $b->id, 'menu_item_id' => $burger->id, 'price' => 99000, 'is_available' => true]);

        $this->actingAs($this->makeUser('manager', $a));
        $this->save([$burger->id => ['price' => '45000', 'is_available' => '0']]);

        $this->assertDatabaseHas('branch_menu_items', ['branch_id' => $b->id, 'price' => 99000, 'is_available' => 1]);
    }

    public function test_chain_wide_disabled_item_cannot_be_enabled(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $burger->update(['is_available' => false]);

        $this->actingAs($this->makeUser('manager', $branch));
        $this->save([$burger->id => ['price' => '', 'is_available' => '1']])
            ->assertSessionHasErrors("items.{$burger->id}.is_available");
        $this->assertDatabaseCount('branch_menu_items', 0);
    }

    public function test_manager_toggles_bestseller(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $this->actingAs($this->makeUser('manager', $branch));

        $this->save([$burger->id => ['price' => '', 'is_available' => '1', 'is_bestseller' => '1']]);
        $this->assertTrue(BranchMenuItem::where('branch_id', $branch->id)->value('is_bestseller'));

        $this->save([$burger->id => ['price' => '', 'is_available' => '1', 'is_bestseller' => '0']]);
        $this->assertFalse(BranchMenuItem::where('branch_id', $branch->id)->value('is_bestseller'));
    }

    public function test_dashboard_saves_chef_picks_replacing_old_ones(): void
    {
        $branch = $this->makeBranch();
        $other  = $this->makeBranch('B');
        $burger = $this->makeMenuItem('Burger Đầu Bếp');
        $pizza  = $this->makeMenuItem('Pizza Đầu Bếp');
        $off    = $this->makeMenuItem('Gà Đã Tắt');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $pizza->id, 'is_available' => true, 'is_chef_pick' => true]);
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $off->id, 'is_available' => false]);
        BranchMenuItem::create(['branch_id' => $other->id, 'menu_item_id' => $pizza->id, 'is_available' => true, 'is_chef_pick' => true]);
        $this->actingAs($this->makeUser('manager', $branch));

        $this->get('/manager')->assertOk()->assertSee('Burger Đầu Bếp')->assertDontSee('Gà Đã Tắt');

        $this->from('/manager')->put('/manager/chef-picks', ['items' => [$burger->id, $off->id]])
            ->assertRedirect('/manager')->assertSessionHas('success');

        $picks = BranchMenuItem::where('is_chef_pick', true)->get(['branch_id', 'menu_item_id']);
        $this->assertEqualsCanonicalizing(
            [[$branch->id, $burger->id], [$other->id, $pizza->id]],
            $picks->map(fn ($r) => [$r->branch_id, $r->menu_item_id])->all()
        );
    }

    public function test_chef_picks_limited_to_ten(): void
    {
        $branch = $this->makeBranch();
        $ids = collect(range(1, 11))->map(fn ($i) => $this->makeMenuItem("Món $i")->id)->all();

        $this->actingAs($this->makeUser('manager', $branch))
            ->from('/manager')->put('/manager/chef-picks', ['items' => $ids])
            ->assertSessionHasErrors('items');
        $this->assertDatabaseCount('branch_menu_items', 0);
    }
}
