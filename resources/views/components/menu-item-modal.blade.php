{{-- resources/views/components/menu-item-modal.blade.php --}}

<div id="item-modal" data-item-id="" class="fixed inset-0 z-[100] flex items-center justify-center p-4 hidden">

    {{-- Overlay --}}
    <div id="modal-overlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"></div>

    {{-- Modal box --}}
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">

        {{-- Nút đóng --}}
        <button id="modal-close-btn"
            class="absolute top-4 right-4 z-20 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-red-50 hover:text-red-500 transition cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Ảnh --}}
        <div class="relative h-56 bg-[#f6e6d6] overflow-hidden">
            <img id="modal-image" src="" alt="" class="w-full h-full object-cover transition duration-300">
            <span id="modal-badge" class="absolute left-4 top-4 hidden rounded-full bg-[#ff6a61] px-4 py-1.5 text-sm font-bold text-white"></span>
        </div>

        {{-- Nội dung --}}
        <div class="p-6">

            <div class="flex items-start justify-between gap-3 mb-1">
                <h2 id="modal-name" class="text-xl font-bold text-slate-800 leading-tight"></h2>
                <button class="btn-favorite flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-300 transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4C8.24 4 9.91 4.81 11 6.09 12.09 4.81 13.76 4 15.5 4A4.5 4.5 0 0 1 20 8.5c0 3.78-3.4 6.86-8.55 11.18Z" />
                    </svg>
                </button>
            </div>

            <p id="modal-category" class="text-xs text-slate-400 mb-3"></p>
            <p id="modal-description" class="text-sm text-slate-500 leading-relaxed mb-4"></p>

            <div class="border-t border-slate-100 mb-4"></div>

            <div class="flex items-center justify-between gap-4">

                {{-- Giá --}}
                <div>
                    <div id="modal-price-sale" class="hidden items-baseline gap-2">
                        <span id="modal-base-price" class="text-sm text-slate-400 line-through"></span>
                        <span id="modal-discounted-price" class="text-xl font-bold text-red-500"></span>
                    </div>
                    <div id="modal-price-normal">
                        <span id="modal-normal-price" class="text-xl font-bold text-slate-800"></span>
                    </div>
                </div>

                {{-- Số lượng --}}
                <div class="flex items-center gap-3 bg-slate-100 rounded-full px-2 py-1">
                    <button id="modal-qty-minus"
                        class="w-7 h-7 flex items-center justify-center rounded-full bg-white text-slate-600 shadow-xs hover:text-red-500 transition cursor-pointer font-bold">
                        −
                    </button>
                    <span id="modal-qty" class="text-sm font-bold text-slate-800 w-4 text-center">1</span>
                    <button id="modal-qty-plus"
                        class="w-7 h-7 flex items-center justify-center rounded-full bg-white text-slate-600 shadow-xs hover:text-red-500 transition cursor-pointer font-bold">
                        +
                    </button>
                </div>

                {{-- Thêm vào giỏ --}}
                <button id="modal-add-cart-btn"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-full transition cursor-pointer text-sm">
                    Thêm vào giỏ
                </button>
            </div>
        </div>
    </div>
</div>