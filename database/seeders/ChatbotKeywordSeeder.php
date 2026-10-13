<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChatbotKeyword;
use Illuminate\Database\Seeder;

class ChatbotKeywordSeeder extends Seeder
{
    public function run(): void
    {
        $firstCategory = Category::where('is_active', true)->orderBy('sort_order')->value('slug');

        $rows = [
            [
                'keywords'   => 'thực đơn, menu, có món gì, xem món',
                'reply'      => 'ZomZop có burger, pizza, gà rán và nhiều món khác. Bấm nút bên dưới để xem thực đơn nhé!',
                'link_url'   => $firstCategory ? "/category/{$firstCategory}" : null,
                'link_label' => $firstCategory ? 'Xem thực đơn' : null,
            ],
            [
                'keywords'   => 'giờ mở cửa, mấy giờ mở, mở cửa, đóng cửa',
                'reply'      => 'Giờ mở cửa có thể khác nhau giữa các chi nhánh. Bạn chọn chi nhánh để xem giờ mở cửa nhé.',
                'link_url'   => '/branches/select',
                'link_label' => 'Chọn chi nhánh',
            ],
            [
                'keywords'   => 'chi nhánh, địa chỉ, ở đâu',
                'reply'      => 'Bạn chọn chi nhánh gần nhất để xem món và giá tại đó nhé.',
                'link_url'   => '/branches/select',
                'link_label' => 'Chọn chi nhánh',
            ],
            [
                'keywords'   => 'mã giảm giá, khuyến mãi, coupon, voucher',
                'reply'      => 'Các mã giảm giá đang có đều ở trang Mã giảm giá, nhập mã khi thanh toán là được.',
                'link_url'   => '/coupons',
                'link_label' => 'Xem mã giảm giá',
            ],
            [
                'keywords'   => 'đặt hàng, cách đặt, đặt món, order',
                'reply'      => "Chọn chi nhánh, thêm món vào giỏ, rồi bấm Thanh toán.\nĐơn xong bạn sẽ có mã lấy hàng để nhận món tại quầy.",
                'link_url'   => '/cart',
                'link_label' => 'Xem giỏ hàng',
            ],
        ];

        foreach ($rows as $row) {
            ChatbotKeyword::updateOrCreate(['keywords' => $row['keywords']], $row + ['is_active' => true]);
        }
    }
}
