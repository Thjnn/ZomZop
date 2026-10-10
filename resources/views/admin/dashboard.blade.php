@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp
    <h1 class="text-xl font-bold mb-4">Tổng quan toàn chuỗi</h1>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Doanh thu hôm nay', $money($today['revenue'])],
            ['Đơn hôm nay', $today['total']],
            ['Doanh thu tháng này', $money($month['revenue'])],
            ['Đơn tháng này', $month['total']],
            ['Chi nhánh đang mở', $branches],
            ['Khách mới tháng này', $newCustomers],
            ['Đơn chờ xác nhận (hôm nay)', $today['pending']],
            ['Đơn huỷ tháng này', $month['cancelled']],
        ] as [$label, $value])
            <div class="bg-white rounded-2xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-xl font-bold mt-1">{{ is_int($value) ? number_format($value, 0, ',', '.') : $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h2 class="font-semibold mb-3">Doanh thu theo chi nhánh — tháng này</h2>
            @php $max = max(1, $ranking->max('revenue')); @endphp
            <div class="space-y-3 text-sm">
                @foreach ($ranking as $row)
                    <div>
                        <div class="flex justify-between gap-2">
                            <span class="{{ $row['branch']->is_active ? '' : 'text-slate-400' }}">{{ $row['branch']->name }}</span>
                            <span class="font-semibold">{{ $money($row['revenue']) }} <span class="text-xs text-slate-400 font-normal">· {{ $row['orders'] }} đơn</span></span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 mt-1"><div class="h-2 rounded-full bg-red-400" style="width: {{ round($row['revenue'] / $max * 100) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h2 class="font-semibold mb-3">7 ngày gần nhất</h2>
            @php $maxDay = max(1, max($series['revenue'])); @endphp
            <div class="flex items-end gap-2 h-40">
                @foreach ($series['labels'] as $i => $label)
                    <div class="flex-1 flex flex-col items-center gap-1" title="{{ $label }}: {{ $money($series['revenue'][$i]) }} · {{ $series['orders'][$i] }} đơn">
                        <div class="w-full rounded-t bg-red-400" style="height: {{ max(2, round($series['revenue'][$i] / $maxDay * 130)) }}px"></div>
                        <span class="text-[10px] text-slate-400">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
