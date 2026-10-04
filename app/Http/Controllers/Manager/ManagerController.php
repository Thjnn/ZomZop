<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shift;
use App\Models\User;

abstract class ManagerController extends Controller
{
    /** Chi nhánh của manager đang đăng nhập */
    protected function branchId(): int
    {
        $branchId = auth()->user()->branch_id;

        abort_if(!$branchId, 403, 'Tài khoản quản lý chưa được gán chi nhánh.');

        return (int) $branchId;
    }

    /** Đơn của chi nhánh khác coi như không tồn tại */
    protected function ensureSameBranch(Order $order): void
    {
        abort_if((int) $order->branch_id !== $this->branchId(), 404);
    }

    /** Chỉ staff/kitchen của chi nhánh mình; còn lại coi như không tồn tại */
    protected function ensureOwnStaff(User $user): void
    {
        abort_unless(
            in_array($user->role, ['staff', 'kitchen'], true) && (int) $user->branch_id === $this->branchId(),
            404
        );
    }

    protected function ensureOwnShift(Shift $shift): void
    {
        abort_if((int) $shift->branch_id !== $this->branchId(), 404);
    }
}
