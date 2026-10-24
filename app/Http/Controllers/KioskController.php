<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\KioskDevice;
use App\Services\FaceMatcher;
use App\Services\FacePunch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/** Trang chấm công khuôn mặt chạy trên máy quầy + API của nó */
class KioskController extends Controller
{
    /** Luật chung cho descriptor 128 số của face-api.js */
    public const DESCRIPTOR_RULES = [
        'descriptor'   => ['required', 'array', 'size:128'],
        'descriptor.*' => ['required', 'numeric', 'between:-1,1'],
    ];

    private function device(Request $request): KioskDevice
    {
        return $request->attributes->get('kiosk');
    }

    public function status(Request $request)
    {
        $device = $this->device($request);

        return response()->json(['branch' => $device->branch->name, 'device' => $device->name]);
    }

    public function punch(Request $request, FaceMatcher $matcher, FacePunch $punch)
    {
        $data = $request->validate(self::DESCRIPTOR_RULES + [
            'action' => ['required', 'in:in,out'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $device = $this->device($request);

        $match = $matcher->match($device->branch_id, array_map('floatval', $data['descriptor']));
        if (!$match) {
            return response()->json(['status' => 'not_recognized']);
        }

        $result = $punch->punch($device, $match['user'], $match['confidence'], $data['action'], $data['reason'] ?? null);
        $a      = $result['attendance'];

        if (in_array($result['status'], ['checked_in', 'checked_out'], true)) {
            $this->savePhoto($request, $a, $result['status'] === 'checked_in' ? 'in' : 'out');
        }

        return response()->json([
            'status'     => $result['status'],
            'name'       => $match['user']->name,
            'confidence' => $match['confidence'],
            'shift'      => $a?->shift?->name,
            // Giờ của lượt liên quan: giờ ra nếu đã ra, không thì giờ vào; chưa có lượt thì giờ hiện tại
            'time'       => ($a?->check_out ?? $a?->check_in ?? now())->format('H:i'),
            'hours'      => $a?->check_out ? $a->working_hours : null,
            // Số phút trễ/sớm khi cần hỏi lý do (need_*_reason)
            'minutes'    => $result['minutes'] ?? null,
            'late_minutes'  => $result['status'] === 'checked_in' ? $a->lateMinutes() : null,
            'early_minutes' => $result['status'] === 'checked_out' ? $a->earlyMinutes() : null,
        ]);
    }

    /** Ảnh bằng chứng: ảnh lỗi/thiếu thì bỏ qua, không bắt nhân viên chấm lại */
    private function savePhoto(Request $request, Attendance $attendance, string $kind): void
    {
        $ok = Validator::make($request->only('photo'), ['photo' => ['required', 'image', 'mimes:jpeg,jpg', 'max:200']])->passes();
        if (!$ok) {
            return;
        }

        $path = $attendance->photoFile($kind);
        Storage::disk('local')->putFileAs(dirname($path), $request->file('photo'), basename($path));

        if ($kind === 'in') {
            $attendance->update(['photo_path' => $path]);
        }
    }
}
