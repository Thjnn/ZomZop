<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactMessage;

class ContactMessageController extends AdminController
{
    public function index()
    {
        return view('admin.contact-messages.index', [
            // Chưa xử lý lên trước, mới nhất trước
            'messages' => ContactMessage::orderByRaw('handled_at is not null')->latest()->paginate(20),
        ]);
    }

    public function toggle(ContactMessage $contactMessage)
    {
        $contactMessage->forceFill(['handled_at' => $contactMessage->handled_at ? null : now()])->save();
        $this->log('contact_message.toggle', $contactMessage, ['after' => ['handled' => (bool) $contactMessage->handled_at]]);

        return back()->with('success', $contactMessage->handled_at ? 'Đã đánh dấu đã xử lý.' : 'Đã chuyển về chưa xử lý.');
    }
}
