<?php

namespace App\Http\Controllers;

use App\Models\AiChatSession;
use App\Services\Chatbot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function messages(Request $request)
    {
        return response()->json(['messages' => $this->session($request)->getRecentMessages()]);
    }

    public function send(Request $request, Chatbot $bot)
    {
        $data = $request->validate(
            ['message' => ['required', 'string', 'max:500']],
            ['message.required' => 'Bạn chưa nhập tin nhắn.', 'message.max' => 'Tin nhắn tối đa 500 ký tự.']
        );

        $session = $this->session($request);
        $answer  = $bot->reply($data['message'], $session->getRecentMessages());

        $session->addMessage('user', $data['message']);
        $session->addMessage('bot', $answer['reply'], array_filter([
            'link_url'   => $answer['link_url'],
            'link_label' => $answer['link_label'],
        ]));

        return response()->json($answer);
    }

    /** Mỗi khách một phiên chat */
    private function session(Request $request): AiChatSession
    {
        return AiChatSession::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['session_token' => Str::random(40)]
        );
    }
}
