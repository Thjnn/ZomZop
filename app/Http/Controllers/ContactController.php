<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validateWithBag('contact', [
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:150'],
            'phone'   => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'name.required'    => 'Vui lòng nhập họ tên.',
            'email.required'   => 'Vui lòng nhập email.',
            'email.email'      => 'Email không hợp lệ.',
            'phone.regex'      => 'Số điện thoại không hợp lệ (VD: 0901234567).',
            'message.required' => 'Vui lòng nhập nội dung.',
            'message.min'      => 'Nội dung tối thiểu 10 ký tự.',
            'message.max'      => 'Nội dung tối đa 2000 ký tự.',
        ]);

        ContactMessage::create($data + ['user_id' => $request->user()?->id]);

        return redirect()->to(url()->previous() . '#lien-he')
            ->with('contact_sent', 'Cảm ơn bạn! ZomZop đã nhận được tin nhắn và sẽ phản hồi sớm.');
    }
}
