<?php

namespace App\Services;

use App\Models\ChatbotKeyword;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Trả lời khách: từ khoá soạn sẵn trước, không khớp thì hỏi Gemini, lỗi thì câu dự phòng */
class Chatbot
{
    public const FALLBACK = 'Xin lỗi, mình đang bận một chút, bạn thử lại sau nhé.';

    public function __construct(private BranchMenu $menu)
    {
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history  tin cũ, chưa gồm $message
     * @return array{reply: string, link_url: ?string, link_label: ?string, source: string}
     */
    public function reply(string $message, array $history): array
    {
        if ($keyword = ChatbotKeyword::findFor($message)) {
            return [
                'reply'      => $keyword->reply,
                'link_url'   => $keyword->link_url,
                'link_label' => $keyword->link_label,
                'source'     => 'keyword',
            ];
        }

        $text = $this->askGemini($message, $history);

        return [
            'reply'      => $text ?? self::FALLBACK,
            'link_url'   => null,
            'link_label' => null,
            'source'     => $text === null ? 'fallback' : 'gemini',
        ];
    }

    private function askGemini(string $message, array $history): ?string
    {
        $key = config('services.gemini.key');
        if (!$key) {
            Log::warning('Chatbot: chưa cấu hình GEMINI_API_KEY');

            return null;
        }

        // Gemini cần lượt đầu là "user": bỏ các tin bot đứng đầu, bot → "model"
        $contents = collect($history)->slice(-6)
            ->map(fn ($m) => ['role' => $m['role'] === 'user' ? 'user' : 'model', 'parts' => [['text' => $m['content']]]])
            ->skipUntil(fn ($c) => $c['role'] === 'user')
            ->push(['role' => 'user', 'parts' => [['text' => $message]]])
            ->values()->all();

        try {
            $response = Http::timeout(config('services.gemini.timeout'))
                ->withHeaders(['x-goog-api-key' => $key])
                ->post('https://generativelanguage.googleapis.com/v1beta/models/' . config('services.gemini.model') . ':generateContent', [
                    'systemInstruction' => ['parts' => [['text' => $this->instruction()]]],
                    'contents'          => $contents,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Chatbot: không gọi được Gemini', ['error' => $e->getMessage()]);

            return null;
        }

        $text = trim(collect($response->json('candidates.0.content.parts', []))->pluck('text')->implode(''));
        if ($response->failed() || $text === '') {
            Log::warning('Chatbot: Gemini trả lỗi hoặc không có chữ', ['status' => $response->status()]);

            return null;
        }

        return $text;
    }

    private function instruction(): string
    {
        $branchId = $this->menu->currentBranchId();
        $items = $this->menu->filterAvailable(MenuItem::query(), $branchId)->orderBy('menu_items.name')->get();
        $this->menu->applyPrices($items, $branchId);

        $menu = $items->map(fn (MenuItem $item) => "- {$item->name} — {$item->display_price} — "
            . Str::limit(strip_tags((string) $item->description), 120))->implode("\n");

        return <<<TEXT
        Bạn là trợ lý chăm sóc khách hàng của ZomZop, chuỗi cửa hàng đồ ăn nhanh tại Việt Nam.
        Trả lời bằng tiếng Việt, ngắn gọn, thân thiện, không dùng định dạng Markdown.
        Chỉ trả lời về món ăn, dinh dưỡng, cách đặt món và ZomZop. Câu hỏi ngoài phạm vi thì từ chối nhẹ nhàng và gợi ý hỏi về món ăn.
        Calo và dinh dưỡng là ước tính, hãy nói rõ là ước tính.
        Chỉ nhắc đến các món có trong danh sách dưới đây, không bịa món hay giá.
        Chỉ gợi ý khách quan: nêu thông tin và vài lựa chọn phù hợp kèm lý do, không thúc ép hay quyết định thay khách; việc chọn món là của khách.
        Khi nhắc đến giá, nói rõ đó là giá tham khảo tại thời điểm hiện tại, có thể thay đổi, giá chính xác xem ở trang món hoặc giỏ hàng.

        Danh sách món đang bán (tên — giá — mô tả):
        {$menu}
        TEXT;
    }
}
