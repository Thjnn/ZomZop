<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Từ khoá → câu trả lời soạn sẵn của chatbot (admin quản lý) */
class ChatbotKeyword extends Model
{
    protected $fillable = ['keywords', 'reply', 'link_url', 'link_label', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** "Cho XEM thực đơn?!" → "cho xem thuc don" */
    public static function normalize(string $text): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($text))));
    }

    /**
     * Dòng đang bật có từ khoá nằm trọn trong tin nhắn (theo ranh giới từ).
     * Nhiều dòng khớp → lấy dòng có từ khoá dài nhất (cụ thể nhất).
     * ponytail: quét cả bảng mỗi tin, ổn với vài chục từ khoá; nhiều hơn thì cache danh sách đã chuẩn hoá.
     */
    public static function findFor(string $message): ?self
    {
        $text = ' ' . static::normalize($message) . ' ';
        $best = null;
        $bestLength = 0;

        foreach (static::where('is_active', true)->get() as $row) {
            foreach (explode(',', $row->keywords) as $keyword) {
                $keyword = static::normalize($keyword);
                if ($keyword !== '' && strlen($keyword) > $bestLength && str_contains($text, " {$keyword} ")) {
                    $best = $row;
                    $bestLength = strlen($keyword);
                }
            }
        }

        return $best;
    }
}
