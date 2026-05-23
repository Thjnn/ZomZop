<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ZomZop - Fast Food')</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800">

    <header class="sticky top-0 z-50 bg-white shadow-xs border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-6 flex-shrink-0">
                <a href="/" class="flex items-center gap-2 hover:opacity-90 transition">
                    <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop Avatar" class="h-10 w-auto object-contain">
                    <img src="{{ asset('images/text-logo.png') }}" alt="ZomZop Fast Food" class="h-8 w-auto object-contain">
                </a>
            </div>

            <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="/" class="{{ Request::is('/') ? 'text-red-500' : 'hover:text-red-500 transition' }}">Trang Chủ</a>
                <a href="/category/pizza" class="{{ Request::is('category/pizza') ? 'text-red-500' : 'hover:text-red-500 transition' }}">Danh Mục</a>
            </nav>

            <div class="flex-1 max-w-md mx-4">
                <div class="relative">
                    <input type="text" placeholder="Bạn đang đói bụng cồn cào?" class="w-full bg-slate-100 pl-10 pr-4 py-2 rounded-full text-sm border border-transparent focus:bg-white focus:border-red-400 focus:outline-hidden transition">
                    <span class="absolute left-3.5 top-2.5 text-slate-400 text-sm">🔍</span>
                </div>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                <div class="hidden sm:block text-right">
                    <p class="text-[10px] text-slate-400">Chi nhánh</p>
                    <select class="text-xs font-semibold text-slate-700 bg-transparent focus:outline-hidden cursor-pointer">
                        <option>Chi nhánh chính</option>
                    </select>
                </div>
                <button class="relative p-2 hover:bg-slate-100 rounded-full transition cursor-pointer">
                    <span class="text-xl">❤️</span>
                    <span class="absolute top-1 right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">0</span>
                </button>
                <button class="relative p-2 hover:bg-slate-100 rounded-full transition cursor-pointer">
                    <span class="text-xl">🛍️</span>
                    <span class="absolute top-1 right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">0</span>
                </button>
                <button class="p-2 hover:bg-slate-100 rounded-full transition cursor-pointer hidden sm:block">👤</button>
                <button class="p-2 hover:bg-slate-100 rounded-full transition cursor-pointer">☰</button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 space-y-10">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-100 mt-16 text-slate-600 text-sm">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-4">
                    <a href="/" class="flex items-center gap-2 hover:opacity-90 transition">
                        <img src="{{ asset('images/Logo_1_ZomZop.png') }}" alt="ZomZop Avatar" class="h-9 w-auto object-contain">
                        <img src="{{ asset('images/Logo_2_ZomZop.png') }}" alt="ZomZop Fast Food" class="h-7 w-auto object-contain">
                    </a>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        ZomZop - Chuỗi hệ thống thức ăn nhanh chất lượng cao, mang đến những bữa ăn ngon miệng, nhanh chóng và tràn đầy năng lượng cho ngày dài của bạn.
                    </p>
                    <div class="flex items-center gap-3 text-lg select-none">
                        <a href="#" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition">🌐</a>
                        <a href="#" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition">💬</a>
                        <a href="#" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition">📮</a>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm tracking-wide uppercase">Thực Đơn</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#" class="hover:text-red-500 transition">Combo Ưu Đãi</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Gà Rán Giòn Rụm</a></li>
                        <li><a href="/category/pizza" class="hover:text-red-500 transition">Pizza Ý Thượng Hạng</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Tráng Miệng & Đồ Uống</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm tracking-wide uppercase">Chính Sách & Hỗ Trợ</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#" class="hover:text-red-500 transition">Hình thức thanh toán</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Chính sách giao hàng</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Chính sách bảo mật thông tin</a></li>
                        <li><a href="#" class="hover:text-red-500 transition">Phản hồi & Góp ý</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm tracking-wide uppercase">Thông Tin Liên Hệ</h4>
                    <ul class="space-y-2 text-xs text-slate-500">
                        <li class="flex items-center gap-1.5"><span>📞</span> <strong class="text-slate-700">Hotline:</strong> 1900 xxxx</li>
                        <li class="flex items-center gap-1.5"><span>✉️</span> <strong class="text-slate-700">Email:</strong> contact@zomzop.com</li>
                        <li class="flex items-center gap-1.5"><span>⏰</span> <strong class="text-slate-700">Giờ mở cửa:</strong> 09:00 - 22:00</li>
                        <li class="flex items-start gap-1.5"><span class="mt-0.5">📍</span> <span><strong class="text-slate-700">Trụ sở:</strong> Toà nhà ZomZop, Quận 1, TP. Hồ Chí Minh</span></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-100 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>© 2026 ZomZop Fast Food. Tất cả các quyền được bảo lưu.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:underline">Điều khoản sử dụng</a>
                    <a href="#" class="hover:underline">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>