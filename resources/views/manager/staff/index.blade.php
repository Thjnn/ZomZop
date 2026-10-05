@extends('layouts.manager')

@section('title', 'Nhân viên')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Nhân viên chi nhánh</h1>
        <a href="{{ route('manager.staff.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Tạo tài khoản</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Họ tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">SĐT</th>
                    <th class="px-4 py-3">Vai trò</th><th class="px-4 py-3">Lương/giờ</th><th class="px-4 py-3">Khuôn mặt</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staff as $u)
                    <tr class="border-b border-slate-50 {{ $u->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">{{ $u->phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $roles[$u->role] ?? $u->role }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $u->latestSalary ? number_format($u->latestSalary->rate, 0, ',', '.') . 'đ/giờ' : '—' }}
                            @if ($u->isOnProbation(today()))
                                <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Thử việc đến {{ $u->probationEndsAt()->format('d/m') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('manager.staff.face', $u) }}" class="hover:text-red-500 {{ $u->face_descriptors_count ? '' : 'text-slate-400' }}">
                                {{ $u->face_descriptors_count ? $u->face_descriptors_count . '/' . config('attendance.face.max_samples') . ' mẫu' : 'Chưa đăng ký' }}
                            </a>
                            @if (!$u->is_active && $u->face_descriptors_count)
                                <span class="block text-xs text-amber-600">Đã khoá — nên xoá dữ liệu khuôn mặt</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $u->is_active ? 'Đang làm' : 'Đã khoá' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('manager.staff.edit', $u) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('manager.staff.lock', $u) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $u->is_active ? 'Khoá' : 'Mở khoá' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Chưa có nhân viên nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
