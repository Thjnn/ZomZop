<?php

namespace Tests\Feature\Chatbot;

use App\Models\BranchMenuItem;
use App\Models\ChatbotKeyword;
use App\Services\Chatbot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ChatbotChatTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.key' => 'test-key', 'services.gemini.model' => 'gemini-test']);
        ChatbotKeyword::create([
            'keywords' => 'thực đơn, menu', 'reply' => 'Xem thực đơn ở đây nhé!',
            'link_url' => '/category/burger', 'link_label' => 'Xem thực đơn',
        ]);
    }

    private function fakeGemini(string $text = 'Khoảng 550 kcal (ước tính).'): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => $text]]]]],
        ])]);
    }

    public function test_keyword_match_replies_with_link_without_calling_gemini(): void
    {
        Http::fake();

        $this->actingAs($this->makeUser('customer'))
            ->postJson('/chatbot/message', ['message' => 'Cho xem thuc don voi'])
            ->assertOk()
            ->assertExactJson([
                'reply' => 'Xem thực đơn ở đây nhé!', 'link_url' => '/category/burger',
                'link_label' => 'Xem thực đơn', 'source' => 'keyword',
            ]);

        Http::assertNothingSent();
    }

    public function test_other_questions_go_to_gemini_with_menu_and_branch_prices(): void
    {
        $this->fakeGemini();
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Bò');
        $off    = $this->makeMenuItem('Pizza Đã Tắt');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $off->id, 'is_available' => false]);

        $this->actingAs($this->makeUser('customer'))
            ->withSession(['selected_branch_id' => $branch->id])
            ->postJson('/chatbot/message', ['message' => 'Burger bò bao nhiêu calo?'])
            ->assertOk()
            ->assertJson(['reply' => 'Khoảng 550 kcal (ước tính).', 'source' => 'gemini', 'link_url' => null]);

        Http::assertSent(function (HttpRequest $req) {
            $system = $req['systemInstruction']['parts'][0]['text'];
            $contents = $req['contents']; // end() nhận tham chiếu, không dùng trực tiếp trên ArrayAccess của Request

            return str_contains($req->url(), '/models/gemini-test:generateContent')
                && $req->hasHeader('x-goog-api-key', 'test-key')
                && str_contains($system, 'Burger Bò')
                && str_contains($system, '61.000')
                && !str_contains($system, 'Pizza Đã Tắt')
                && end($contents)['parts'][0]['text'] === 'Burger bò bao nhiêu calo?';
        });
    }

    public function test_history_sent_to_gemini_starts_with_user_and_maps_bot_to_model(): void
    {
        $this->fakeGemini();
        $user = $this->makeUser('customer');
        $this->actingAs($user)->postJson('/chatbot/message', ['message' => 'menu']);           // user + bot (từ khoá)
        $this->actingAs($user)->postJson('/chatbot/message', ['message' => 'món này bao nhiêu calo']);

        Http::assertSent(function (HttpRequest $req) {
            $roles = array_column($req['contents'], 'role');

            return $roles === ['user', 'model', 'user'];
        });
    }

    public function test_bot_only_history_is_dropped_so_contents_start_with_user(): void
    {
        $this->fakeGemini();

        app(Chatbot::class)->reply('calo?', [['role' => 'bot', 'content' => 'Xin chào!']]);

        Http::assertSent(fn (HttpRequest $req) => array_column($req['contents'], 'role') === ['user']);
    }

    public function test_gemini_multiple_parts_are_joined(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Khoảng 550 '], ['text' => 'kcal.']]]]],
        ])]);

        $this->actingAs($this->makeUser('customer'))
            ->postJson('/chatbot/message', ['message' => 'calo bao nhiêu'])
            ->assertOk()
            ->assertJson(['reply' => 'Khoảng 550 kcal.', 'source' => 'gemini']);
    }

    public function test_gemini_error_returns_fallback_with_200(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response('boom', 500)]);

        $this->actingAs($this->makeUser('customer'))
            ->postJson('/chatbot/message', ['message' => 'calo bao nhiêu'])
            ->assertOk()
            ->assertJson(['reply' => Chatbot::FALLBACK, 'source' => 'fallback']);
    }

    public function test_gemini_empty_candidates_returns_fallback(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => []])]);

        $this->actingAs($this->makeUser('customer'))
            ->postJson('/chatbot/message', ['message' => 'calo bao nhiêu'])
            ->assertOk()
            ->assertJson(['reply' => Chatbot::FALLBACK, 'source' => 'fallback']);
    }

    public function test_missing_api_key_returns_fallback_without_request(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        $this->actingAs($this->makeUser('customer'))
            ->postJson('/chatbot/message', ['message' => 'calo bao nhiêu'])
            ->assertJson(['reply' => Chatbot::FALLBACK, 'source' => 'fallback']);

        Http::assertNothingSent();
    }

    public function test_history_is_saved_and_capped_at_15(): void
    {
        $user = $this->makeUser('customer');
        $this->actingAs($user)->postJson('/chatbot/message', ['message' => 'menu']);

        $this->actingAs($user)->getJson('/chatbot/messages')
            ->assertOk()
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('messages.0.role', 'user')
            ->assertJsonPath('messages.0.content', 'menu')
            ->assertJsonPath('messages.1.role', 'bot')
            ->assertJsonPath('messages.1.link_url', '/category/burger');

        for ($i = 0; $i < 7; $i++) {
            $this->actingAs($user)->postJson('/chatbot/message', ['message' => 'menu']);
        }

        $this->actingAs($user)->getJson('/chatbot/messages')->assertJsonCount(15, 'messages');
        $this->assertSame(8, $user->aiChatSessions()->sole()->message_count);
    }

    public function test_guest_cannot_chat(): void
    {
        $this->postJson('/chatbot/message', ['message' => 'menu'])->assertUnauthorized();
        $this->getJson('/chatbot/messages')->assertUnauthorized();
    }

    public function test_message_is_required_and_at_most_500_chars(): void
    {
        $user = $this->makeUser('customer');

        $this->actingAs($user)->postJson('/chatbot/message', ['message' => ''])->assertUnprocessable();
        $this->actingAs($user)->postJson('/chatbot/message', ['message' => str_repeat('a', 501)])->assertUnprocessable();
    }

    public function test_eleventh_message_in_a_minute_is_throttled(): void
    {
        $user = $this->makeUser('customer');

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->postJson('/chatbot/message', ['message' => 'menu'])->assertOk();
        }

        $this->actingAs($user)->postJson('/chatbot/message', ['message' => 'menu'])->assertStatus(429);
    }
}
