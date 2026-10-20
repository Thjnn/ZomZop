<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialUser;
use Mockery;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'x']);
    }

    /** Giả lập Google trả về người dùng này ở bước callback */
    private function googleReturns(?string $email, string $id = 'g-1', string $name = 'Khách Google'): void
    {
        $social = (new SocialUser)->map(['id' => $id, 'name' => $name, 'email' => $email]);
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($social);
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    }

    public function test_new_google_user_becomes_customer_and_is_logged_in(): void
    {
        $this->googleReturns('moi@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $user = User::where('email', 'moi@gmail.com')->first();
        $this->assertSame('customer', $user->role);
        $this->assertSame('g-1', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_second_login_finds_same_user_by_google_id(): void
    {
        $this->googleReturns('moi@gmail.com');
        $this->get('/auth/google/callback');
        auth()->logout();

        // Đổi email bên Google vẫn là người cũ
        $this->googleReturns('doi@gmail.com');
        $this->get('/auth/google/callback');

        $this->assertSame(1, User::where('google_id', 'g-1')->count());
        $this->assertDatabaseMissing('users', ['email' => 'doi@gmail.com']);
    }

    public function test_existing_customer_with_same_email_is_linked(): void
    {
        $customer = $this->makeUser('customer');
        $this->googleReturns($customer->email);

        $this->get('/auth/google/callback');

        $this->assertSame('g-1', $customer->fresh()->google_id);
        $this->assertAuthenticatedAs($customer);
    }

    public function test_staff_email_cannot_log_in_with_google(): void
    {
        $manager = $this->makeUser('manager', $this->makeBranch());
        $this->googleReturns($manager->email);

        $this->get('/auth/google/callback')->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull($manager->fresh()->google_id);
    }

    public function test_locked_customer_is_rejected(): void
    {
        $customer = $this->makeUser('customer');
        $customer->update(['is_active' => false]);
        $this->googleReturns($customer->email);

        $this->get('/auth/google/callback')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_account_without_email_is_rejected(): void
    {
        $this->googleReturns(null);

        $this->get('/auth/google/callback')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertSame(0, User::whereNotNull('google_id')->count());
    }

    public function test_buttons_hidden_and_routes_off_without_config(): void
    {
        $this->get('/login')->assertSee('Đăng nhập bằng Google')->assertDontSee('bằng Facebook');
        $this->get('/auth/facebook')->assertNotFound();
    }
}
