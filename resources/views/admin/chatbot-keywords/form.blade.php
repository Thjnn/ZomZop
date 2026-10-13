@extends('layouts.admin')

@section('title', $keyword ? 'Sửa từ khoá' : 'Thêm từ khoá')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $keyword ? 'Sửa từ khoá' : 'Thêm từ khoá' }}</h1>

    <form method="POST"
          action="{{ $keyword ? route('admin.chatbot-keywords.update', $keyword) : route('admin.chatbot-keywords.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($keyword) @method('PUT') @endif

        <label class="grid gap-1">Từ khoá (nhiều từ cách nhau dấu phẩy; có dấu hay không dấu đều được)
            <input name="keywords" value="{{ old('keywords', $keyword?->keywords) }}" required maxlength="255" placeholder="thực đơn, menu, có món gì" class="{{ $input }}">
        </label>
        <label class="grid gap-1">Câu trả lời
            <textarea name="reply" rows="4" required maxlength="1000" class="{{ $input }}">{{ old('reply', $keyword?->reply) }}</textarea>
        </label>
        <div class="grid sm:grid-cols-2 gap-4">
            <label class="grid gap-1">Link nút (tuỳ chọn, trang trong web, VD: /coupons)
                <input name="link_url" value="{{ old('link_url', $keyword?->link_url) }}" maxlength="255" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Chữ trên nút
                <input name="link_label" value="{{ old('link_label', $keyword?->link_label) }}" maxlength="50" placeholder="Xem thực đơn" class="{{ $input }}">
            </label>
        </div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $keyword?->is_active ?? true)) class="accent-red-500"> Đang bật
        </label>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.chatbot-keywords.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
