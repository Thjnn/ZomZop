<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản trị') · ZomZop Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @php
        $_user  = auth()->user();
        $_icons = [
            'home'   => '<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/>',
            'branch' => '<path d="M3 21h18M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/>',
            'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'staff'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
            'orders' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/>',
            'report' => '<path d="M4 19V5M4 19h16"/><path d="M8 16v-4M12 16V8M16 16v-6"/>',
            'money'  => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
            'check'  => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8 12 3 3 5-6"/>',
            'image'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 16-5-5-9 8"/>',
            'ticket' => '<path d="M3 8a2 2 0 0 0 0 4v4h18v-4a2 2 0 0 1 0-4V4H3z"/><path d="M13 4v16"/>',
            'tag'    => '<path d="M3 12V3h9l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
            'menu'   => '<path d="M4 11h16a8 8 0 0 1-16 0Z"/><path d="M12 4v3M8 5l1 2M16 5l-1 2"/>',
            'gear'   => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1 7 17M17 7l2.1-2.1"/>',
            'list'   => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        ];
        // Mục nào chưa có route (phase chưa làm) sẽ tự ẩn nhờ Route::has
        $_nav = [
            'Tổng quan' => [
                ['route' => 'admin.dashboard',         'match' => 'admin.dashboard',     'label' => 'Tổng quan',          'icon' => 'home'],
            ],
            'Chuỗi' => [
                ['route' => 'admin.branches.index',    'match' => 'admin.branches.*',    'label' => 'Chi nhánh',          'icon' => 'branch'],
                ['route' => 'admin.managers.index',    'match' => 'admin.managers.*',    'label' => 'Tài khoản quản lý',  'icon' => 'user'],
                ['route' => 'admin.staff.index',       'match' => 'admin.staff.*',       'label' => 'Nhân viên',          'icon' => 'staff'],
                ['route' => 'admin.customers.index',   'match' => 'admin.customers.*',   'label' => 'Khách hàng',         'icon' => 'user'],
            ],
            'Bán hàng' => [
                ['route' => 'admin.orders.index',      'match' => 'admin.orders.*',      'label' => 'Đơn hàng',           'icon' => 'orders'],
                ['route' => 'admin.reports.index',     'match' => 'admin.reports.*',     'label' => 'Báo cáo',            'icon' => 'report'],
            ],
            'Nhân sự' => [
                ['route' => 'admin.attendances.index', 'match' => 'admin.attendances.*', 'label' => 'Chấm công',          'icon' => 'check'],
                ['route' => 'admin.payrolls.index',    'match' => 'admin.payrolls.*',    'label' => 'Bảng lương',         'icon' => 'money'],
            ],
            'Marketing' => [
                ['route' => 'admin.banners.index',     'match' => 'admin.banners.*',     'label' => 'Banner',             'icon' => 'image'],
                ['route' => 'admin.coupons.index',     'match' => 'admin.coupons.*',     'label' => 'Mã giảm giá',        'icon' => 'ticket'],
            ],
            'Thực đơn' => [
                ['route' => 'admin.categories.index',  'match' => 'admin.categories.*',  'label' => 'Danh mục',           'icon' => 'tag'],
                ['route' => 'admin.menu-items.index',  'match' => 'admin.menu-items.*',  'label' => 'Món ăn',             'icon' => 'menu'],
            ],
            'Hệ thống' => [
                ['route' => 'admin.settings.edit',     'match' => 'admin.settings.*',    'label' => 'Cài đặt chung',      'icon' => 'gear'],
                ['route' => 'admin.logs.index',        'match' => 'admin.logs.*',        'label' => 'Nhật ký thao tác',   'icon' => 'list'],
            ],
        ];
        $_svg = fn ($name, $class = 'w-[18px] h-[18px]') => '<svg class="' . $class . ' shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' . $_icons[$name] . '</svg>';
    @endphp

    <div class="flex min-h-screen">
        <aside class="hidden md:flex w-64 flex-col bg-white border-r border-slate-100 sticky top-0 h-screen">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-5 h-16 shrink-0">
                <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop" class="h-9 w-auto">
                <span class="font-bold text-red-500 text-lg">ZomZop <span class="text-xs text-slate-400 font-semibold">ADMIN</span></span>
            </a>
            <nav class="flex-1 overflow-y-auto px-3 pb-4 text-sm">
                @foreach ($_nav as $group => $items)
                    @php $items = array_filter($items, fn ($i) => Route::has($i['route'])); @endphp
                    @if ($items)
                        <p class="px-3 pt-4 pb-1.5 text-[11px] font-semibold tracking-wider uppercase text-slate-400">{{ $group }}</p>
                        @foreach ($items as $item)
                            @php $_active = request()->routeIs($item['match']); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $_active ? 'bg-red-50 text-red-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                {!! $_svg($item['icon']) !!} <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between gap-4 px-4 md:px-6 sticky top-0 z-20">
                <p class="font-semibold">Quản trị toàn chuỗi</p>
                <details class="relative">
                    <summary class="flex items-center gap-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                        <div class="text-right leading-tight hidden sm:block">
                            <p class="text-sm font-semibold">{{ $_user->name }}</p>
                            <p class="text-xs text-slate-400">Admin</p>
                        </div>
                        <span class="w-10 h-10 rounded-full bg-slate-800 text-white font-bold grid place-items-center">
                            {{ mb_strtoupper(mb_substr($_user->name, 0, 1)) }}
                        </span>
                    </summary>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-slate-100 shadow-lg py-1 text-sm">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="w-full text-left px-4 py-2 hover:bg-slate-50 text-red-600 cursor-pointer">Đăng xuất</button>
                        </form>
                    </div>
                </details>
            </header>

            {{-- Menu ngang cho điện thoại --}}
            <nav class="md:hidden flex gap-2 overflow-x-auto px-4 py-2 bg-white border-b border-slate-100 text-sm">
                @foreach (collect($_nav)->flatten(1) as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="whitespace-nowrap px-3 py-1.5 rounded-full {{ request()->routeIs($item['match']) ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <main class="flex-1 p-4 md:p-6">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-amber-50 text-amber-700 text-sm">{{ session('warning') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm">
                        @foreach ($errors->all() as $e) <p>• {{ $e }}</p> @endforeach
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
