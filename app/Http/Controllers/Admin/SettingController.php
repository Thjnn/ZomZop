<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Support\PaymentMethods;
use Illuminate\Http\Request;

class SettingController extends AdminController
{
    /** key => [nhãn, rule validate, giá trị mặc định khi chưa có trong DB] */
    public const FIELDS = [
        'brand_name'      => ['Tên thương hiệu', ['required', 'string', 'max:50'], 'ZomZop'],
        'brand_slogan'    => ['Khẩu hiệu', ['nullable', 'string', 'max:100'], ''],
        'hotline'         => ['Hotline', ['required', 'string', 'max:20', 'regex:/^[0-9 +.]{6,20}$/'], '1900 1234'],
        'email'           => ['Email hỗ trợ', ['required', 'email', 'max:150'], 'contact@zomzop.vn'],
        'address'         => ['Địa chỉ trụ sở', ['nullable', 'string', 'max:255'], ''],
        'open_hours'      => ['Giờ mở cửa', ['nullable', 'string', 'max:50'], '09:00 - 22:00'],
        'footer_about'    => ['Giới thiệu ở chân trang', ['nullable', 'string', 'max:500'], ''],
        'footer_facebook' => ['Link Facebook', ['nullable', 'url', 'max:255'], ''],
        'footer_zalo'     => ['Link Zalo', ['nullable', 'url', 'max:255'], ''],
    ];

    public function edit()
    {
        $values = collect(self::FIELDS)->mapWithKeys(fn ($f, $key) => [$key => Setting::get($key, $f[2])]);

        return view('admin.settings.edit', [
            'fields'   => self::FIELDS,
            'values'   => $values,
            'payments' => PaymentMethods::LABELS,
            'enabled'  => PaymentMethods::enabled(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(
            collect(self::FIELDS)->map(fn ($f) => $f[1])->all(),
            [
                'required'      => ':attribute không được để trống.',
                'email'         => 'Email không hợp lệ.',
                'url'           => ':attribute phải là đường dẫn đầy đủ (https://…).',
                'hotline.regex' => 'Hotline chỉ gồm số, khoảng trắng, dấu + hoặc dấu chấm.',
                'max'           => ':attribute quá dài.',
            ],
            collect(self::FIELDS)->map(fn ($f) => $f[0])->all(),
        );

        $payments = collect(PaymentMethods::LABELS)->keys()->mapWithKeys(fn ($k) => ["payment_{$k}" => $request->boolean("payment_{$k}") ? '1' : '0']);
        if (!$payments->contains('1')) {
            return back()->withInput()->withErrors(['payment' => 'Phải bật ít nhất một phương thức thanh toán.']);
        }

        $before = [];
        $after  = [];
        foreach ($payments->all() + array_map(fn ($v) => (string) $v, $data) as $key => $value) {
            $old = (string) Setting::get($key, self::FIELDS[$key][2] ?? '1');
            if ($old !== $value) {
                $before[$key] = $old;
                $after[$key]  = $value;
                Setting::set($key, $value);
            }
        }
        $this->log('settings.update', null, ['before' => $before, 'after' => $after]);

        return redirect()->route('admin.settings.edit')->with('success', 'Đã lưu cài đặt.');
    }
}
