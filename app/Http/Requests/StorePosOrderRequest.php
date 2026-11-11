<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Support\PaymentMethods;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Giỏ hàng POS gửi lên. Chỉ nhận id món + số lượng + ghi chú;
 * giá/tên do server tự lấy, client có gửi price cũng bị bỏ qua.
 */
class StorePosOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Quyền đã chặn ở route (role:staff); thiếu chi nhánh thì không bán được
        return (bool) $this->user()?->branch_id;
    }

    public function rules(): array
    {
        return [
            'type'                 => ['required', Rule::in(array_keys(Order::TYPE_LABELS))],
            'table_number'         => ['required_if:type,dine_in', 'nullable', 'string', 'max:10'],
            'customer_name'        => ['required_if:type,delivery', 'nullable', 'string', 'max:100'],
            'customer_phone'       => ['required_if:type,delivery', 'nullable', 'string', 'regex:/^[0-9+ .]{8,20}$/'],
            'delivery_address'     => ['required_if:type,delivery', 'nullable', 'string', 'max:500'],
            'payment_method'       => ['required', Rule::in(array_keys(PaymentMethods::POS_LABELS))],
            'cash_received'        => ['required_if:payment_method,cash', 'nullable', 'integer', 'min:0', 'max:1000000000'],
            'note'                 => ['nullable', 'string', 'max:500'],
            'items'                => ['required', 'array', 'min:1', 'max:50'],
            'items.*.menu_item_id' => ['required', 'integer'],
            'items.*.quantity'     => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.note'         => ['nullable', 'string', 'max:255'],
            // [{id, quantity}]; quantity = 0 bị bỏ qua. Còn bán / đúng món / món Burger kiểm tra lại trong controller
            'items.*.toppings'            => ['nullable', 'array', 'max:20'],
            'items.*.toppings.*.id'       => ['required', 'integer', 'distinct'],
            'items.*.toppings.*.quantity' => ['required', 'integer', 'min:0', 'max:' . config('zomzop.topping_max_qty', 5)],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'              => 'Vui lòng chọn loại đơn.',
            'type.in'                    => 'Loại đơn không hợp lệ.',
            'table_number.required_if'   => 'Vui lòng nhập số bàn cho đơn tại quán.',
            'customer_name.required_if'  => 'Đơn giao hàng cần tên khách.',
            'customer_phone.required_if' => 'Đơn giao hàng cần số điện thoại khách.',
            'customer_phone.regex'       => 'Số điện thoại không hợp lệ.',
            'delivery_address.required_if' => 'Đơn giao hàng cần địa chỉ nhận.',
            'payment_method.required'    => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'          => 'Phương thức thanh toán không hợp lệ.',
            'cash_received.required_if'  => 'Vui lòng nhập số tiền khách đưa.',
            'cash_received.integer'      => 'Số tiền khách đưa không hợp lệ.',
            'items.required'             => 'Giỏ hàng đang trống.',
            'items.min'                  => 'Giỏ hàng đang trống.',
            'items.max'                  => 'Một đơn tối đa 50 dòng món.',
            'items.*.menu_item_id.*'     => 'Món trong giỏ không hợp lệ.',
            'items.*.quantity.*'         => 'Số lượng mỗi món từ 1 đến 99.',
            'items.*.note.max'           => 'Ghi chú món tối đa 255 ký tự.',
            'items.*.toppings.*.id.*'    => 'Topping trong giỏ không hợp lệ.',
            'items.*.toppings.*.quantity.*' => 'Số phần mỗi topping từ 0 đến ' . config('zomzop.topping_max_qty', 5) . '.',
            'note.max'                   => 'Ghi chú đơn tối đa 500 ký tự.',
            '*.max'                      => ':attribute quá dài.',
        ];
    }

    public function attributes(): array
    {
        return ['table_number' => 'Số bàn', 'customer_name' => 'Tên khách', 'delivery_address' => 'Địa chỉ'];
    }
}
