<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chấm công · ZomZop</title>
    @vite(['resources/css/app.css', 'resources/js/kiosk.js'])
</head>
<body class="bg-slate-900 text-white min-h-screen select-none">
    <div id="kiosk" class="max-w-5xl mx-auto px-4 py-6 grid lg:grid-cols-5 gap-6 items-start">
        <div class="lg:col-span-3">
            <div class="relative rounded-3xl overflow-hidden bg-black aspect-[4/3]">
                <video id="kiosk-video" class="w-full h-full object-cover -scale-x-100 border-8 border-transparent rounded-3xl transition-colors" muted playsinline></video>
                <p id="kiosk-hint" class="absolute bottom-4 inset-x-4 text-center text-xl font-semibold bg-black/60 rounded-2xl py-3">Đang khởi động…</p>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop" class="h-10">
                <div>
                    <p class="font-bold text-red-400 text-lg">Chấm công</p>
                    <p id="kiosk-branch" class="text-sm text-slate-400">—</p>
                </div>
            </div>
            <p id="kiosk-clock" class="text-6xl font-bold tabular-nums">--:--</p>
            <p id="kiosk-date" class="text-slate-400"></p>

            <div id="kiosk-result" class="hidden rounded-3xl p-6 text-center space-y-2">
                <p id="kiosk-result-title" class="text-2xl font-bold"></p>
                <p id="kiosk-result-body" class="text-lg"></p>
                <div id="kiosk-confirm" class="hidden flex gap-3 justify-center pt-2">
                    <button id="kiosk-confirm-yes" class="px-6 py-3 rounded-2xl bg-red-500 text-lg font-semibold cursor-pointer">Chấm ra</button>
                    <button id="kiosk-confirm-no" class="px-6 py-3 rounded-2xl bg-slate-600 text-lg cursor-pointer">Huỷ</button>
                </div>
            </div>

            <p class="text-sm text-slate-500">Không nhận ra hoặc không có ca? Báo quản lý để chấm công tay.</p>
        </div>
    </div>
</body>
</html>
