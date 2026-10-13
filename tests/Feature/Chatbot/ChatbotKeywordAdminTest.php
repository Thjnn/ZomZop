<?php

namespace Tests\Feature\Chatbot;

use App\Models\AdminLog;
use App\Models\ChatbotKeyword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ChatbotKeywordAdminTest extends TestCase
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
            'keywords' => 'giờ mở cửa, mấy giờ', 'reply' => 'Mở cửa 8:00–22:00.',
            'link_url' => '/branches/select', 'link_label' => 'Chọn chi nhánh', 'is_active' => '1',
        ], $over);
    }

    public function test_admin_lists_and_creates_keyword(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/chatbot-keywords')->assertOk()->assertSee('Chatbot');

        $this->actingAs($admin)
            ->post('/admin/chatbot-keywords', $this->validData())
            ->assertRedirect(route('admin.chatbot-keywords.index'));

        $row = ChatbotKeyword::sole();
        $this->assertSame('/branches/select', $row->link_url);
        $this->assertTrue($row->is_active);
        $this->assertSame('chatbot_keyword.create', AdminLog::sole()->action);
    }

    public function test_rejects_external_and_protocol_relative_links(): void
    {
        $admin = $this->makeUser('admin');

        foreach (['https://evil.com', '//evil.com', '/\evil.com', 'javascript:alert(1)'] as $link) {
            $this->actingAs($admin)
                ->post('/admin/chatbot-keywords', $this->validData(['link_url' => $link]))
                ->assertSessionHasErrors('link_url');
        }

        $this->assertDatabaseCount('chatbot_keywords', 0);
    }

    public function test_link_label_required_when_link_given_and_both_optional(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->post('/admin/chatbot-keywords', $this->validData(['link_label' => '']))
            ->assertSessionHasErrors('link_label');

        $this->actingAs($admin)
            ->post('/admin/chatbot-keywords', $this->validData(['link_url' => '', 'link_label' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull(ChatbotKeyword::sole()->link_url);
    }

    public function test_admin_updates_toggles_and_deletes(): void
    {
        $admin = $this->makeUser('admin');
        $row = ChatbotKeyword::create(['keywords' => 'menu', 'reply' => 'Cũ']);

        $this->actingAs($admin)->put("/admin/chatbot-keywords/{$row->id}", $this->validData(['reply' => 'Mới']))
            ->assertRedirect(route('admin.chatbot-keywords.index'));
        $this->assertSame('Mới', $row->fresh()->reply);

        $this->actingAs($admin)->patch("/admin/chatbot-keywords/{$row->id}/toggle")->assertRedirect();
        $this->assertFalse($row->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/chatbot-keywords/{$row->id}")->assertRedirect();
        $this->assertDatabaseCount('chatbot_keywords', 0);

        $this->assertSame(
            ['chatbot_keyword.update', 'chatbot_keyword.toggle', 'chatbot_keyword.delete'],
            AdminLog::orderBy('id')->pluck('action')->all()
        );
    }

    public function test_customer_and_manager_cannot_access(): void
    {
        foreach (['customer', 'manager'] as $role) {
            $this->actingAs($this->makeUser($role, $role === 'manager' ? $this->makeBranch() : null))
                ->get('/admin/chatbot-keywords')
                ->assertForbidden();
        }
    }
}
