<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_sends_contact_message(): void
    {
        $this->get('/')->assertOk()->assertSee('id="lien-he"', false);

        $this->from('/')->post('/contact', [
            'name' => 'Nguyễn Văn A', 'email' => 'a@example.com', 'phone' => '0901234567', 'message' => 'Cho mình hỏi có giao tới Chợ Gạo không?',
        ])->assertRedirect(url('/') . '#lien-he')->assertSessionHas('contact_sent');

        $this->assertDatabaseHas('contact_messages', ['email' => 'a@example.com', 'user_id' => null, 'handled_at' => null]);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->from('/')->post('/contact', ['name' => '', 'email' => 'sai', 'phone' => '123', 'message' => 'ngắn'])
            ->assertSessionHasErrorsIn('contact', ['name', 'email', 'phone', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_admin_lists_and_marks_handled(): void
    {
        $msg = ContactMessage::create(['name' => 'Khách Liên Hệ', 'email' => 'b@example.com', 'message' => 'Món rất ngon, cảm ơn shop!']);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/contact-messages')->assertOk()->assertSee('Khách Liên Hệ');
        $this->actingAs($admin)->patch("/admin/contact-messages/{$msg->id}/toggle")->assertRedirect();
        $this->assertNotNull($msg->fresh()->handled_at);

        $this->actingAs($this->makeUser('manager', $this->makeBranch()))->get('/admin/contact-messages')->assertForbidden();
    }
}
