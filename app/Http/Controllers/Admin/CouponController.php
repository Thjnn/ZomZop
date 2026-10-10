<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CouponController extends AdminController
{
    /** Không đổi được khi mã đã có người dùng */
    private const LOCKED_WHEN_USED = ['code', 'type', 'value', 'max_discount'];

    private const MESSAGES = [
        'code.required'          => 'Vui lòng nhập mã.',
        'code.regex'             => 'Mã chỉ gồm chữ không dấu và số, 4–20 ký tự.',
        'code.unique'            => 'Mã này đã tồn tại.',
        'type.in'                => 'Loại mã không hợp lệ.',
        'value.required'         => 'Vui lòng nhập giá trị giảm.',
        'value.max'              => 'Giảm theo % chỉ từ 1 đến 100.',
        'value.min'              => 'Giảm tiền tối thiểu 1.000đ; giảm % tối thiểu 1.',
        'max_discount.*'         => 'Giảm tối đa phải từ 1.000đ trở lên.',
        'min_order_value.*'      => 'Đơn tối thiểu là số nguyên ≥ 0.',
        'max_uses.*'             => 'Tổng lượt dùng là số nguyên ≥ 0 (0 = không giới hạn).',
        'max_uses_per_user.*'    => 'Lượt mỗi khách từ 1 đến 100.',
        'expired_at.after'       => 'Ngày hết hạn phải sau ngày bắt đầu.',
    ];

    private function rules(Request $request, ?Coupon $coupon = null): array
    {
        $percent    = $request->input('type') === 'percent';
        $afterStart = $request->filled('started_at') ? ['after:started_at'] : [];

        return [
            'code'              => ['required', 'regex:/^[A-Z0-9]{4,20}$/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'type'              => ['required', Rule::in(array_keys(Coupon::TYPES))],
            'value'             => ['required', 'integer', ...($percent ? ['min:1', 'max:100'] : ['min:1000', 'max:10000000'])],
            'max_discount'      => ['nullable', 'integer', 'min:1000', 'max:10000000'],
            'min_order_value'   => ['required', 'integer', 'min:0', 'max:100000000'],
            'max_uses'          => ['required', 'integer', 'min:0', 'max:1000000'],
            'max_uses_per_user' => ['required', 'integer', 'min:1', 'max:100'],
            'started_at'        => ['nullable', 'date'],
            'expired_at'        => ['nullable', 'date', ...$afterStart],
        ];
    }

    private function payload(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $data = $request->validate($this->rules($request, $coupon), self::MESSAGES);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_public'] = $request->boolean('is_public');
        if ($data['type'] === 'fixed') {
            $data['max_discount'] = null;
        }
        if (!empty($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at'])->endOfDay(); // dùng được hết ngày hết hạn
        }

        return $data;
    }

    public function index(Request $request)
    {
        $coupons = Coupon::latest()->paginate(20);

        return view('admin.coupons.index', ['coupons' => $coupons, 'types' => Coupon::TYPES]);
    }

    public function create()
    {
        return view('admin.coupons.form', ['coupon' => null, 'types' => Coupon::TYPES]);
    }

    public function store(Request $request)
    {
        $data   = $this->payload($request);
        $coupon = Coupon::create($data);
        $this->log('coupon.create', $coupon, ['after' => $data]);

        return redirect()->route('admin.coupons.index')->with('success', "Đã tạo mã {$coupon->code}.");
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', ['coupon' => $coupon, 'types' => Coupon::TYPES]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        if ($coupon->isUsed()) {
            // Giữ nguyên các trường bị khoá: điền giá trị cũ để validate không báo lỗi oan
            $request->merge(Arr::only($coupon->getAttributes(), self::LOCKED_WHEN_USED));
        }
        $data = $this->payload($request, $coupon);
        if ($coupon->isUsed()) {
            $data = Arr::except($data, self::LOCKED_WHEN_USED);
        }

        $coupon->fill($data);
        $changes = AdminLog::diff($coupon);
        $coupon->save();
        $this->log('coupon.update', $coupon, $changes);

        return redirect()->route('admin.coupons.index')->with('success', "Đã cập nhật mã {$coupon->code}.");
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->is_active = !$coupon->is_active;
        $changes = AdminLog::diff($coupon);
        $coupon->save();
        $this->log($coupon->is_active ? 'coupon.enable' : 'coupon.disable', $coupon, $changes);

        return back()->with('success', $coupon->is_active ? "Đã bật mã {$coupon->code}." : "Đã tắt mã {$coupon->code}.");
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->isUsed()) {
            return back()->withErrors(['coupon' => "Mã {$coupon->code} đã có người dùng, chỉ có thể tắt."]);
        }

        $coupon->delete();
        $this->log('coupon.delete', $coupon, ['before' => $coupon->only(['code', 'type', 'value'])]);

        return back()->with('success', "Đã xoá mã {$coupon->code}.");
    }
}
