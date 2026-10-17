<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nhân viên') · ZomZop</title>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @php
        $_user = auth()->user();
        $_nav  = [['staff.dashboard', 'Của tôi']];
        if ($_user->isKitchen()) {
            array_unshift($_nav, ['kitchen.dashboard', 'Màn hình bếp']);
        }
    @endphp

    <header class="bg-white border-b border-slate-100 sticky top-0 z-20">
        <div class="max-w-6xl mx-auto h-16 px-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop" class="h-9 w-auto object-contain">
                <nav class="flex gap-1 text-sm">
                    @foreach ($_nav as [$route, $label])
                        <a href="{{ route($route) }}"
                           class="px-3 py-1.5 rounded-full font-medium {{ request()->routeIs($route) ? 'bg-red-500 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right leading-tight hidden sm:block">
                    <p class="text-sm font-semibold">{{ $_user->name }}</p>
                    <p class="text-xs text-slate-400">{{ \App\Http\Controllers\Manager\StaffController::ROLES[$_user->role] ?? '' }} · {{ $_user->branch?->name }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="text-sm text-red-600 hover:underline cursor-pointer">Đăng xuất</button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto p-4 md:p-6">
        @yield('content')
    </main>
</body>
</html>
