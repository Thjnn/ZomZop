<?php

return [
    // Phí giao hàng (đồng). Đơn mang đi không tính phí.
    'shipping_fee' => [
        'standard' => 15000,   // giao tiêu chuẩn
        'express'  => 30000,   // giao hỏa tốc
    ],

    // Slug danh mục được bán kèm topping; món danh mục khác không hiện / không nhận topping
    'topping_categories' => ['burger'],

    // Số phần tối đa của MỖI loại topping trên một món (vd 5 miếng thịt bò)
    'topping_max_qty' => 5,
];
