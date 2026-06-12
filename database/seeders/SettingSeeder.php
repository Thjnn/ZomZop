<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── General ──────────────────────────────────────
            ['key' => 'brand_name',    'value' => 'ZomZop',                    'group' => 'general'],
            ['key' => 'brand_slogan',  'value' => 'Ăn ngon mỗi ngày!',         'group' => 'general'],
            ['key' => 'hotline',       'value' => '1900 1234',                  'group' => 'general'],
            ['key' => 'email',         'value' => 'contact@zomzop.vn',          'group' => 'general'],
            ['key' => 'logo',          'value' => 'logo.png',                   'group' => 'general'],
            ['key' => 'favicon',       'value' => 'favicon.ico',                'group' => 'general'],
            ['key' => 'theme_color',   'value' => '#E53935',                    'group' => 'general'],

            // ── Navbar ───────────────────────────────────────
            ['key' => 'navbar_show_search',   'value' => '1', 'group' => 'navbar'],
            ['key' => 'navbar_show_cart',     'value' => '1', 'group' => 'navbar'],
            ['key' => 'navbar_show_wishlist', 'value' => '1', 'group' => 'navbar'],

            // ── Footer ───────────────────────────────────────
            ['key' => 'footer_about',    'value' => 'ZomZop — Chuỗi cửa hàng đồ ăn nhanh hàng đầu Việt Nam.', 'group' => 'footer'],
            ['key' => 'footer_facebook', 'value' => 'https://facebook.com/zomzop', 'group' => 'footer'],
            ['key' => 'footer_zalo',     'value' => 'https://zalo.me/zomzop',      'group' => 'footer'],

            // ── SEO ──────────────────────────────────────────
            ['key' => 'seo_title',       'value' => 'ZomZop — Đặt đồ ăn nhanh online',          'group' => 'seo'],
            ['key' => 'seo_description', 'value' => 'Đặt burger, pizza, gà chiên tươi ngon, giao nhanh tận nơi.', 'group' => 'seo'],
            ['key' => 'seo_keywords',    'value' => 'đặt đồ ăn, burger, pizza, gà chiên, fast food', 'group' => 'seo'],

            // ── Payment ──────────────────────────────────────
            ['key' => 'payment_cash',   'value' => '1', 'group' => 'payment'],
            ['key' => 'payment_momo',   'value' => '1', 'group' => 'payment'],
            ['key' => 'payment_vnpay',  'value' => '1', 'group' => 'payment'],

            // ── CMS ──────────────────────────────────────────
            ['key' => 'cms_about',      'value' => 'ZomZop được thành lập năm 2024, với sứ mệnh mang đến những bữa ăn nhanh chất lượng cao cho người Việt.', 'group' => 'cms'],
            ['key' => 'cms_policy',     'value' => 'Chúng tôi cam kết giao hàng trong vòng 30 phút hoặc hoàn tiền.', 'group' => 'cms'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
