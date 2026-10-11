@props(['href' => url('/menu'), 'label' => 'Quay lại tài khoản'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-sm text-slate-500 hover:text-red-500 transition mb-2']) }}>
    <span aria-hidden="true">←</span> {{ $label }}
</a>