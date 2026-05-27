<?php

// ============================================================
// MIGRATION: create_menu_item_images_table
// Tạo file này tại: database/migrations/xxxx_create_menu_item_images_table.php
// Chạy: php artisan make:migration create_menu_item_images_table
// Rồi thay nội dung bằng file này
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->string('image');                          // tên file
            $table->string('alt_text')->nullable();           // mô tả ảnh
            $table->unsignedInteger('sort_order')->default(0); // thứ tự hiển thị
            $table->boolean('is_primary')->default(false);    // ảnh đại diện chính
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_images');
    }
};
