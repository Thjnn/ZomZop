{{-- Tab Năm/Tháng/Tuần cho biểu đồ; JS đổi dữ liệu theo data-range --}}
<div class="flex rounded-lg border border-slate-200 p-0.5 text-xs font-medium">
    @foreach (['year' => 'Năm nay', 'month' => 'Tháng này', 'week' => '7 ngày'] as $range => $label)
        <button type="button" data-range="{{ $range }}"
                class="px-3 py-1.5 rounded-md cursor-pointer {{ $loop->first ? 'bg-red-500 text-white' : 'text-red-500 hover:bg-red-50' }}">
            {{ $label }}
        </button>
    @endforeach
</div>
