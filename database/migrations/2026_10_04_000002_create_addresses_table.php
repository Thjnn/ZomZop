<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Có máy đã tự tạo bảng bằng SQL tay thì bỏ qua
        if (Schema::hasTable('addresses')) {
            return;
        }

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            // Liên kết với user
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('label', 20)->default('Nhà'); // Nhà | Công ty | Khác
            $table->string('name', 100);                 // Tên người nhận
            $table->string('phone', 20);
            $table->string('address', 255);
            $table->string('note', 255)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
