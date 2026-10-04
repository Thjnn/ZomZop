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

    public function test_update_creates_row_and_sets_price_and_availability(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');

        $this->actingAs($this->makeUser('manager', $branch))
            ->put("/manager/menu/{$burger->id}", ['price' => '45000', 'is_available' => '0'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('branch_menu_items', [
            'branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 45000, 'is_available' => 0,
        ]);
    }

    public function test_empty_price_resets_to_base(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->actingAs($this->makeUser('manager', $branch))
            ->put("/manager/menu/{$burger->id}", ['price' => '', 'is_available' => '1']);

        $this->assertNull(BranchMenuItem::where('branch_id', $branch->id)->value('price'));
    }

    /** Review Focus #3 */
    public function test_invalid_price_is_rejected(): void
    {
        $branch  = $this->makeBranch();
        $burger  = $this->makeMenuItem('Burger');
        $manager = $this->makeUser('manager', $branch);

        foreach (['-5', 'abc', '999999999999', '500'] as $bad) {
            $this->actingAs($manager)->from('/manager/menu')
                ->put("/manager/menu/{$burger->id}", ['price' => $bad, 'is_available' => '1'])
                ->assertSessionHasErrors('price');
        }
        $this->assertDatabaseCount('branch_menu_items', 0);
    }

    public function test_unknown_item_returns_404(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))
            ->put('/manager/menu/99999', ['price' => '45000', 'is_available' => '1'])
            ->assertNotFound();
    }

    /** Review Focus #4 */
    public function test_update_only_touches_own_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $b->id, 'menu_item_id' => $burger->id, 'price' => 99000, 'is_available' => true]);

        $this->actingAs($this->makeUser('manager', $a))
            ->put("/manager/menu/{$burger->id}", ['price' => '45000', 'is_available' => '0']);

        $this->assertDatabaseHas('branch_menu_items', ['branch_id' => $b->id, 'price' => 99000, 'is_available' => 1]);
    }

    public function test_chain_wide_disabled_item_cannot_be_enabled(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $burger->update(['is_available' => false]);

        $this->actingAs($this->makeUser('manager', $branch))
            ->from('/manager/menu')
            ->put("/manager/menu/{$burger->id}", ['price' => '', 'is_available' => '1'])
            ->assertSessionHasErrors('is_available');
    }
}
