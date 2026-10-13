@extends('layouts.admin')

@section('title', 'Liên hệ')

@section('content')
    <div class="mb-4">
        <h1 class="text-xl font-bold">Tin nhắn liên hệ</h1>
        <p class="text-sm text-slate-400">Gửi từ form liên hệ cuối trang chủ. Tin chưa xử lý hiện trước.</p>
    </div>

    <div class="space-y-3">
        @forelse ($messages as $m)
            <article class="bg-white rounded-2xl border p-4 {{ $m->handled_at ? 'border-slate-100 opacity-70' : 'border-amber-200' }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $m->name }}
                            @unless ($m->handled_at)<span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">Mới</span>@endunless
                        </p>
                        <p class="text-xs text-slate-400">
                            <a href="mailto:{{ $m->email }}" class="hover:text-red-500">{{ $m->email }}</a>
                            @if ($m->phone) · <a href="tel:{{ $m->phone }}" class="hover:text-red-500">{{ $m->phone }}</a> @endif
                            · {{ $m->created_at->format('H:i d/m/Y') }}
                            @if ($m->user_id) · khách đã đăng nhập @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.contact-messages.toggle', $m) }}">
                        @csrf @method('PATCH')
                        <button class="text-sm px-3 py-1.5 rounded-lg cursor-pointer {{ $m->handled_at ? 'bg-slate-100 hover:bg-slate-200 text-slate-600' : 'bg-red-500 hover:bg-red-600 text-white' }}">
                            {{ $m->handled_at ? 'Đánh dấu chưa xử lý' : 'Đã xử lý' }}
                        </button>
                    </form>
                </div>
                <p class="mt-3 text-sm text-slate-700 whitespace-pre-line">{{ $m->message }}</p>
            </article>
        @empty
            <p class="bg-white rounded-2xl border border-slate-100 px-4 py-10 text-center text-slate-400">Chưa có tin nhắn liên hệ nào.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
