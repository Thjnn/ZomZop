<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Banner;
use App\Support\PublicUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class BannerController extends AdminController
{
    private const MESSAGES = [
        'title.required'          => 'Vui lòng nhập tiêu đề.',
        'image.required'          => 'Vui lòng chọn ảnh banner.',
        'image.image'             => 'Tệp tải lên phải là hình ảnh.',
        'image.mimes'             => 'Ảnh chỉ nhận jpg, png, webp.',
        'image.max'               => 'Ảnh tối đa 2MB.',
        'link.regex'              => 'Link phải bắt đầu bằng http://, https:// hoặc / (trang trong web).',
        'sort_order.*'            => 'Thứ tự là số nguyên từ 0 đến 999.',
        'ended_at.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
    ];

    private function rules(Request $request, bool $creating): array
    {
        // after_or_equal:started_at chỉ áp khi có ngày bắt đầu (để trống ngày bắt đầu vẫn hợp lệ)
        $afterStart = $request->filled('started_at') ? ['after_or_equal:started_at'] : [];

        return [
            'title'      => ['required', 'string', 'max:150'],
            'image'      => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'link'       => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/i'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'started_at' => ['nullable', 'date'],
            'ended_at'   => ['nullable', 'date', ...$afterStart],
        ];
    }

    private function payload(Request $request, bool $creating): array
    {
        $data = $request->validate($this->rules($request, $creating), self::MESSAGES);
        $data['is_active'] = $request->boolean('is_active');
        if (!empty($data['ended_at'])) {
            $data['ended_at'] = Carbon::parse($data['ended_at'])->endOfDay(); // hiện hết ngày kết thúc
        }

        return Arr::except($data, 'image');
    }

    public function index()
    {
        return view('admin.banners.index', ['banners' => Banner::orderBy('sort_order')->orderByDesc('id')->get()]);
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->payload($request, true);
        $data['image'] = PublicUpload::store($request->file('image'), 'banners');

        $banner = Banner::create($data);
        $this->log('banner.create', $banner, ['after' => $data]);

        return redirect()->route('admin.banners.index')->with('success', 'Đã thêm banner.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->payload($request, false);
        $old  = null;
        if ($request->hasFile('image')) {
            $old = $banner->image;
            $data['image'] = PublicUpload::store($request->file('image'), 'banners');
        }

        $banner->fill($data);
        $changes = AdminLog::diff($banner);
        $banner->save();
        PublicUpload::delete('banners', $old); // xoá ảnh cũ sau khi lưu thành công
        $this->log('banner.update', $banner, $changes);

        return redirect()->route('admin.banners.index')->with('success', 'Đã cập nhật banner.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        PublicUpload::delete('banners', $banner->image);
        $this->log('banner.delete', $banner, ['before' => $banner->only(['title', 'image', 'link'])]);

        return back()->with('success', 'Đã xoá banner.');
    }
}
