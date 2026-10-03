<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    private function rules(): array
    {
        return [
            'label'   => 'required|in:Nhà,Công ty,Khác',
            'name'    => 'required|string|max:100',
            'phone'   => ['required', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'address' => 'required|string|max:255',
            'note'    => 'nullable|string|max:255',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required'    => 'Vui lòng nhập họ tên người nhận.',
            'phone.required'   => 'Vui lòng nhập số điện thoại.',
            'phone.regex'      => 'Số điện thoại không hợp lệ (VD: 0901234567).',
            'address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
        ];
    }

    private function own(Address $address): void
    {
        abort_if($address->user_id !== auth()->id(), 403);
    }

    public function index()
    {
        $addresses = Address::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->merge(['phone' => preg_replace('/\s+/', '', (string) $request->phone)]);
        $data = $request->validate($this->rules(), $this->messages());

        $userId = auth()->id();
        $isFirst = !Address::where('user_id', $userId)->exists();
        $makeDefault = $isFirst || $request->boolean('is_default');

        if ($makeDefault) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        Address::create($data + ['user_id' => $userId, 'is_default' => $makeDefault]);

        return redirect()->route('addresses.index')->with('success', 'Đã thêm địa chỉ mới.');
    }

    public function update(Request $request, Address $address)
    {
        $this->own($address);

        $request->merge(['phone' => preg_replace('/\s+/', '', (string) $request->phone)]);
        $data = $request->validate($this->rules(), $this->messages());

        if ($request->boolean('is_default')) {
            Address::where('user_id', $address->user_id)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $address->update($data);

        return redirect()->route('addresses.index')->with('success', 'Đã cập nhật địa chỉ.');
    }

    public function destroy(Address $address)
    {
        $this->own($address);

        $wasDefault = $address->is_default;
        $userId = $address->user_id;
        $address->delete();

        if ($wasDefault) {
            Address::where('user_id', $userId)->orderByDesc('id')->first()?->update(['is_default' => true]);
        }

        return redirect()->route('addresses.index')->with('success', 'Đã xóa địa chỉ.');
    }

    public function setDefault(Address $address)
    {
        $this->own($address);

        Address::where('user_id', $address->user_id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return redirect()->route('addresses.index')->with('success', 'Đã đặt làm địa chỉ mặc định.');
    }
}