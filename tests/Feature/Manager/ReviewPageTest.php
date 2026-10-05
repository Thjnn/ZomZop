<?php

namespace Tests\Feature\Manager;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function review($branch, int $rating, string $comment, ?int $delivery = null): Review
    {
        $order = $this->makeOrder($branch, ['status' => 'completed']);

        return Review::create(['order_id' => $order->id, 'user_id' => $order->user_id, 'branch_id' => $branch->id,
            'rating' => $rating, 'delivery_rating' => $delivery, 'comment' => $comment]);
    }

    /** Review Focus #4 */
    public function test_lists_own_branch_reviews_with_average(): void
    {
        $mine  = $this->makeBranch('Mine');
        $other = $this->makeBranch('Other');
        $this->review($mine, 5, 'Burger ngon tuyệt', 4);
        $this->review($mine, 2, 'Giao hơi chậm', 2);
        $this->review($other, 1, 'Chi nhánh khác chê');

        $this->actingAs($this->makeUser('manager', $mine))
            ->get('/manager/reviews')
            ->assertOk()
            ->assertSee('Burger ngon tuyệt')
            ->assertSee('Giao hơi chậm')
            ->assertDontSee('Chi nhánh khác chê')
            ->assertSee('3,5')      // điểm TB
            ->assertSee('2 lượt');
    }

    public function test_filter_by_rating_and_bad_filter(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $this->review($branch, 5, 'Năm sao');
        $this->review($branch, 1, 'Một sao');

        $this->actingAs($manager)->get('/manager/reviews?rating=1')
            ->assertSee('Một sao')->assertDontSee('Năm sao');

        $this->actingAs($manager)->get('/manager/reviews?rating=9')
            ->assertOk()->assertSee('Năm sao')->assertSee('Một sao');
    }

    public function test_reply_and_edit_reply(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $review  = $this->review($branch, 2, 'Giao hơi chậm');

        $this->actingAs($manager)->put("/manager/reviews/{$review->id}/reply", ['reply' => 'Xin lỗi anh/chị, lần sau tụi em giao nhanh hơn.'])
            ->assertSessionHas('success');
        $this->assertSame('Xin lỗi anh/chị, lần sau tụi em giao nhanh hơn.', $review->fresh()->reply);
        $this->assertNotNull($review->fresh()->replied_at);

        $this->actingAs($manager)->put("/manager/reviews/{$review->id}/reply", ['reply' => 'Đã sửa lời đáp']);
        $this->actingAs($manager)->get('/manager/reviews')->assertSee('Đã sửa lời đáp');

        $this->actingAs($manager)->put("/manager/reviews/{$review->id}/reply", ['reply' => ''])->assertSessionHasErrors('reply');
        $this->actingAs($manager)->put("/manager/reviews/{$review->id}/reply", ['reply' => str_repeat('a', 1001)])->assertSessionHasErrors('reply');
        $this->assertSame('Đã sửa lời đáp', $review->fresh()->reply);
    }

    public function test_cannot_reply_other_branch_review(): void
    {
        $review = $this->review($this->makeBranch('B'), 1, 'Chê');

        $this->actingAs($this->makeUser('manager', $this->makeBranch('A')))
            ->put("/manager/reviews/{$review->id}/reply", ['reply' => 'Hack'])
            ->assertNotFound();
        $this->assertNull($review->fresh()->reply);
    }
}
