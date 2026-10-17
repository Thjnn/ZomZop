<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Mỗi khách một phiên chat: 2 tin gửi cùng lúc lần đầu không tạo được 2 dòng (firstOrCreate bắt lỗi trùng rồi đọc lại)
    public function up(): void
    {
        // Dòng trùng cũ: giữ phiên đầu tiên của mỗi khách
        // (đọc ra trước: MySQL không cho xoá với subquery trên chính bảng đó)
        $keep = DB::table('ai_chat_sessions')->whereNotNull('user_id')->groupBy('user_id')->selectRaw('MIN(id) as id')->pluck('id');
        DB::table('ai_chat_sessions')->whereNotNull('user_id')->whereNotIn('id', $keep)->delete();

        Schema::table('ai_chat_sessions', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('ai_chat_sessions', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
