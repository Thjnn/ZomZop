<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Có kiểm tra cột đã tồn tại: máy nào đã thêm cột bằng tay / bản cũ thì chạy migrate vẫn không lỗi
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'scope')) {
                // food = giảm giá món ăn | ship = giảm phí giao tiêu chuẩn | express = giảm phí giao hỏa tốc
                $table->enum('scope', ['food', 'ship', 'express'])->default('food')->after('type');
            }
            if (!Schema::hasColumn('coupons', 'description')) {
                $table->string('description')->nullable()->after('scope');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            foreach (['scope', 'description'] as $col) {
                if (Schema::hasColumn('coupons', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
