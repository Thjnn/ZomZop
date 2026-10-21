<div class="zo-food">
    <div class="zo-food-photo">@if($line->menuItem)<img src="{{ $line->menuItem->image_url }}" alt="{{ $line->name_snapshot }}" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span hidden>Chưa có ảnh</span>@else<span>Món ngừng bán</span>@endif</div>
    <div class="zo-food-info"><h3>{{ $line->name_snapshot }}</h3><p>{{ $line->quantity }} × {{ number_format($line->price_snapshot, 0, ',', '.') }}đ</p>@if($line->topping_names)<small>Thêm: {{ $line->topping_names }}</small>@endif @if($line->note)<small>Ghi chú: {{ $line->note }}</small>@endif</div>
    <strong class="zo-line-total">{{ number_format($line->subtotal, 0, ',', '.') }}đ</strong>
</div>
