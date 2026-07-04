{{-- resources/views/components/menu-item-card.blade.php --}}
{{-- Dùng: <x-menu-item-card :item="$item" /> --}}

@php
$image = $item->images->first();
$imgSrc = $image ? $image->image_url : $item->image_url;
@endphp

<article data-item-id="{{ $item->id }}" class="group rounded-[1.7rem] bg-white shadow-[0_16px_35px_rgba(15,23,42,0.08)] ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(253,164,175,0.22)]">
    <div class="relative px-4 pt-4">
        <div class="relative h-40 overflow-hidden rounded-[1.5rem] bg-[#f6e6d6] cursor-pointer">
            <img src="{{ $imgSrc }}" alt="{{ $item->name }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">

            @if ($item->is_on_sale)
            <span class="absolute left-3 top-3 rounded-full bg-[#ff6a61] px-3 py-1 text-[10px] font-bold text-white">
                GIẢM {{ $item->discount_percent }}%
            </span>
            @endif

            <button class="btn-favorite absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500 cursor-pointer" aria-label="Yêu thích">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                </svg>
            </button>
        </div>

        <button
            data-id="{{ $item->id }}"
            data-name="{{ $item->name }}"
            data-price="{{ $item->discounted_price }}"
            class="btn-add-cart absolute -bottom-4 left-1/2 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5 cursor-pointer">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
            Thêm
        </button>
    </div>

    <div class="px-4 pb-5 pt-7 text-center">
        <h4 class="line-clamp-2 text-sm font-semibold text-slate-800 hover:text-red-500 transition cursor-pointer">{{ $item->name }}</h4>

        @if ($item->reviews_avg_rating ?? false)
        <div class="mt-1 flex items-center justify-center gap-1 text-sm text-slate-700">
            <span>{{ number_format($item->reviews_avg_rating, 1) }}</span>
            <span class="text-amber-400">★</span>
        </div>
        @endif

        <div class="mt-2 flex items-center justify-center gap-2 text-sm">
            @if ($item->is_on_sale)
            <span class="text-slate-400 line-through">{{ $item->display_base_price }}</span>
            <span class="font-semibold text-slate-800">{{ $item->display_price }}</span>
            @else
            <span class="font-semibold text-slate-800">{{ $item->display_price }}</span>
            @endif
        </div>
    </div>
</article>