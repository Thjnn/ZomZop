@extends('layouts.admin')

@section('title', 'Nhân viên')

@section('content')
    <h1 class="text-xl font-bold mb-1">Nhân viên toàn chuỗi</h1>
    <p class="text-sm text-slate-500 mb-4">Chỉ xem. Thêm, sửa, khoá nhân viên do quản lý từng chi nhánh thực hiện.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Tất cả chi nhánh</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}" @selected(($filters['branch_id'] ?? null) == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên hoặc email" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Họ tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Chi nhánh</th><th class="px-4 py-3">Vai trò</th><th class="px-4 py-3">Lương/giờ</th><th class="px-4 py-3">Trạng thái</th></tr>
            </thead>
            <tbody>
                @forelse ($staff as $u)
                    <tr class="border-b border-slate-50 {{ $u->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">{{ $u->branch?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $roles[$u->role] ?? $u->role }}</td>
                        <td class="px-4 py-3">{{ $u->latestSalary ? number_format($u->latestSalary->rate, 0, ',', '.') . 'đ' : '—' }}</td>
                        <td class="px-4 py-3">{{ $u->is_active ? 'Đang làm' : 'Đã nghỉ' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Không có nhân viên nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $staff->links() }}</div>
@endsection
