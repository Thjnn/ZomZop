<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keywords')->comment('Nhiều từ khoá, cách nhau dấu phẩy');
            $table->text('reply');
            $table->string('link_url')->nullable()->comment('Đường dẫn nội bộ, vd. /coupons');
            $table->string('link_label', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_keywords');
    }
};
