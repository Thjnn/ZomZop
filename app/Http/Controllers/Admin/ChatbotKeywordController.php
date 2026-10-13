<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\ChatbotKeyword;
use Illuminate\Http\Request;

class ChatbotKeywordController extends AdminController
{
    private const MESSAGES = [
        'keywords.required'        => 'Vui lòng nhập ít nhất một từ khoá.',
        'reply.required'           => 'Vui lòng nhập câu trả lời.',
        'reply.max'                => 'Câu trả lời tối đa 1000 ký tự.',
        'link_url.regex'           => 'Link phải là trang trong web, bắt đầu bằng / (VD: /coupons).',
        'link_label.required_with' => 'Nhập chữ hiện trên nút khi có link.',
        'link_label.max'           => 'Chữ trên nút tối đa 50 ký tự.',
    ];

    private function payload(Request $request): array
    {
        $data = $request->validate([
            'keywords'   => ['required', 'string', 'max:255'],
            'reply'      => ['required', 'string', 'max:1000'],
            'link_url'   => ['nullable', 'string', 'max:255', 'regex:/^\/(?![\/\\\\])/'],
            'link_label' => ['nullable', 'required_with:link_url', 'string', 'max:50'],
        ], self::MESSAGES);
        $data['is_active'] = $request->boolean('is_active');
        if (empty($data['link_url'])) {
            $data['link_url'] = $data['link_label'] = null;
        }

        return $data;
    }

    public function index()
    {
        return view('admin.chatbot-keywords.index', ['keywords' => ChatbotKeyword::orderByDesc('id')->get()]);
    }

    public function create()
    {
        return view('admin.chatbot-keywords.form', ['keyword' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->payload($request);
        $keyword = ChatbotKeyword::create($data);
        $this->log('chatbot_keyword.create', $keyword, ['after' => $data]);

        return redirect()->route('admin.chatbot-keywords.index')->with('success', 'Đã thêm từ khoá.');
    }

    public function edit(ChatbotKeyword $chatbotKeyword)
    {
        return view('admin.chatbot-keywords.form', ['keyword' => $chatbotKeyword]);
    }

    public function update(Request $request, ChatbotKeyword $chatbotKeyword)
    {
        $chatbotKeyword->fill($this->payload($request));
        $changes = AdminLog::diff($chatbotKeyword);
        $chatbotKeyword->save();
        $this->log('chatbot_keyword.update', $chatbotKeyword, $changes);

        return redirect()->route('admin.chatbot-keywords.index')->with('success', 'Đã cập nhật từ khoá.');
    }

    public function toggle(ChatbotKeyword $chatbotKeyword)
    {
        $chatbotKeyword->update(['is_active' => !$chatbotKeyword->is_active]);
        $this->log('chatbot_keyword.toggle', $chatbotKeyword, ['after' => ['is_active' => $chatbotKeyword->is_active]]);

        return back()->with('success', $chatbotKeyword->is_active ? 'Đã bật từ khoá.' : 'Đã tắt từ khoá.');
    }

    public function destroy(ChatbotKeyword $chatbotKeyword)
    {
        $chatbotKeyword->delete();
        $this->log('chatbot_keyword.delete', $chatbotKeyword, ['before' => $chatbotKeyword->only(['keywords', 'reply', 'link_url'])]);

        return back()->with('success', 'Đã xoá từ khoá.');
    }
}
