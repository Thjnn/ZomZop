@extends('layouts.app')
 
@section('title', 'Địa chỉ giao hàng - ZomZop')
 
@section('content')
@php $input = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
 
<div class="max-w-4xl mx-auto py-8 px-4">
    <a href="{{ url('/menu') }}" class="text-sm text-slate-500 hover:text-red-500">← Quay lại tài khoản</a>
    <div class="flex items-center justify-between gap-3 mt-2 mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Địa chỉ giao hàng</h1>
        <button type="button" onclick="openAddr()" class="px-4 py-2 rounded-xl bg-red-500 text-white text-sm font-semibold hover:bg-red-600 whitespace-nowrap">
            + Thêm địa chỉ
        </button>
    </div>
 
    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
 
    @if ($addresses->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 p-10 text-center text-slate-400">
            Bạn chưa lưu địa chỉ nào. Bấm "+ Thêm địa chỉ" để bắt đầu.
        </div>
    @endif
 
    <div class="grid md:grid-cols-2 gap-4">
        @foreach ($addresses as $a)
        <div class="bg-white rounded-2xl border {{ $a->is_default ? 'border-red-300' : 'border-slate-100' }} shadow-sm p-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full bg-red-50 text-red-500 text-xs font-semibold">{{ $a->label }}</span>
                @if ($a->is_default)
                    <span class="px-2.5 py-0.5 rounded-full bg-green-50 text-green-600 text-xs font-semibold">Mặc định</span>
                @endif
            </div>
            <p class="font-semibold text-slate-800">{{ $a->name }} <span class="font-normal text-slate-400">· {{ $a->phone }}</span></p>
            <p class="text-sm text-slate-500 mt-1">{{ $a->address }}</p>
            @if ($a->note)
                <p class="text-xs text-slate-400 mt-1">Ghi chú: {{ $a->note }}</p>
            @endif
 
            <div class="flex flex-wrap items-center gap-4 mt-4 text-sm">
                <button type="button" onclick='openAddr(@json($a))' class="text-slate-600 hover:text-red-500">Sửa</button>
 
                <form method="POST" action="{{ route('addresses.destroy', $a) }}" onsubmit="return confirm('Xóa địa chỉ này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-600 hover:text-red-500">Xóa</button>
                </form>
 
                @unless ($a->is_default)
                    <form method="POST" action="{{ route('addresses.default', $a) }}">
                        @csrf
                        <button type="submit" class="text-red-500 font-medium">Đặt làm mặc định</button>
                    </form>
                @endunless
            </div>
        </div>
        @endforeach
    </div>
</div>
 
{{-- Popup thêm / sửa địa chỉ --}}
<div id="addrModal" class="fixed inset-0 bg-black/40 z-[100] hidden items-center justify-center p-4">
    <form id="addrForm" method="POST" action="{{ route('addresses.store') }}" class="bg-white rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        @csrf
        <input type="hidden" name="_method" value="PUT" id="addrMethod" disabled>
        <input type="hidden" name="editing_id" id="editing_id" value="{{ old('editing_id') }}">
 
        <div class="flex justify-between items-center mb-4">
            <h2 id="addrTitle" class="font-bold text-lg text-slate-800">Thêm địa chỉ</h2>
            <button type="button" onclick="closeAddr()" class="text-slate-400 text-xl">✕</button>
        </div>
 
        @if ($errors->any())
            <div class="mb-3 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm space-y-1">
                @foreach ($errors->all() as $e) <p>• {{ $e }}</p> @endforeach
            </div>
        @endif
 
        <div class="space-y-3">
            <div class="flex gap-2">
                @foreach (['Nhà', 'Công ty', 'Khác'] as $l)
                <label class="cursor-pointer">
                    <input type="radio" name="label" value="{{ $l }}" class="peer hidden" {{ old('label', 'Nhà') === $l ? 'checked' : '' }}>
                    <span class="px-4 py-1.5 rounded-full border border-slate-200 text-sm inline-block peer-checked:bg-red-500 peer-checked:text-white peer-checked:border-red-500">{{ $l }}</span>
                </label>
                @endforeach
            </div>
            <input id="f_name" name="name" value="{{ old('name') }}" placeholder="Họ tên người nhận" class="{{ $input }}">
            <input id="f_phone" name="phone" value="{{ old('phone') }}" placeholder="Số điện thoại" class="{{ $input }}">
            <input id="f_address" name="address" value="{{ old('address') }}" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành" class="{{ $input }}">
            <input id="f_note" name="note" value="{{ old('note') }}" placeholder="Ghi chú cho shipper (không bắt buộc)" class="{{ $input }}">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" id="f_default" name="is_default" value="1" class="accent-red-500" {{ old('is_default') ? 'checked' : '' }}>
                Đặt làm địa chỉ mặc định
            </label>
        </div>
        <button type="submit" class="w-full mt-5 py-2.5 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-600">Lưu địa chỉ</button>
    </form>
</div>
 
<script>
    const addrModal  = document.getElementById('addrModal');
    const addrForm   = document.getElementById('addrForm');
    const addrBase   = @json(url('/addresses'));
 
    function setMode(id) {
        document.getElementById('addrMethod').disabled = !id;
        document.getElementById('editing_id').value    = id || '';
        addrForm.action = id ? addrBase + '/' + id : addrBase;
        document.getElementById('addrTitle').textContent = id ? 'Sửa địa chỉ' : 'Thêm địa chỉ';
    }
    function showAddrModal() { addrModal.classList.remove('hidden'); addrModal.classList.add('flex'); }
    function closeAddr()     { addrModal.classList.add('hidden');    addrModal.classList.remove('flex'); }
 
    function openAddr(a) {
        setMode(a ? a.id : null);
        document.getElementById('f_name').value    = a ? a.name : '';
        document.getElementById('f_phone').value   = a ? a.phone : '';
        document.getElementById('f_address').value = a ? a.address : '';
        document.getElementById('f_note').value    = a && a.note ? a.note : '';
        document.getElementById('f_default').checked = a ? !!a.is_default : false;
        addrForm.querySelector('input[name=label][value="' + (a ? a.label : 'Nhà') + '"]').checked = true;
        showAddrModal();
    }
    addrModal.addEventListener('click', e => { if (e.target === addrModal) closeAddr(); });
 
    @if ($errors->any())
        // Lưu lỗi -> mở lại popup, giữ nguyên dữ liệu đã nhập
        setMode(@json(old('editing_id')));
        showAddrModal();
    @endif
</script>
@endsection
 