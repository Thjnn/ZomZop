<?php

namespace App\Http\Controllers;

use App\Models\KioskDevice;
use App\Services\FaceMatcher;
use App\Services\FacePunch;
use Illuminate\Http\Request;

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
        $data   = $request->validate(self::DESCRIPTOR_RULES + ['force_checkout' => ['nullable', 'boolean']]);
        $device = $this->device($request);

        $match = $matcher->match($device->branch_id, array_map('floatval', $data['descriptor']));
        if (!$match) {
            return response()->json(['status' => 'not_recognized']);
        }

        $result = $punch->punch($device, $match['user'], $match['confidence'], (bool) ($data['force_checkout'] ?? false));
        $a      = $result['attendance'];

        return response()->json([
            'status'     => $result['status'],
            'name'       => $match['user']->name,
            'confidence' => $match['confidence'],
            'shift'      => $a?->shift?->name,
            'time'       => match ($result['status']) {
                'checked_out' => $a->check_out->format('H:i'),
                'checked_in', 'confirm_checkout', 'duplicate' => ($a->check_out ?? $a->check_in)->format('H:i'),
                default => now()->format('H:i'),
            },
            'hours'      => $a?->check_out ? $a->working_hours : null,
        ]);
    }
}
