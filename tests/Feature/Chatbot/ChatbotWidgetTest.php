<?php

namespace Tests\Feature\Chatbot;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ChatbotWidgetTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_logged_in_customer_gets_chat_panel(): void
    {
        $this->actingAs($this->makeUser('customer'))
            ->get('/search')
            ->assertOk()
            ->assertSee('id="chatbot-toggle"', false)
            ->assertSee('id="chatbot-panel"', false);
    }

    public function test_guest_sees_bubble_but_no_panel(): void
    {
        $this->get('/search')
            ->assertOk()
            ->assertSee('id="chatbot-toggle"', false)
            ->assertDontSee('id="chatbot-panel"', false);
    }
}
