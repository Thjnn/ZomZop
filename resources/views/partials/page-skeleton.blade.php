{{-- Khung xương hiện trong lúc trang đang tải (ảnh, font...). Ẩn bằng script ở cuối layouts/app. --}}
@php
    $variant = request()->routeIs('home') ? 'home'
        : (request()->routeIs('menu.all', 'bestsellers', 'category.show', 'favorites.index', 'search') ? 'menu' : 'page');
@endphp

<div id="page-skeleton" aria-hidden="true" class="absolute inset-0 z-30 overflow-hidden bg-slate-50 px-4 py-6">
    <div class="max-w-7xl mx-auto space-y-10">

        @if ($variant === 'home')
            {{-- Banner + danh mục --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="sk lg:col-span-2 rounded-2xl h-[220px] sm:h-[280px] lg:h-[320px]"></div>
                <div class="bg-slate-100/60 rounded-2xl p-6 border border-slate-200/50">
                    <div class="sk h-4 w-36 mx-auto mb-5 rounded-full"></div>
                    <div class="grid grid-cols-3 gap-3">
                        @for ($i = 0; $i < 9; $i++)
                            <div class="bg-white p-3 rounded-xl border border-slate-100 flex flex-col items-center gap-2">
                                <div class="sk h-9 w-9 rounded-lg"></div>
                                <div class="sk h-2.5 w-12 rounded-full"></div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        @elseif ($variant === 'menu')
            {{-- Tiêu đề màu + tab danh mục + nút sắp xếp --}}
            <div class="space-y-6">
                <div class="rounded-2xl bg-linear-to-r from-red-600/25 to-orange-500/25 p-6">
                    <div class="sk sk-light h-8 w-48 rounded-lg"></div>
                    <div class="sk sk-light h-3 w-64 max-w-full rounded-full mt-3"></div>
                </div>
                <div class="flex gap-6 border-b border-slate-200 pb-2 overflow-hidden">
                    @for ($i = 0; $i < 7; $i++)<div class="sk h-3.5 w-16 shrink-0 rounded-full"></div>@endfor
                </div>
                <div class="flex gap-2">
                    @for ($i = 0; $i < 4; $i++)<div class="sk h-8 w-24 rounded-full"></div>@endfor
                </div>
            </div>
        @else
            {{-- Trang thường: tiêu đề + khối nội dung --}}
            <div class="max-w-3xl mx-auto space-y-5">
                <div class="sk h-7 w-56 rounded-lg"></div>
                @for ($b = 0; $b < 2; $b++)
                    <div class="bg-white rounded-2xl border border-slate-100 p-5 space-y-3">
                        <div class="sk h-5 w-40 rounded-full"></div>
                        <div class="sk h-3 w-full rounded-full"></div>
                        <div class="sk h-3 w-11/12 rounded-full"></div>
                        <div class="sk h-3 w-2/3 rounded-full"></div>
                    </div>
                @endfor
            </div>
        @endif

        @if ($variant !== 'page')
            {{-- Lưới thẻ món, cùng khung với components/menu-item-card --}}
            <div class="space-y-4">
                @if ($variant === 'home')
                    <div class="flex justify-between items-end">
                        <div class="sk h-5 w-56 rounded-full"></div>
                        <div class="sk h-3 w-16 rounded-full"></div>
                    </div>
                @endif
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @for ($i = 0; $i < 10; $i++)
                        <div class="rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 {{ $i >= 4 ? 'hidden sm:block' : '' }}">
                            <div class="px-4 pt-4"><div class="sk h-40 rounded-[1.5rem]"></div></div>
                            <div class="px-4 pb-5 pt-6 flex flex-col items-center gap-2">
                                <div class="sk h-3.5 w-3/4 rounded-full"></div>
                                <div class="sk h-3.5 w-1/3 rounded-full"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @endif
    </div>
</div>
<noscript><style>#page-skeleton{display:none}</style></noscript>
