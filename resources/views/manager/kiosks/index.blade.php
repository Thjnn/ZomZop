@extends('layouts.manager')

@section('title', 'Thiết bị quầy')

@section('content')
    <h1 class="text-xl font-bold mb-1">Thiết bị chấm công khuôn mặt</h1>
    <p class="text-sm text-slate-500 mb-4">Laptop/PC có webcam đặt ở quầy. Nhân viên đứng trước camera để chấm vào/ra.</p>

    @if (session('kiosk_link'))
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 text-sm">
            <p class="font-semibold">Link ghép thiết bị — chỉ hiện một lần, hãy mở ngay trên máy quầy:</p>
            <div class="flex gap-2 mt-2">
                <input id="kiosk-link" readonly value="{{ session('kiosk_link') }}" onfocus="this.select()" class="flex-1 px-3 py-2 rounded-lg border border-amber-200 bg-white font-mono text-xs">
                <button type="button" id="kiosk-copy" class="px-3 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Chép</button>
            </div>
            <p id="kiosk-copy-msg" class="text-xs text-slate-500 mt-2">Camera chỉ chạy trên HTTPS hoặc localhost. Mất link thì thu hồi và tạo thiết bị mới.</p>
        </div>
        <script>
            // Trang http thường (VD http://zomzop.test) bị trình duyệt chặn navigator.clipboard → dùng execCommand dự phòng;
            // vẫn không được thì báo rõ, tránh dán nhầm link cũ còn trong bộ nhớ tạm
            document.getElementById('kiosk-copy').addEventListener('click', async (e) => {
                const input = document.getElementById('kiosk-link');
                const msg = document.getElementById('kiosk-copy-msg');
                let ok = false;
                try {
                    await navigator.clipboard.writeText(input.value);
                    ok = true;
                } catch {
                    input.focus();
                    input.select();
                    try { ok = document.execCommand('copy'); } catch {}
                }
                e.target.textContent = ok ? 'Đã chép' : 'Chép tay';
                if (!ok) {
                    msg.textContent = 'Trình duyệt không cho chép tự động — link đã được bôi đen, hãy bấm Ctrl+C.';
                    msg.className = 'text-xs text-red-600 font-semibold mt-2';
                }
            });
        </script>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                    <tr><th class="px-4 py-3">Tên</th><th class="px-4 py-3">Tạo lúc</th><th class="px-4 py-3">Dùng lần cuối</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody>
                    @forelse ($devices as $d)
                        <tr class="border-b border-slate-50 {{ $d->revoked_at ? 'text-slate-400' : '' }}">
                            <td class="px-4 py-3 font-semibold">{{ $d->name }}</td>
                            <td class="px-4 py-3">{{ $d->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $d->last_used_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($d->revoked_at)
                                    Đã thu hồi {{ $d->revoked_at->format('d/m') }}
                                @else
                                    <form method="POST" action="{{ route('manager.kiosks.revoke', $d) }}">
                                        @csrf @method('PATCH')
                                        <button class="text-red-500 hover:underline cursor-pointer">Thu hồi</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Chưa có thiết bị nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('manager.kiosks.store') }}" class="bg-white rounded-2xl p-5 border border-slate-100 space-y-3 text-sm h-fit">
            @csrf
            <h2 class="font-semibold">Thêm thiết bị</h2>
            <input name="name" value="{{ old('name') }}" placeholder="VD: Laptop quầy thu ngân" maxlength="100" required class="w-full px-3 py-2 rounded-lg border border-slate-200">
            @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Tạo link ghép</button>
        </form>
    </div>
@endsection
