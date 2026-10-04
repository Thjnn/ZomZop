<?php

namespace App\Http\Controllers\Manager;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends ManagerController
{
    private function validated(Request $request): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:50'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'different:start_time'],
        ], [
            'name.required'          => 'Vui lòng nhập tên ca.',
            'start_time.date_format' => 'Giờ bắt đầu không hợp lệ (HH:MM).',
            'end_time.date_format'   => 'Giờ kết thúc không hợp lệ (HH:MM).',
            'end_time.different'     => 'Giờ kết thúc phải khác giờ bắt đầu.',
        ]);
    }

    public function index()
    {
        $shifts = Shift::ofBranch($this->branchId())->withCount('attendances')->orderBy('start_time')->get();

        return view('manager.shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Shift::create($data + ['branch_id' => $this->branchId()]);

        return back()->with('success', "Đã thêm {$data['name']}.");
    }

    public function update(Request $request, Shift $shift)
    {
        $this->ensureOwnShift($shift);
        $shift->update($this->validated($request));

        return back()->with('success', "Đã cập nhật {$shift->name}.");
    }

    public function destroy(Shift $shift)
    {
        $this->ensureOwnShift($shift);

        // attendances xoá dây chuyền theo ca → chặn để không mất dữ liệu công
        if ($shift->attendances()->exists()) {
            return back()->withErrors(['shift' => "Không xoá được {$shift->name} vì đã có chấm công. Hãy đổi tên/giờ thay vì xoá."]);
        }

        $shift->delete();

        return back()->with('success', 'Đã xoá ca.');
    }
}
