@extends('layouts.admin')

@section('title', 'Chatbot')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold">Từ khoá chatbot</h1>
            <p class="text-sm text-slate-400">Tin nhắn chứa từ khoá → trả câu soạn sẵn. Không khớp từ khoá nào thì Gemini trả lời.</p>
        </div>
        <a href="{{ route('admin.chatbot-keywords.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold shrink-0">+ Thêm từ khoá</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 font-medium">Từ khoá</th>
                    <th class="px-4 py-3 font-medium">Câu trả lời</th>
                    <th class="px-4 py-3 font-medium">Nút link</th>
                    <th class="px-4 py-3 font-medium">Trạng thái</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($keywords as $k)
                    <tr class="border-b border-slate-50 align-top">
                        <td class="px-4 py-3 font-medium">{{ $k->keywords }}</td>
                        <td class="px-4 py-3 text-slate-600 max-w-sm">{{ \Illuminate\Support\Str::limit($k->reply, 120) }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            @if ($k->link_url) {{ $k->link_label }} <span class="text-slate-400">→ {{ $k->link_url }}</span> @else — @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $k->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ $k->is_active ? 'Đang bật' : 'Đang tắt' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3 justify-end whitespace-nowrap">
                                <a href="{{ route('admin.chatbot-keywords.edit', $k) }}" class="text-red-500 hover:underline">Sửa</a>
                                <form method="POST" action="{{ route('admin.chatbot-keywords.toggle', $k) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-slate-500 hover:underline cursor-pointer">{{ $k->is_active ? 'Tắt' : 'Bật' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.chatbot-keywords.destroy', $k) }}" onsubmit="return confirm('Xoá từ khoá này?')">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-500 hover:underline cursor-pointer">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Chưa có từ khoá. Mọi câu hỏi đang do Gemini trả lời.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
