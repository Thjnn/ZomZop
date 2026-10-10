@extends('layouts.admin')

@section('title', 'Chi nhánh')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Chi nhánh</h1>
        <a href="{{ route('admin.branches.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Thêm chi nhánh</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Tên</th><th class="px-4 py-3">Địa chỉ</th><th class="px-4 py-3">SĐT</th>
                    <th class="px-4 py-3">Giờ mở</th><th class="px-4 py-3">Quản lý</th><th class="px-4 py-3">Nhân viên</th>
                    <th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($branches as $b)
                    <tr class="border-b border-slate-50 {{ $b->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-semibold">{{ $b->name }}</td>
                        <td class="px-4 py-3">{{ $b->address }}</td>
                        <td class="px-4 py-3">{{ $b->phone ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ substr($b->open_time, 0, 5) }} – {{ substr($b->close_time, 0, 5) }}</td>
                        <td class="px-4 py-3">{{ $b->managers_count }}</td>
                        <td class="px-4 py-3">{{ $b->staff_count }}</td>
                        <td class="px-4 py-3">{{ $b->is_active ? 'Đang mở' : 'Đã đóng' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.branches.edit', $b) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.branches.toggle', $b) }}" class="inline"
                                  onsubmit="return {{ $b->is_active ? 'confirm(\'Đóng chi nhánh này? Khách sẽ không đặt được đơn.\')' : 'true' }}">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $b->is_active ? 'Đóng' : 'Mở lại' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Chưa có chi nhánh nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
