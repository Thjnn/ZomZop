<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

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
            'branch_id' => 'required|exists:branches,id',
        ]);

        $branch = Branch::findOrFail($request->branch_id);

        session([
            'selected_branch_id'   => $branch->id,
            'selected_branch_name' => $branch->name,
        ]);

        // Quay về trang trước hoặc trang chủ
        return redirect()->intended(route('home'));
    }
}
