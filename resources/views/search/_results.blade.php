{{-- Dùng chung: trang /search và khung kết quả tức thời dưới ô tìm kiếm (fetch) --}}
<div>
    <h1 class="text-2xl font-bold text-slate-800">
        @if ($q !== '') Kết quả cho "{{ $q }}" @else Tìm món ăn @endif
    </h1>
    @if ($q !== '')
    <p class="text-slate-400 text-sm mt-1">{{ $items->count() }} món</p>
    @endif
</div>

<section class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 pt-4">
    @forelse ($items as $item)
    <x-menu-item-card :item="$item" />
    @empty
    <div class="col-span-full text-center py-16 text-slate-400">
        <p class="text-4xl mb-3">🔍</p>
        <p class="font-medium">{{ $q !== '' ? 'Không tìm thấy món nào phù hợp.' : 'Nhập tên món vào ô tìm kiếm phía trên.' }}</p>
    </div>
    @endforelse
</section>
