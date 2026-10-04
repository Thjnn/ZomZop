<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản lý') · ZomZop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @php
        $_branch = auth()->user()->branch;
        $_nav = [
            ['route' => 'manager.dashboard',    'match' => 'manager.dashboard', 'label' => 'Tổng quan', 'icon' => '📊'],
            ['route' => 'manager.orders.index', 'match' => 'manager.orders.*',  'label' => 'Đơn hàng',  'icon' => '🧾'],
            ['route' => 'manager.menu.index',   'match' => 'manager.menu.*',    'label' => 'Menu & giá', 'icon' => '🍔'],
            ['route' => 'manager.staff.index',  'match' => 'manager.staff.*',   'label' => 'Nhân viên',  'icon' => '👥'],
            ['route' => 'manager.shifts.index', 'match' => 'manager.shifts.*',  'label' => 'Ca làm',     'icon' => '🕒'],
            ['route' => 'manager.attendances.index', 'match' => 'manager.attendances.*', 'label' => 'Chấm công', 'icon' => '✅'],
        ];
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar (ẩn trên điện thoại, thay bằng thanh ngang phía trên) --}}
        <aside class="hidden md:flex w-60 flex-col bg-white border-r border-slate-100">
            <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-2 px-5 h-16 border-b border-slate-100">
                <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop" class="h-9 w-auto">
                <span class="font-bold text-red-500">Quản lý</span>
            </a>
            <nav class="flex-1 p-3 space-y-1 text-sm font-medium">
                @foreach ($_nav as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs($item['match']) ? 'bg-red-50 text-red-600' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 md:px-6">
                <div class="min-w-0">
                    <p class="text-[11px] text-slate-400">Chi nhánh</p>
                    <p class="font-semibold truncate">{{ $_branch?->name }}</p>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="hidden sm:inline text-slate-500">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 cursor-pointer">Đăng xuất</button>
                    </form>
                </div>
            </header>

            {{-- Menu ngang cho điện thoại --}}
            <nav class="md:hidden flex gap-2 overflow-x-auto px-4 py-2 bg-white border-b border-slate-100 text-sm">
                @foreach ($_nav as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="whitespace-nowrap px-3 py-1.5 rounded-full {{ request()->routeIs($item['match']) ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $item['icon'] }} {{ $item['label'] }}
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
</body>
</html>
