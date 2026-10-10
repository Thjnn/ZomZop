<?php

namespace Tests\Feature\Admin;

use App\Jobs\SendCouponEmail;
use App\Mail\CouponMail;
use App\Models\Coupon;
use App\Models\CouponNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CouponEmailTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function coupon(): Coupon
    {
        return Coupon::create(['code' => 'TANGBAN', 'type' => 'fixed', 'value' => 20000, 'is_active' => true]);
    }

    private function customer(bool $optedIn = true, bool $active = true)
    {
        $u = $this->makeUser('customer');
        $u->forceFill(['email_opted_in' => $optedIn, 'is_active' => $active])->save();

        return $u;
    }

    public function test_send_to_all_opted_in_active_customers_only(): void
    {
        Queue::fake();
        $coupon = $this->coupon();
        $yes = $this->customer();
        $this->customer(optedIn: false);
        $this->customer(active: false);
        $this->makeUser('staff', $this->makeBranch());

        $this->actingAs($this->makeUser('admin'))
            ->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'all'])
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertSame([$yes->id], CouponNotification::pluck('user_id')->all());
        $this->assertSame('pending', CouponNotification::sole()->status);
        Queue::assertPushed(SendCouponEmail::class, 1);
    }

    public function test_audience_by_branch(): void
    {
        Queue::fake();
        $coupon = $this->coupon();
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $buyerA = $this->customer();
        $buyerB = $this->customer();
        $this->makeOrder($a, ['user_id' => $buyerA->id]);
        $this->makeOrder($b, ['user_id' => $buyerB->id]);

        $this->actingAs($this->makeUser('admin'))
            ->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'branch', 'branch_id' => $a->id]);

        $this->assertSame([$buyerA->id], CouponNotification::pluck('user_id')->all());
    }

    public function test_sending_twice_does_not_duplicate(): void
    {
        Queue::fake();
        $coupon = $this->coupon();
        $this->customer();
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'all']);
        $this->actingAs($admin)->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'all']);

        $this->assertSame(1, CouponNotification::count());
        Queue::assertPushed(SendCouponEmail::class, 1);
    }

    public function test_cannot_send_invalid_coupon(): void
    {
        Queue::fake();
        $coupon = $this->coupon();
        $coupon->update(['is_active' => false]);
        $this->customer();

        $this->actingAs($this->makeUser('admin'))
            ->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'all'])
            ->assertSessionHasErrors();

        $this->assertSame(0, CouponNotification::count());
    }

    public function test_job_sends_mail_and_marks_sent(): void
    {
        Mail::fake();
        $coupon = $this->coupon();
        $user = $this->customer();
        $n = CouponNotification::create(['user_id' => $user->id, 'coupon_id' => $coupon->id, 'channel' => 'email', 'status' => 'pending']);

        (new SendCouponEmail($n->id))->handle();

        Mail::assertSent(CouponMail::class, fn ($m) => $m->hasTo($user->email));
        $this->assertSame('sent', $n->fresh()->status);
        $this->assertNotNull($n->fresh()->sent_at);
    }

    public function test_job_skips_user_who_unsubscribed_meanwhile(): void
    {
        Mail::fake();
        $coupon = $this->coupon();
        $user = $this->customer(optedIn: false);
        $n = CouponNotification::create(['user_id' => $user->id, 'coupon_id' => $coupon->id, 'channel' => 'email', 'status' => 'pending']);

        (new SendCouponEmail($n->id))->handle();

        Mail::assertNothingSent();
        $this->assertSame('failed', $n->fresh()->status);
    }

    public function test_mail_contains_code_and_unsubscribe_link(): void
    {
        $mail = new CouponMail($this->coupon(), $this->customer());

        $mail->assertSeeInHtml('TANGBAN');
        $mail->assertSeeInHtml('/unsubscribe/');
    }

    public function test_signed_unsubscribe_link_opts_out(): void
    {
        $user = $this->customer();

        $this->get("/unsubscribe/{$user->id}")->assertForbidden(); // không có chữ ký
        $this->get(URL::signedRoute('unsubscribe', ['user' => $user->id]))->assertOk();

        $this->assertFalse((bool) $user->fresh()->email_opted_in);
    }

    public function test_profile_checkbox_saves_opt_in(): void
    {
        $user = $this->customer(optedIn: false);

        $this->actingAs($user)->put('/profile', [
            'name' => $user->name, 'email' => $user->email, 'email_opted_in' => '1',
        ])->assertRedirect();

        $this->assertTrue((bool) $user->fresh()->email_opted_in);
    }
}
