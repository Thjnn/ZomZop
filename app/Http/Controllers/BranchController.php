<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Trang chọn chi nhánh
     */
    public function select()
    {
        $branches = Branch::where('is_active', true)->get();

        return view('branches.select', compact('branches'));
    }

    /**
     * Lưu chi nhánh đã chọn vào session
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('is_active', true)],
        ], [
            'branch_id.exists' => 'Chi nhánh này đang tạm đóng, vui lòng chọn chi nhánh khác.',
        ]);

        $branch = Branch::findOrFail($request->branch_id);

        session([
            'selected_branch_id'   => $branch->id,
            'selected_branch_name' => $branch->name,
        ]);

        // Về trang chủ (không dùng intended: đó là trang chờ đăng nhập, không phải trang chờ chọn chi nhánh)
        return redirect()->route('home');
    }
}
