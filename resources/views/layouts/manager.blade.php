<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản lý') · ZomZop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @php
        $_user   = auth()->user();
        $_branch = $_user->branch;
        // Icon SVG nét (viewBox 24, stroke)
        $_icons = [
            'home'   => '<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/>',
            'orders' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/>',
            'menu'   => '<path d="M4 11h16a8 8 0 0 1-16 0Z"/><path d="M12 4v3M8 5l1 2M16 5l-1 2"/>',
            'staff'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
            'shifts' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'check'  => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8 12 3 3 5-6"/>',
            'report' => '<path d="M4 19V5M4 19h16"/><path d="M8 16v-4M12 16V8M16 16v-6"/>',
            'money'  => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M7 9.5v5M17 9.5v5"/>',
            'star'   =>'<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9L12 3Z"/>',
        ];
        $_nav = [
            'Tổng quan' => [
                ['route' => 'manager.dashboard',    'match' => 'manager.dashboard', 'label' => 'Tổng quan', 'icon' => 'home'],
            ],
            'Bán hàng' => [
                ['route' => 'manager.orders.index', 'match' => 'manager.orders.*',  'label' => 'Đơn hàng',   'icon' => 'orders'],
                ['route' => 'manager.menu.index',   'match' => 'manager.menu.*',    'label' => 'Menu & giá', 'icon' => 'menu'],
            ],
            'Nhân sự' => [
                ['route' => 'manager.staff.index',  'match' => 'manager.staff.*',   'label' => 'Nhân viên',  'icon' => 'staff'],
                ['route' => 'manager.shifts.index', 'match' => 'manager.shifts.*',  'label' => 'Ca làm',     'icon' => 'shifts'],
                ['route' => 'manager.attendances.index', 'match' => 'manager.attendances.*', 'label' => 'Chấm công', 'icon' => 'check'],
                ['route' => 'manager.payrolls.index', 'match' => 'manager.payrolls.*', 'label' => 'Bảng lương', 'icon' => 'money'],
            ],
            'Báo cáo' => [
                ['route' => 'manager.reports.index', 'match' => 'manager.reports.*', 'label' => 'Doanh thu', 'icon' => 'report'],
                ['route' => 'manager.reviews.index', 'match' => 'manager.reviews.*', 'label' => 'Đánh giá',  'icon' => 'star'],
            ],
        ];
        $_svg = fn ($name, $class = 'w-[18px] h-[18px]') => '<svg class="' . $class . ' shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' . $_icons[$name] . '</svg>';
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar (ẩn trên điện thoại, thay bằng thanh ngang phía trên) --}}
        <aside class="hidden md:flex w-64 flex-col bg-white border-r border-slate-100 sticky top-0 h-screen">
            <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-2 px-5 h-16 shrink-0">
                <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop" class="h-9 w-auto">
                <span class="font-bold text-red-500 text-lg">ZomZop</span>
            </a>

            <div class="px-3 pb-2">
                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 focus-within:border-red-300 focus-within:ring-2 focus-within:ring-red-50">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" id="nav-search" placeholder="Tìm menu…" class="w-full text-sm outline-none bg-transparent">
                </label>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 pb-4 text-sm">
                @foreach ($_nav as $group => $items)
                    <div data-nav-group>
                        <p class="px-3 pt-4 pb-1.5 text-[11px] font-semibold tracking-wider uppercase text-slate-400">{{ $group }}</p>
                        @foreach ($items as $item)
                            @if (Route::has($item['route']))
                                @php $_active = request()->routeIs($item['match']); @endphp
                                <a href="{{ route($item['route']) }}" data-nav-item
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $_active ? 'bg-red-50 text-red-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    {!! $_svg($item['icon']) !!} <span>{{ $item['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between gap-4 px-4 md:px-6 sticky top-0 z-20">
                <div class="min-w-0 md:invisible">
                    <p class="text-[11px] text-slate-400">Chi nhánh</p>
                    <p class="font-semibold truncate">{{ $_branch?->name }}</p>
                </div>

                <details class="relative">
                    <summary class="flex items-center gap-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                        <div class="text-right leading-tight hidden sm:block">
                            <p class="text-sm font-semibold">{{ $_user->name }}</p>
                            <p class="text-xs text-slate-400">Quản lý · {{ $_branch?->name }}</p>
                        </div>
                        <span class="relative w-10 h-10 rounded-full bg-red-100 text-red-600 font-bold grid place-items-center">
                            {{ mb_strtoupper(mb_substr($_user->name, 0, 1)) }}
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-green-500 ring-2 ring-white"></span>
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
                           class="flex items-center gap-1.5 whitespace-nowrap px-3 py-1.5 rounded-full {{ request()->routeIs($item['match']) ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {!! $_svg($item['icon'], 'w-4 h-4') !!} {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <main class="flex-1 p-4 md:p-6">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
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

    <script>
        // Lọc mục sidebar theo ô tìm kiếm; ẩn cả nhóm nếu không còn mục nào khớp
        document.getElementById('nav-search')?.addEventListener('input', (e) => {
            const q = e.target.value.trim().toLowerCase();
            document.querySelectorAll('[data-nav-group]').forEach((group) => {
                let visible = 0;
                group.querySelectorAll('[data-nav-item]').forEach((item) => {
                    const match = item.textContent.toLowerCase().includes(q);
                    item.classList.toggle('hidden', !match);
                    visible += match;
                });
                group.classList.toggle('hidden', visible === 0);
            });
        });
    </script>
</body>
</html>
