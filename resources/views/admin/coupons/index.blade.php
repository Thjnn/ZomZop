@extends('layouts.admin')

@section('title', 'Mã giảm giá')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Mã giảm giá</h1>
        <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Tạo mã</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Mã</th><th class="px-4 py-3">Giảm</th><th class="px-4 py-3">Đơn tối thiểu</th><th class="px-4 py-3">Đã dùng</th><th class="px-4 py-3">Hiệu lực</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($coupons as $c)
                    <tr class="border-b border-slate-50 {{ $c->isValid() ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-mono font-semibold">{{ $c->code }} @unless ($c->is_public) <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-slate-100 font-sans">Riêng</span> @endunless</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $c->type === 'percent' ? $c->value . '%' : number_format($c->value, 0, ',', '.') . 'đ' }}
                            @if ($c->max_discount) <span class="text-xs text-slate-400">(tối đa {{ number_format($c->max_discount, 0, ',', '.') }}đ)</span> @endif
                        </td>
                        <td class="px-4 py-3">{{ number_format($c->min_order_value, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3">{{ $c->used_count }}{{ $c->max_uses ? ' / ' . $c->max_uses : '' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $c->started_at?->format('d/m/Y') ?? '…' }} → {{ $c->expired_at?->format('d/m/Y') ?? '…' }}</td>
                        <td class="px-4 py-3">{{ $c->isValid() ? 'Dùng được' : ($c->is_active ? 'Hết hạn/hết lượt' : 'Đã tắt') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            @if (Route::has('admin.coupons.send.create'))
                                <a href="{{ route('admin.coupons.send.create', $c) }}" class="text-red-500 hover:underline mr-3">Gửi email</a>
                            @endif
                            <a href="{{ route('admin.coupons.edit', $c) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.coupons.toggle', $c) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer mr-3">{{ $c->is_active ? 'Tắt' : 'Bật' }}</button>
                            </form>
                            @unless ($c->isUsed())
                                <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}" class="inline" onsubmit="return confirm('Xoá mã {{ $c->code }}?')">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-500 hover:underline cursor-pointer">Xoá</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Chưa có mã nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>
@endsection
