@extends('layouts.admin')

@section('title', 'Cài đặt chung')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">Cài đặt chung</h1>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf @method('PUT')

        @foreach ($fields as $key => [$label])
            <label class="grid gap-1">{{ $label }}
                @if ($key === 'footer_about')
                    <textarea name="{{ $key }}" rows="3" class="{{ $input }}">{{ old($key, $values[$key]) }}</textarea>
                @else
                    <input name="{{ $key }}" value="{{ old($key, $values[$key]) }}" class="{{ $input }}">
                @endif
            </label>
        @endforeach

        <fieldset class="grid gap-2">
            <legend class="font-semibold mb-1">Phương thức thanh toán cho khách</legend>
            @foreach ($payments as $key => $label)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="payment_{{ $key }}" value="1" @checked(old("payment_{$key}", in_array($key, $enabled, true))) class="accent-red-500"> {{ $label }}
                </label>
            @endforeach
            <p class="text-xs text-slate-400">MoMo/VNPay hiện chưa nối cổng thanh toán — khách chọn thì chỉ ghi nhận phương thức.</p>
        </fieldset>

        <button class="justify-self-start px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu cài đặt</button>
    </form>
@endsection
