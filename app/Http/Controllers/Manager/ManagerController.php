<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;

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
}
