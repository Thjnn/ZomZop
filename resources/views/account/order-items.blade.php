<div class="space-y-3">
@foreach($order->items as $line)
<div class="flex gap-3 items-center">
    @if($line->menuItem)
    <img src="{{ $line->menuItem->image_url }}" alt="{{ $line->name_snapshot }}" class="w-16 h-16 rounded-xl object-cover bg-orange-50" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
    <span hidden>Ảnh món chưa có</span>
    @else <span class="text-xs text-slate-400">Món đã ngừng bán</span> @endif
    <div class="flex-1"><p class="font-semibold">{{ $line->name_snapshot }}</p><p class="text-sm text-slate-500">{{ $line->quantity }} × {{ number_format($line->price_snapshot, 0, ',', '.') }}đ</p>@if($line->note)<p class="text-xs text-slate-500">{{ $line->note }}</p>@endif</div>
    <strong>{{ number_format($line->subtotal, 0, ',', '.') }}đ</strong>
</div>
@endforeach
</div>
