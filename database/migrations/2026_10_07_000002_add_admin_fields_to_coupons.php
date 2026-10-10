<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('max_discount', 12, 0)->nullable()->after('value')->comment('Giảm tối đa (chỉ loại percent), null = không giới hạn');
            $table->boolean('is_public')->default(true)->after('is_active')->comment('Hiện ở trang /coupons; false = chỉ gửi riêng');
        });

        // unique(coupon_id, user_id) mâu thuẫn với max_uses_per_user > 1.
        // Thêm index thường TRƯỚC: MySQL cần một index cho khoá ngoại coupon_id khi bỏ unique.
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->index(['coupon_id', 'user_id'], 'coupon_usages_coupon_user_index');
        });
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropUnique(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->unique(['coupon_id', 'user_id']);
        });
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropIndex('coupon_usages_coupon_user_index');
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['max_discount', 'is_public']);
        });
    }
};
