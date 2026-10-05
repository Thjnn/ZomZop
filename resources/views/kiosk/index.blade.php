<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Chấm công · ZomZop</title>
    @vite(['resources/css/app.css', 'resources/js/kiosk.js'])
</head>
{{-- Màn hình máy chấm công (máy tính bảng / laptop có webcam). Nút to cho màn hình cảm ứng. --}}
<body class="bg-slate-900 text-white min-h-dvh select-none overscroll-none">
    <div id="kiosk" class="min-h-dvh max-w-6xl mx-auto px-4 sm:px-6 py-4 flex flex-col gap-4">

        {{-- Đầu trang: logo, chi nhánh, đồng hồ --}}
        <header class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop" class="h-10 sm:h-12 shrink-0">
                <div class="min-w-0">
                    <p class="font-bold text-red-400 text-lg leading-tight">Chấm công</p>
                    <p id="kiosk-branch" class="text-sm text-slate-400 truncate">—</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p id="kiosk-clock" class="text-3xl sm:text-4xl font-bold tabular-nums leading-none">--:--</p>
                    <p id="kiosk-date" class="text-xs sm:text-sm text-slate-400"></p>
                </div>
                <button id="kiosk-fullscreen" type="button" title="Toàn màn hình"
                        class="w-12 h-12 rounded-2xl bg-slate-800 hover:bg-slate-700 grid place-items-center cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
                </button>
            </div>
        </header>

        {{-- Thông báo trạng thái chung (chưa ghép, mất kết nối…) --}}
        <p id="kiosk-status" class="hidden rounded-2xl bg-red-700/80 px-4 py-3 text-center text-lg font-semibold"></p>

        <main class="flex-1 grid place-items-center">

            {{-- 1. Màn chờ: 3 nút lớn --}}
            <section data-screen="idle" class="w-full">
                <p class="text-center text-slate-300 text-xl sm:text-2xl mb-6">Chọn thao tác rồi nhìn vào camera</p>
                <div class="grid sm:grid-cols-3 gap-4 sm:gap-6">
                    <button data-action="in" class="kiosk-btn bg-emerald-600 hover:bg-emerald-500">
                        <svg class="w-14 h-14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                        <span>Chấm vào</span>
                    </button>
                    <button data-action="out" class="kiosk-btn bg-sky-600 hover:bg-sky-500">
                        <svg class="w-14 h-14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        <span>Chấm ra</span>
                    </button>
                    <button data-action="early" class="kiosk-btn bg-amber-600 hover:bg-amber-500">
                        <svg class="w-14 h-14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l-3 2"/></svg>
                        <span>Ra ca sớm</span>
                        <small class="text-sm font-normal opacity-80">có lý do</small>
                    </button>
                </div>
            </section>

            {{-- 2. Chọn lý do đi trễ / ra sớm --}}
            <section data-screen="reason" class="hidden w-full max-w-3xl">
                <p id="reason-title" class="text-center text-2xl sm:text-3xl font-bold"></p>
                <p id="reason-sub" class="text-center text-slate-300 text-lg mt-1 mb-6"></p>
                <div id="reason-options" class="grid grid-cols-2 gap-3 sm:gap-4"></div>
                <div id="reason-other" class="hidden mt-4 flex gap-3">
                    <input id="reason-text" maxlength="255" placeholder="Nhập lý do…"
                           class="flex-1 min-w-0 px-5 h-16 rounded-2xl bg-white text-slate-900 text-xl outline-none">
                    <button id="reason-send" class="px-6 h-16 rounded-2xl bg-red-500 hover:bg-red-600 text-xl font-semibold cursor-pointer">Gửi</button>
                </div>
                <button data-cancel class="mt-6 mx-auto block px-8 h-14 rounded-2xl bg-slate-700 hover:bg-slate-600 text-lg cursor-pointer">Huỷ</button>
            </section>

            {{-- 3. Quét khuôn mặt --}}
            <section data-screen="scan" class="hidden w-full max-w-3xl">
                <p id="scan-title" class="text-center text-2xl font-bold mb-3"></p>
                <div class="relative rounded-3xl overflow-hidden bg-black aspect-[4/3] max-h-[60dvh] mx-auto">
                    <video id="kiosk-video" class="w-full h-full object-cover -scale-x-100 border-8 border-transparent rounded-3xl transition-colors" muted playsinline></video>
                    <p id="kiosk-hint" class="absolute bottom-4 inset-x-4 text-center text-xl sm:text-2xl font-semibold bg-black/60 rounded-2xl py-3">Nhìn vào camera</p>
                </div>
                <button data-cancel class="mt-4 mx-auto block px-8 h-14 rounded-2xl bg-slate-700 hover:bg-slate-600 text-lg cursor-pointer">Huỷ</button>
            </section>

            {{-- 4. Kết quả --}}
            <section data-screen="result" class="hidden w-full max-w-2xl">
                <div id="result-card" class="rounded-3xl p-8 sm:p-10 text-center space-y-3">
                    <p id="result-title" class="text-3xl sm:text-4xl font-bold"></p>
                    <p id="result-body" class="text-xl sm:text-2xl"></p>
                    <p id="result-extra" class="text-lg opacity-90"></p>
                </div>
            </section>
        </main>

        <footer class="text-center text-sm text-slate-500">Không nhận ra hoặc không có ca? Báo quản lý để chấm công tay.</footer>
    </div>

    <style>
        .kiosk-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .5rem;
            min-height: 11rem; border-radius: 1.75rem; font-size: 1.75rem; font-weight: 700; cursor: pointer;
            box-shadow: 0 10px 30px -10px rgb(0 0 0 / .5); transition: transform .1s; touch-action: manipulation; }
        .kiosk-btn:active { transform: scale(.97); }
        .reason-chip { min-height: 4.5rem; border-radius: 1.25rem; background: rgb(51 65 85); font-size: 1.35rem;
            font-weight: 600; cursor: pointer; touch-action: manipulation; }
        .reason-chip:active { background: rgb(71 85 105); }
    </style>
</body>
</html>
