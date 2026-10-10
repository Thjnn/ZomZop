<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CouponManageTest extends TestCase
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
            'code' => 'thang10', 'type' => 'percent', 'value' => 15, 'max_discount' => 50000,
            'min_order_value' => 100000, 'max_uses' => 100, 'max_uses_per_user' => 1,
            'started_at' => '2026-10-01', 'expired_at' => '2026-10-31', 'is_active' => '1', 'is_public' => '1',
        ], $over);
    }

    public function test_admin_creates_coupon_code_uppercased(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/coupons', $this->validData())
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::sole();
        $this->assertSame('THANG10', $coupon->code);
        $this->assertSame(50000, $coupon->max_discount);
        $this->assertSame('coupon.create', AdminLog::sole()->action);
    }

    public function test_validation(): void
    {
        Coupon::create(['code' => 'TRUNG', 'type' => 'fixed', 'value' => 10000]);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['code' => 'trung']))->assertSessionHasErrors('code');
        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['code' => 'có dấu!']))->assertSessionHasErrors('code');
        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['value' => 120]))->assertSessionHasErrors('value');
        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['type' => 'fixed', 'value' => 500]))->assertSessionHasErrors('value');
        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['expired_at' => '2026-09-01']))->assertSessionHasErrors('expired_at');
    }

    public function test_fixed_coupon_has_no_max_discount(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/coupons', $this->validData(['type' => 'fixed', 'value' => 20000, 'max_discount' => 5000]));

        $this->assertNull(Coupon::sole()->max_discount);
    }

    public function test_used_coupon_keeps_code_type_value(): void
    {
        $coupon = Coupon::create(['code' => 'DADUNG', 'type' => 'percent', 'value' => 10, 'used_count' => 3]);

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/coupons/{$coupon->id}", $this->validData(['code' => 'KHAC', 'value' => 90, 'max_uses' => 500]))
            ->assertRedirect(route('admin.coupons.index'));

        $coupon->refresh();
        $this->assertSame('DADUNG', $coupon->code);
        $this->assertSame(10, $coupon->value);
        $this->assertSame(500, $coupon->max_uses); // các trường khác vẫn sửa được
    }

    public function test_cannot_delete_used_coupon_but_can_delete_unused(): void
    {
        $used   = Coupon::create(['code' => 'DADUNG', 'type' => 'fixed', 'value' => 10000, 'used_count' => 1]);
        $unused = Coupon::create(['code' => 'CHUADUNG', 'type' => 'fixed', 'value' => 10000]);
        $admin  = $this->makeUser('admin');

        $this->actingAs($admin)->delete("/admin/coupons/{$used->id}")->assertSessionHasErrors();
        $this->actingAs($admin)->delete("/admin/coupons/{$unused->id}")->assertRedirect();

        $this->assertModelExists($used);
        $this->assertModelMissing($unused);
    }

    public function test_toggle(): void
    {
        $coupon = Coupon::create(['code' => 'BAT', 'type' => 'fixed', 'value' => 10000, 'is_active' => true]);

        $this->actingAs($this->makeUser('admin'))->patch("/admin/coupons/{$coupon->id}/toggle")->assertRedirect();

        $this->assertFalse($coupon->fresh()->is_active);
    }

    public function test_calc_discount_respects_min_and_max(): void
    {
        $c = new Coupon(['type' => 'percent', 'value' => 20, 'max_discount' => 30000, 'min_order_value' => 100000]);

        $this->assertSame(0, $c->calcDiscount(90000));       // dưới đơn tối thiểu
        $this->assertSame(24000, $c->calcDiscount(120000));  // 20%
        $this->assertSame(30000, $c->calcDiscount(500000));  // chạm trần

        $f = new Coupon(['type' => 'fixed', 'value' => 50000, 'min_order_value' => 0]);
        $this->assertSame(40000, $f->calcDiscount(40000));   // không giảm quá tiền đơn
    }
}
