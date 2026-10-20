<?php

namespace App\Http\Controllers\Manager;

use App\Models\User;
use App\Services\FaceMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'min'   => config('attendance.face.min_samples'),
        ]);
    }

    /** Nhận cả bộ min..max mẫu (camera tự chụp 15, hoặc từ ảnh tải lên), hợp lệ thì thay toàn bộ mẫu cũ của nhân viên */
    public function store(Request $request, User $user, FaceMatcher $matcher)
    {
        $this->ensureOwnStaff($user);

        $min  = config('attendance.face.min_samples');
        $max  = config('attendance.face.max_samples');
        $data = $request->validate([
            'descriptors'     => ['required', 'array', "between:{$min},{$max}"],
            'descriptors.*'   => ['required', 'array', 'size:128'],
            'descriptors.*.*' => ['required', 'numeric', 'between:-1,1'],
            'consent'         => ['accepted'],
        ], [
            'consent.accepted'  => 'Cần xác nhận nhân viên đã đồng ý.',
            'descriptors.between' => "Cần từ {$min} đến {$max} mẫu hợp lệ.",
            'descriptors.*'     => 'Dữ liệu khuôn mặt không hợp lệ, hãy chụp lại.',
            'descriptors.*.*'   => 'Dữ liệu khuôn mặt không hợp lệ, hãy chụp lại.',
        ]);
        $samples = array_map(fn ($d) => array_map('floatval', $d), $data['descriptors']);

        $center = array_map(fn (...$col) => array_sum($col) / count($col), ...$samples);
        foreach ($samples as $s) {
            if (FaceMatcher::distance($s, $center) > config('attendance.face.threshold')) {
                throw ValidationException::withMessages(['descriptors' => 'Các mẫu không cùng một người, hãy chụp lại.']);
            }
        }

        if ($dup = $matcher->nearestOther($center, $user->id)) {
            throw ValidationException::withMessages(['descriptors' => "Khuôn mặt này giống nhân viên {$dup['user']->name} đã đăng ký."]);
        }

        DB::transaction(function () use ($user, $samples) {
            $user->faceDescriptors()->delete();
            foreach ($samples as $s) {
                $user->faceDescriptors()->create(['descriptor' => $s]);
            }
        });

        return response()->json(['count' => $user->faceDescriptors()->count()]);
    }

    public function destroy(User $user)
    {
        $this->ensureOwnStaff($user);

        $user->faceDescriptors()->delete();
        // Kể cả lượt chấm vào tay nhưng chấm ra ở máy quầy (có ảnh "ra")
        $user->attendances()->get()->each->deletePhotos();

        return back()->with('success', "Đã xoá dữ liệu khuôn mặt và ảnh chấm công của {$user->name}.");
    }
}
