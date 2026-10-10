<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Setting;
use App\Support\PaymentMethods;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class SettingTest extends TestCase
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
            'brand_name' => 'ZomZop', 'brand_slogan' => 'Ăn ngon mỗi ngày', 'hotline' => '1900 8888',
            'email' => 'hotro@zomzop.vn', 'address' => '12 Ấp Bắc, Mỹ Tho', 'open_hours' => '08:00 - 22:00',
            'footer_about' => 'Chuỗi đồ ăn nhanh', 'footer_facebook' => 'https://facebook.com/zomzop', 'footer_zalo' => '',
            'payment_cash' => '1', 'payment_momo' => '1',
        ], $over);
    }

    public function test_admin_saves_settings_and_footer_uses_them(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->put('/admin/settings', $this->validData())
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('1900 8888', Setting::get('hotline'));
        $this->assertSame('settings.update', AdminLog::sole()->action);

        $this->get('/support')->assertOk()->assertSee('1900 8888')->assertSee('hotro@zomzop.vn');
        $this->get('/about-us')->assertOk()->assertSee('1900 8888'); // footer
    }

    public function test_validation(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->put('/admin/settings', $this->validData(['email' => 'khong-phai-email', 'footer_facebook' => 'abc']))
            ->assertSessionHasErrors(['email', 'footer_facebook']);

        $this->actingAs($admin)->put('/admin/settings', $this->validData(['payment_cash' => null, 'payment_momo' => null]))
            ->assertSessionHasErrors('payment');
    }

    public function test_disabled_payment_method_is_hidden_and_rejected(): void
    {
        $this->actingAs($this->makeUser('admin'))->put('/admin/settings', $this->validData()); // vnpay không gửi = tắt

        $this->assertSame(['cash', 'momo'], PaymentMethods::enabled());

        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $cart   = ['branch_id' => $branch->id, 'items' => [['id' => $item->id, 'name' => $item->name, 'price' => 50000, 'quantity' => 1, 'image' => '']]];
        $customer = $this->makeUser('customer');

        $this->actingAs($customer)->withSession(['cart' => $cart])->get('/checkout')
            ->assertOk()->assertDontSee('value="vnpay"', false);

        $this->actingAs($customer)->withSession(['cart' => $cart])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'vnpay'])
            ->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
    }
}
