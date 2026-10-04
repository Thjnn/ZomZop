@php
    $colors = [
        'pending'   => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'cooking'   => 'bg-orange-100 text-orange-700',
        'ready'     => 'bg-purple-100 text-purple-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-slate-200 text-slate-600',
    ];
@endphp
<span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $colors[$status] ?? 'bg-slate-100' }}">
    {{ \App\Services\OrderStatusService::LABELS[$status] ?? $status }}
</span>
