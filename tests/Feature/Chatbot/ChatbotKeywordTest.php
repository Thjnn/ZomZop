<?php

namespace Tests\Feature\Chatbot;

use App\Models\Category;
use App\Models\ChatbotKeyword;
use Database\Seeders\ChatbotKeywordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotKeywordTest extends TestCase
{
    use RefreshDatabase;

    private function keyword(string $keywords, array $over = []): ChatbotKeyword
    {
        return ChatbotKeyword::create(array_merge(['keywords' => $keywords, 'reply' => "Trả lời: {$keywords}"], $over));
    }

    public function test_normalize_strips_accents_case_and_punctuation(): void
    {
        $this->assertSame('cho minh xem thuc don', ChatbotKeyword::normalize('  Cho mình XEM thực đơn?! '));
        $this->assertSame('dat hang', ChatbotKeyword::normalize('Đặt hàng'));
    }

    public function test_matches_without_accents_and_any_keyword_in_list(): void
    {
        $row = $this->keyword('thực đơn, menu');

        $this->assertTrue($row->is(ChatbotKeyword::findFor('cho xem thuc don voi')));
        $this->assertTrue($row->is(ChatbotKeyword::findFor('MENU hôm nay có gì?')));
        $this->assertNull(ChatbotKeyword::findFor('burger bao nhiêu calo'));
    }

    public function test_short_keyword_does_not_match_inside_another_word(): void
    {
        $this->keyword('gà');

        $this->assertNull(ChatbotKeyword::findFor('có bán gạo không'));
        $this->assertNotNull(ChatbotKeyword::findFor('có gà rán không'));
    }

    public function test_longest_keyword_wins_and_inactive_is_ignored(): void
    {
        $this->keyword('mã');
        $coupon = $this->keyword('mã giảm giá');
        $this->keyword('mã giảm giá hôm nay', ['is_active' => false]);

        $this->assertTrue($coupon->is(ChatbotKeyword::findFor('có mã giảm giá hôm nay không')));
    }

    public function test_seeder_is_idempotent_and_links_menu_to_first_category(): void
    {
        Category::create(['name' => 'Burger', 'slug' => 'burger', 'is_active' => true, 'sort_order' => 1]);

        $this->seed(ChatbotKeywordSeeder::class);
        $this->seed(ChatbotKeywordSeeder::class);

        $this->assertSame(5, ChatbotKeyword::count());
        $this->assertSame('/category/burger', ChatbotKeyword::findFor('thực đơn')->link_url);
    }
}
