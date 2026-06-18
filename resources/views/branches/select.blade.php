{{-- resources/views/branches/select.blade.php --}}

@extends('layouts.app')

@section('title', 'Chọn Chi Nhánh — ZomZop')

@section('content')

<div class="max-w-4xl mx-auto py-6">

    {{-- Tiêu đề + Toggle --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Chọn Chi Nhánh</h1>
            <p class="text-sm text-slate-500 mt-1">Chọn chi nhánh gần bạn để đặt món nhanh hơn</p>
        </div>

        {{-- Nút toggle Grid / Map --}}
        <div class="inline-flex bg-slate-100 rounded-lg p-1 gap-1">
            <button id="btn-grid"
                onclick="switchView('grid')"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition bg-white text-red-500 shadow-xs cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" />
                    <rect x="14" y="3" width="7" height="7" />
                    <rect x="3" y="14" width="7" height="7" />
                    <rect x="14" y="14" width="7" height="7" />
                </svg>
                Grid
            </button>
            <button id="btn-map"
                onclick="switchView('map')"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition text-slate-500 hover:text-red-500 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Map
            </button>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('branches.confirm') }}" method="POST" id="branch-form">
        @csrf

        {{-- VIEW: GRID --}}
        <div id="view-grid">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
                @foreach ($branches as $branch)
                @php $isSelected = session('selected_branch_id') == $branch->id; @endphp

                <label for="branch-{{ $branch->id }}" class="relative cursor-pointer group">
                    <input type="radio"
                        name="branch_id"
                        id="branch-{{ $branch->id }}"
                        value="{{ $branch->id }}"
                        class="peer hidden"
                        {{ $isSelected ? 'checked' : '' }}>

                    <div class="rounded-2xl border-2 overflow-hidden transition-all duration-200
                                {{ $isSelected ? 'border-red-500 shadow-lg shadow-red-100' : 'border-slate-200 hover:border-red-300 hover:shadow-md' }}">

                        {{-- Ảnh --}}
                        <div class="h-36 bg-slate-200 overflow-hidden relative">
                            @if ($branch->image)
                            <img src="{{ asset('images/branches/' . $branch->image) }}"
                                alt="{{ $branch->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                            <div class="w-full h-full bg-gradient-to-br from-red-100 to-orange-100 flex items-center justify-center">
                                <span class="text-4xl">🍔</span>
                            </div>
                            @endif

                            @if ($isSelected)
                            <span class="absolute top-3 right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                Đang chọn
                            </span>
                            @endif
                        </div>

                        {{-- Thông tin --}}
                        <div class="p-4 bg-white flex items-center gap-3">
                            <img src="{{ asset('images/branches/logo.png') }}"
                                class="w-12 h-12 rounded-xl object-cover border border-slate-100 flex-shrink-0"
                                alt="{{ $branch->name }}">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-sm text-slate-800 truncate">{{ $branch->name }}</h4>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">📍 {{ $branch->address }}</p>
                                <p class="text-xs text-slate-400">🕐 {{ substr($branch->open_time, 0, 5) }} – {{ substr($branch->close_time, 0, 5) }}</p>
                            </div>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- Nút xác nhận --}}
            <div class="flex justify-center">
                <button type="submit"
                    class="bg-red-500 hover:bg-red-600 text-white font-semibold px-12 py-3 rounded-full transition shadow-lg shadow-red-100 cursor-pointer">
                    Xác Nhận Chi Nhánh
                </button>
            </div>
        </div>

        {{-- VIEW: MAP --}}
        <div id="view-map" class="hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Danh sách chi nhánh bên trái --}}
                <div class="space-y-3 overflow-y-auto max-h-[500px] pr-1">
                    @foreach ($branches as $branch)
                    @php $isSelected = session('selected_branch_id') == $branch->id; @endphp

                    <label for="branch-map-{{ $branch->id }}" class="cursor-pointer block">
                        <input type="radio"
                            name="branch_id"
                            id="branch-map-{{ $branch->id }}"
                            value="{{ $branch->id }}"
                            class="peer hidden"
                            {{ $isSelected ? 'checked' : '' }}
                            onchange="showMap(this.value)">

                        <div class="rounded-2xl border-2 p-4 bg-white flex items-center gap-3 transition-all
                                    {{ $isSelected ? 'border-red-500 shadow-md shadow-red-100' : 'border-slate-200 hover:border-red-300' }}">
                            <img src="{{ asset('images/branches/logo.png') }}"
                                class="w-12 h-12 rounded-xl object-cover border border-slate-100 flex-shrink-0"
                                alt="{{ $branch->name }}">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-sm text-slate-800 truncate">{{ $branch->name }}</h4>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">📍 {{ $branch->address }}</p>
                                <p class="text-xs text-slate-400">🕐 {{ substr($branch->open_time, 0, 5) }} – {{ substr($branch->close_time, 0, 5) }}</p>
                            </div>
                            @if ($isSelected)
                            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full flex-shrink-0">Đang chọn</span>
                            @endif
                        </div>
                    </label>
                    @endforeach

                    {{-- Nút xác nhận --}}
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-full transition shadow-lg shadow-red-100 cursor-pointer">
                            Xác Nhận Chi Nhánh
                        </button>
                    </div>
                </div>

                {{-- Bản đồ bên phải --}}
                <div class="rounded-2xl overflow-hidden shadow-md h-[500px] bg-slate-100">
                    {{-- Thay src bên dưới bằng link embed từ Google Maps của từng chi nhánh --}}
                    @foreach ($branches as $branch)
                    <iframe
                        id="map-{{ $branch->id }}"
                        src="https://maps.google.com/maps?q={{ $branch->lat }},{{ $branch->lng }}&z=16&output=embed"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen
                        loading="lazy"
                        class="{{ session('selected_branch_id') == $branch->id || (!session('selected_branch_id') && $loop->first) ? '' : 'hidden' }}">
                    </iframe>
                    @endforeach
                </div>

            </div>
        </div>

    </form>
</div>

<script>
    function switchView(view) {
        const grid = document.getElementById('view-grid');
        const map = document.getElementById('view-map');
        const btnGrid = document.getElementById('btn-grid');
        const btnMap = document.getElementById('btn-map');

        if (view === 'grid') {
            grid.classList.remove('hidden');
            map.classList.add('hidden');
            btnGrid.classList.add('bg-white', 'text-red-500', 'shadow-xs');
            btnGrid.classList.remove('text-slate-500');
            btnMap.classList.remove('bg-white', 'text-red-500', 'shadow-xs');
            btnMap.classList.add('text-slate-500');
        } else {
            grid.classList.add('hidden');
            map.classList.remove('hidden');
            btnMap.classList.add('bg-white', 'text-red-500', 'shadow-xs');
            btnMap.classList.remove('text-slate-500');
            btnGrid.classList.remove('bg-white', 'text-red-500', 'shadow-xs');
            btnGrid.classList.add('text-slate-500');
        }
    }

    function showMap(branchId) {
        // Ẩn tất cả iframe
        document.querySelectorAll('[id^="map-"]').forEach(el => el.classList.add('hidden'));
        // Hiện iframe của chi nhánh được chọn
        const target = document.getElementById('map-' + branchId);
        if (target) target.classList.remove('hidden');
    }

    // Highlight border khi chọn ở grid view
    document.querySelectorAll('input[name="branch_id"]').forEach(input => {
        input.addEventListener('change', () => {
            document.querySelectorAll('label > div').forEach(div => {
                div.classList.remove('border-red-500', 'shadow-lg', 'shadow-red-100', 'shadow-md');
                div.classList.add('border-slate-200');
            });
            const selected = input.closest('label').querySelector('div');
            selected.classList.add('border-red-500', 'shadow-md', 'shadow-red-100');
            selected.classList.remove('border-slate-200');
        });
    });
</script>

@endsection