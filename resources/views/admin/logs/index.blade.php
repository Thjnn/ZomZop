@extends('layouts.admin')

@section('title', 'Nhật ký thao tác')

@section('content')
    <h1 class="text-xl font-bold mb-4">Nhật ký thao tác</h1>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <select name="action" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Mọi thao tác</option>
            @foreach (['branch' => 'Chi nhánh', 'manager' => 'Tài khoản quản lý', 'customer' => 'Khách hàng', 'banner' => 'Banner', 'coupon' => 'Mã giảm giá', 'settings' => 'Cài đặt', 'category' => 'Danh mục', 'menu_item' => 'Món ăn'] as $k => $label)
                <option value="{{ $k }}" @selected($action === $k)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ $date }}" class="px-3 py-2 rounded-lg border border-slate-200">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Thời gian</th><th class="px-4 py-3">Người làm</th><th class="px-4 py-3">Thao tác</th><th class="px-4 py-3">Đối tượng</th><th class="px-4 py-3">Thay đổi</th><th class="px-4 py-3">IP</th></tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b border-slate-50 align-top">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $log->action }}</td>
                        <td class="px-4 py-3">{{ $log->subject_type ? $log->subject_type . ' #' . $log->subject_id : '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($log->changes)
                                <details><summary class="cursor-pointer text-red-500 text-xs">Xem</summary>
                                    <pre class="text-xs bg-slate-50 rounded-lg p-2 mt-1 whitespace-pre-wrap">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Chưa có thao tác nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
