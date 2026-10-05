<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\KioskController;
use App\Models\User;
use App\Services\FaceMatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Manager đăng ký khuôn mặt cho nhân viên (chỉ lưu descriptor 128 số, không lưu ảnh) */
class FaceController extends ManagerController
{
    public function show(User $user)
    {
        $this->ensureOwnStaff($user);

        return view('manager.staff.face', [
            'user'  => $user,
            'count' => $user->faceDescriptors()->count(),
            'max'   => config('attendance.face.max_samples'),
        ]);
    }

    public function store(Request $request, User $user, FaceMatcher $matcher)
    {
        $this->ensureOwnStaff($user);

        $data = $request->validate(KioskController::DESCRIPTOR_RULES + ['consent' => ['accepted']], [
            'consent.accepted' => 'Cần xác nhận nhân viên đã đồng ý.',
            'descriptor.*'     => 'Dữ liệu khuôn mặt không hợp lệ, hãy chụp lại.',
        ]);
        $descriptor = array_map('floatval', $data['descriptor']);

        if ($user->faceDescriptors()->count() >= config('attendance.face.max_samples')) {
            throw ValidationException::withMessages(['descriptor' => 'Đã đủ ' . config('attendance.face.max_samples') . ' mẫu. Xoá mẫu cũ nếu muốn chụp lại.']);
        }

        if ($dup = $matcher->nearestOther($this->branchId(), $descriptor, $user->id)) {
            throw ValidationException::withMessages(['descriptor' => "Khuôn mặt này giống nhân viên {$dup['user']->name} đã đăng ký."]);
        }

        $user->faceDescriptors()->create(['descriptor' => $descriptor]);

        return response()->json(['count' => $user->faceDescriptors()->count()]);
    }

    public function destroy(User $user)
    {
        $this->ensureOwnStaff($user);

        $user->faceDescriptors()->delete();

        return back()->with('success', "Đã xoá dữ liệu khuôn mặt của {$user->name}.");
    }
}
