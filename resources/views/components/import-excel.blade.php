{{-- Nút Nhập Excel: tải file mẫu, chọn file rồi gửi. Lỗi từng dòng hiện ở khung lỗi chung của layout --}}
@props(['action', 'template', 'hint' => null])
<details class="relative">
    <summary class="list-none cursor-pointer select-none px-3 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">Nhập Excel ▾</summary>
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="absolute right-0 z-30 mt-2 w-72 grid gap-2 p-3 bg-white border border-slate-200 rounded-xl shadow-lg text-sm text-slate-700">
        @csrf
        <a href="{{ $template }}" class="text-sky-600 hover:underline">⬇ Tải file mẫu (.xlsx)</a>
        @if ($hint) <p class="text-xs text-slate-500">{{ $hint }}</p> @endif
        <input type="file" name="file" accept=".xlsx,.csv" required class="text-xs">
        <p class="text-xs text-slate-400">Có dòng sai thì không lưu dòng nào.</p>
        <button class="px-3 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold cursor-pointer">Nhập</button>
    </form>
</details>
