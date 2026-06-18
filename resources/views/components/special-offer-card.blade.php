{{-- resources/views/components/special-offer-card.blade.php --}}
{{-- Dùng: <x-special-offer-card :item="$item" /> --}}

@php
    $image = $item->images->first();
    $imgSrc = $image ? $image->image_url : $item->image_url;
@endphp

<article class="swiper-slide rounded-[1.7rem] bg-white shadow-[0_20px_45px_rgba(253,164,175,0.18)]">
    <div class="relative px-6 pt-6">
        <div class="relative h-72 overflow-hidden rounded-[1.7rem] bg-[#f6e6d6]">
            <img src="{{ $imgSrc }}" alt="{{ $item->name }}"
                 class="h-full w-full object-cover transition duration-300 hover:scale-105">

            <span class="absolute left-4 top-4 rounded-full bg-[#ff6a61] px-4 py-2 text-sm font-bold text-white">
                Giảm {{ $item->discount_percent }}%
            </span>

            <button class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:text-red-500 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                </svg>
            </button>
        </div>

        <button
            data-id="{{ $item->id }}"
            data-name="{{ $item->name }}"
            data-price="{{ $item->discounted_price }}"
            class="btn-add-cart absolute -bottom-5 left-12 z-10 inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-[1.05rem] font-medium text-[#ff5a55] shadow-lg transition hover:-translate-y-0.5 cursor-pointer">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff5a55] text-white">+</span>
            Thêm
        </button>
    </div>

    <div class="px-6 pb-8 pt-10 text-center">
        <h3 class="text-[1.05rem] font-medium tracking-[0.01em] text-slate-800 hover:text-red-500 transition cursor-pointer">
            {{ $item->name }}
        </h3>

        @if ($item->reviews_avg_rating ?? false)
        <div class="mt-2 flex items-center justify-center gap-1 text-sm text-slate-700">
            <span>{{ number_format($item->reviews_avg_rating, 1) }}</span>
            <span class="text-amber-400">★</span>
        </div>
        @endif

        <div class="mt-2 flex items-center justify-center gap-2 text-[0.95rem]">
            <span class="text-slate-400 line-through">{{ $item->display_base_price }}</span>
            <span class="font-semibold text-slate-800">{{ $item->display_price }}</span>
        </div>
    </div>
</article>
