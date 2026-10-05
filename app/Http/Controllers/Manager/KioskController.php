<?php

namespace App\Http\Controllers\Manager;

use App\Models\KioskDevice;
use Illuminate\Http\Request;

/** Máy chấm công khuôn mặt đặt ở quầy chi nhánh */
class KioskController extends ManagerController
{
    public function index()
    {
        return view('manager.kiosks.index', [
            'devices' => KioskDevice::where('branch_id', $this->branchId())->orderBy('revoked_at')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']], [
            'name.required' => 'Vui lòng đặt tên thiết bị.',
            'name.max'      => 'Tên thiết bị tối đa 100 ký tự.',
        ]);

        [, $token] = KioskDevice::issue($this->branchId(), $data['name']);

        // Token gốc chỉ hiện đúng 1 lần ở đây; DB chỉ giữ hash
        return redirect()->route('manager.kiosks.index')
            ->with('kiosk_link', url('/kiosk') . '#device=' . $token)   // # : token không bao giờ gửi lên server/log
            ->with('success', "Đã tạo thiết bị {$data['name']}. Mở link bên dưới trên máy quầy.");
    }

    public function revoke(KioskDevice $kiosk)
    {
        abort_if((int) $kiosk->branch_id !== $this->branchId(), 404);

        $kiosk->update(['revoked_at' => now()]);

        return back()->with('success', "Đã thu hồi {$kiosk->name}, máy này không chấm công được nữa.");
    }
}
