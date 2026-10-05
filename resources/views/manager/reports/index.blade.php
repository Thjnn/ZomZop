@extends('layouts.manager')

@section('title', 'Báo cáo')

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $typeLabel = \App\Http\Controllers\Manager\ReportController::TYPE_LABELS;
        $payLabel  = \App\Http\Controllers\Manager\ReportController::PAY_LABELS;
        $max = max(1, max(array_column($daily, 'revenue') ?: [0]));
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold">Báo cáo</h1>
            <p class="text-sm text-slate-500">{{ $from->format('d/m/Y') }} – {{ $to->format('d/m/Y') }}</p>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-2 text-sm">
            <label class="flex flex-col gap-1"><span class="text-xs text-slate-400">Từ ngày</span>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200"></label>
            <label class="flex flex-col gap-1"><span class="text-xs text-slate-400">Đến ngày</span>
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200"></label>
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Xem</button>
            <a href="{{ route('manager.reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-sm">Xuất Excel</a>
        </form>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Doanh thu', $money($summary['revenue']), 'text-green-600'],
            ['Đơn hoàn thành', $summary['completed'] . ' / ' . $summary['orders'], 'text-slate-800'],
            ['Giá trị TB / đơn', $money($summary['avg_order']), 'text-slate-800'],
            ['Tỉ lệ huỷ', number_format($summary['cancel_rate'], 1, ',', '.') . '%', 'text-slate-500'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-2xl p-4 border border-slate-100">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1 {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <section class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100 overflow-x-auto">
            <h2 class="font-semibold mb-4">Doanh thu theo ngày</h2>
            <table class="w-full text-sm">
                @foreach ($daily as $day => $d)
                    <tr class="border-b border-slate-50">
                        <td class="py-1.5 pr-3 whitespace-nowrap text-slate-500">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</td>
                        <td class="py-1.5 w-full"><div class="h-3 rounded bg-red-400" style="width: {{ round($d['revenue'] / $max * 100) }}%"></div></td>
                        <td class="py-1.5 pl-3 whitespace-nowrap text-right">{{ $money($d['revenue']) }}</td>
                        <td class="py-1.5 pl-3 whitespace-nowrap text-right text-slate-400">{{ $d['orders'] }} đơn</td>
                    </tr>
                @endforeach
            </table>
        </section>

        <div class="space-y-6">
            @foreach ([['Theo hình thức', $byType, $typeLabel], ['Theo thanh toán', $byPay, $payLabel]] as [$title, $rows, $labels])
                <section class="bg-white rounded-2xl p-5 border border-slate-100 text-sm">
                    <h2 class="font-semibold mb-3">{{ $title }}</h2>
                    @foreach ($rows as $key => $r)
                        <div class="flex justify-between py-1.5 border-b border-slate-50">
                            <span>{{ $labels[$key] }} <span class="text-slate-400">({{ $r['orders'] }})</span></span>
                            <span class="font-semibold">{{ $money($r['revenue']) }}</span>
                        </div>
                    @endforeach
                </section>
            @endforeach
        </div>
    </div>

    <section class="bg-white rounded-2xl p-5 border border-slate-100 text-sm">
        <h2 class="font-semibold mb-3">Món bán chạy</h2>
        @forelse ($topItems as $i => $item)
            <div class="flex justify-between py-1.5 border-b border-slate-50">
                <span>{{ $i + 1 }}. {{ $item->name }}</span>
                <span class="text-slate-500">{{ $item->qty }} phần · {{ $money($item->revenue) }}</span>
            </div>
        @empty
            <p class="text-slate-400">Không có đơn hoàn thành trong khoảng này.</p>
        @endforelse
    </section>
@endsection
