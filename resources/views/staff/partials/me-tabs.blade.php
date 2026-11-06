{{-- Tab con của "Của tôi" — chỉ nhân viên bán hàng có tab đơn hôm nay --}}
@if (auth()->user()->isStaff())
    <div class="flex gap-2 mb-5 border-b border-slate-200">
        @foreach ([['staff.me.orders', 'Đơn hôm nay'], ['staff.dashboard', 'Chấm công & lương']] as [$route, $label])
            <a href="{{ route($route) }}"
               class="px-4 py-2.5 -mb-px border-b-2 text-sm font-semibold {{ request()->routeIs($route) ? 'border-red-500 text-red-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">{{ $label }}</a>
        @endforeach
    </div>
@endif
