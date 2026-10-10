@extends('layouts.admin')

@section('title', 'Báo cáo')

@section('content')
    @php
        $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ';
        $query = ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'branch_id' => $branch?->id];
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl font-bold">Báo cáo doanh thu — {{ $branch?->name ?? 'Toàn chuỗi' }}</h1>
        <a href="{{ route('admin.reports.export', $query) }}" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">Xuất Excel</a>
    </div>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <label class="grid gap-1">Từ ngày <input type="date" name="from" value="{{ $from->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200"></label>
        <label class="grid gap-1">Đến ngày <input type="date" name="to" value="{{ $to->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200"></label>
        <label class="grid gap-1">Chi nhánh
            <select name="branch_id" class="px-3 py-2 rounded-lg border border-slate-200">
                <option value="">Toàn chuỗi</option>
                @foreach ($branches as $b) <option value="{{ $b->id }}" @selected($branch?->id === $b->id)>{{ $b->name }}</option> @endforeach
            </select>
        </label>
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Xem</button>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        @foreach ([['Doanh thu', $money($summary['revenue'])], ['Số đơn', $summary['orders']], ['Hoàn thành', $summary['completed']], ['Đã huỷ', $summary['cancelled']], ['TB/đơn', $money($summary['avg_order'])], ['Tỉ lệ huỷ', $summary['cancel_rate'] . '%']] as [$label, $value])
            <div class="bg-white rounded-2xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-lg font-bold mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    @if ($byBranch)
        <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto mb-6">
            <h2 class="font-semibold px-4 pt-4">So sánh chi nhánh</h2>
            <table class="w-full text-sm mt-2">
                <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                    <tr><th class="px-4 py-2">Chi nhánh</th><th class="px-4 py-2">Số đơn</th><th class="px-4 py-2">Doanh thu</th><th class="px-4 py-2">Đơn huỷ</th><th class="px-4 py-2">% doanh thu chuỗi</th></tr>
                </thead>
                <tbody>
                    @foreach ($byBranch as $r)
                        <tr class="border-b border-slate-50">
                            <td class="px-4 py-2"><a href="{{ route('admin.reports.index', ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'branch_id' => $r['branch']->id]) }}" class="hover:text-red-500">{{ $r['branch']->name }}</a></td>
                            <td class="px-4 py-2">{{ $r['orders'] }}</td>
                            <td class="px-4 py-2 font-semibold">{{ $money($r['revenue']) }}</td>
                            <td class="px-4 py-2">{{ $r['cancelled'] }}</td>
                            <td class="px-4 py-2">{{ $summary['revenue'] ? round($r['revenue'] / $summary['revenue'] * 100, 1) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-4 text-sm">
            <h2 class="font-semibold mb-2">Theo ngày</h2>
            <table class="w-full">
                @foreach ($daily as $day => $d)
                    <tr class="border-b border-slate-50"><td class="py-1.5">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</td><td>{{ $d['orders'] }} đơn</td><td class="text-right font-semibold">{{ $money($d['revenue']) }}</td></tr>
                @endforeach
            </table>
        </div>
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 text-sm">
                <h2 class="font-semibold mb-2">Hình thức & thanh toán (đơn hoàn thành)</h2>
                @foreach ($byType as $k => $d) <p class="flex justify-between py-1"><span>{{ $typeLabels[$k] }}</span><span>{{ $d['orders'] }} đơn · {{ $money($d['revenue']) }}</span></p> @endforeach
                <hr class="my-2 border-slate-100">
                @foreach ($byPay as $k => $d) <p class="flex justify-between py-1"><span>{{ $payLabels[$k] }}</span><span>{{ $d['orders'] }} đơn · {{ $money($d['revenue']) }}</span></p> @endforeach
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 p-4 text-sm">
                <h2 class="font-semibold mb-2">Món bán chạy</h2>
                @forelse ($topItems as $item)
                    <p class="flex justify-between py-1"><span>{{ $item->name }}</span><span>{{ (int) $item->qty }} phần · {{ $money($item->revenue) }}</span></p>
                @empty
                    <p class="text-slate-400">Chưa có đơn hoàn thành.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
