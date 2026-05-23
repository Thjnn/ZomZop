<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZomZop - Danh Mục Pizza</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800">

    <header class="sticky top-0 z-50 bg-white shadow-xs border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-6 flex-shrink-0">
                <a href="/" class="flex items-center gap-2 hover:opacity-90 transition">
                    <img src="{{ asset('images/avatar-logo.png') }}" alt="ZomZop Avatar" class="h-9 w-auto object-contain">
                    <img src="{{ asset('images/text-logo.png') }}" alt="ZomZop Fast Food" class="h-7 w-auto object-contain">
                </a>
            </div>

            <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="/" class="hover:text-red-500 transition">Trang Chủ</a>
                <a href="#" class="text-red-500">Danh Mục</a>
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

    <main class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        <section class="relative h-48 rounded-2xl overflow-hidden shadow-sm bg-amber-900">
            <div class="absolute inset-0 grid grid-cols-5 gap-1 opacity-80">
                <div class="bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1513104890138-7c749659a591?w=400')"></div>
                <div class="bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400')"></div>
                <div class="bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=400')"></div>
                <div class="bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1628840042765-356cda07504e?w=400')"></div>
                <div class="bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?w=400')"></div>
            </div>
            <div class="absolute inset-0 bg-linear-to-r from-black/70 via-black/30 to-black/50 flex items-end p-6">
                <h1 class="text-white text-3xl font-bold tracking-wide">Bánh Pizza</h1>
            </div>
        </section>

        <div class="border-b border-slate-200">
            <div class="flex gap-6 text-sm font-medium">
                <a href="#" class="text-red-500 border-b-2 border-red-500 pb-2 px-1">Tất cả</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-2 px-1 transition">Pizza Ý Cay Nồng</a>
            </div>
        </div>

        <div class="flex justify-center">
            <div class="inline-flex bg-white border border-slate-200 rounded-lg p-1 gap-1 text-xs font-semibold shadow-xs">
                <button class="bg-red-500 text-white px-4 py-1.5 rounded-md cursor-pointer">Tất cả</button>
                <button class="text-slate-600 hover:bg-slate-50 px-4 py-1.5 rounded-md flex items-center gap-1 cursor-pointer">🍗 Món Mặn</button>
                <button class="text-slate-600 hover:bg-slate-50 px-4 py-1.5 rounded-md flex items-center gap-1 cursor-pointer">🌿 Món Chay</button>
            </div>
        </div>

        <section class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 pt-4">

            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between">
                <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=300" alt="Pizza" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 5%</span>
                    <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                    <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
                </div>
                <div class="p-3 space-y-2">
                    <h4 class="font-bold text-sm text-slate-700 line-clamp-2">Pizza Phô Mai Mozzarella Thượng Hạng</h4>
                    <div class="flex items-center gap-1 text-xs text-amber-500 font-semibold">
                        <span>3.0 ★</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                        <span class="bg-green-100 text-green-700 text-[8px] font-bold px-1 rounded-sm">HALAL</span>
                    </div>
                    <div class="flex items-baseline gap-2 pt-1">
                        <span class="text-[11px] text-slate-400 line-through">150.000 đ</span>
                        <span class="text-sm font-bold text-red-500">142.500 đ</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden group flex flex-col justify-between">
                <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=300" alt="Pizza" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    <span class="absolute top-2 left-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">GIẢM 10%</span>
                    <button class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center text-xs shadow-xs text-slate-400 hover:text-red-500 transition cursor-pointer">❤️</button>
                    <button class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white text-red-500 text-xs font-bold px-4 py-1 rounded-full shadow-md flex items-center gap-1 hover:bg-red-500 hover:text-white transition cursor-pointer">➕ Thêm</button>
                </div>
                <div class="p-3 space-y-2">
                    <h4 class="font-bold text-sm text-slate-700 line-clamp-2">Pizza Ý Vị Cay Nồng Đặc Biệt</h4>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 border border-green-600 flex items-center justify-center text-[6px] text-green-600 font-bold">●</span>
                    </div>
                    <div class="flex items-baseline gap-2 pt-1">
                        <span class="text-[11px] text-slate-400 line-through">190.000 đ</span>
                        <span class="text-sm font-bold text-red-500">171.000 đ</span>
                    </div>
                </div>
            </div>

        </section>
    </main>

    <div class="fixed bottom-6 right-6 z-50">
        <button class="bg-green-500 text-white px-4 py-2.5 rounded-full font-semibold shadow-lg flex items-center gap-2 hover:bg-green-600 transition cursor-pointer text-sm">
            <span>💬</span> Chat Ngay
        </button>
    </div>

</body>

</html>