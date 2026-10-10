<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SendCouponEmail;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\CouponNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CouponSendController extends AdminController
{
    /** Khách đang hoạt động, đồng ý nhận email, chưa từng được gửi mã này */
    private function recipients(Coupon $coupon, ?int $branchId): Builder
    {
        return User::where('role', 'customer')
            ->where('is_active', true)
            ->where('email_opted_in', true)
            ->when($branchId, fn ($q, $id) => $q->whereHas('orders', fn ($o) => $o->where('branch_id', $id)))
            ->whereNotIn('id', CouponNotification::where('coupon_id', $coupon->id)->where('channel', 'email')->select('user_id'));
    }

    public function create(Coupon $coupon)
    {
        $stats = CouponNotification::where('coupon_id', $coupon->id)->where('channel', 'email')
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.coupons.send', [
            'coupon'   => $coupon,
            'branches' => Branch::orderBy('name')->get(),
            'stats'    => $stats,
            'allCount' => $this->recipients($coupon, null)->count(),
        ]);
    }

    public function store(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'audience'  => ['required', 'in:all,branch'],
            'branch_id' => ['required_if:audience,branch', 'nullable', 'integer', 'exists:branches,id'],
        ], [
            'audience.in'           => 'Đối tượng gửi không hợp lệ.',
            'branch_id.required_if' => 'Vui lòng chọn chi nhánh.',
            'branch_id.exists'      => 'Chi nhánh không tồn tại.',
        ]);

        if (!$coupon->isValid()) {
            return back()->withErrors(['coupon' => "Mã {$coupon->code} đang tắt, hết hạn hoặc hết lượt — không gửi được."]);
        }

        $branchId = $data['audience'] === 'branch' ? (int) $data['branch_id'] : null;
        $count    = 0;

        $this->recipients($coupon, $branchId)->select('id')->chunkById(200, function ($users) use ($coupon, &$count) {
            foreach ($users as $user) {
                $n = CouponNotification::create([
                    'user_id' => $user->id, 'coupon_id' => $coupon->id, 'channel' => 'email', 'status' => 'pending',
                ]);
                SendCouponEmail::dispatch($n->id);
                $count++;
            }
        });

        $this->log('coupon.send', $coupon, ['after' => ['audience' => $data['audience'], 'branch_id' => $branchId, 'recipients' => $count]]);

        return redirect()->route('admin.coupons.index')->with('success', $count
            ? "Đã xếp hàng gửi {$count} email mã {$coupon->code}."
            : 'Không có khách nào mới để gửi (đã gửi hết hoặc chưa ai đồng ý nhận email).');
    }
}
