<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Đơn bán tại quầy (POS) dùng chung bảng orders/order_items với đơn online, chỉ thêm cột còn thiếu.
// type: enum → string (như payment_method) để thêm 'dine_in' mà không phải sửa enum mỗi lần.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->string('channel', 10)->default('online')->after('order_code'); // online | pos
            $table->string('table_number', 10)->nullable()->after('type');
            $table->string('customer_name', 100)->nullable()->after('table_number');
            $table->string('customer_phone', 20)->nullable()->after('customer_name');
            $table->decimal('cash_received', 12, 0)->nullable()->after('payment_status');
            $table->decimal('change_amount', 12, 0)->nullable()->after('cash_received');
            $table->index(['channel', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['channel', 'created_at']);
            $table->dropColumn(['channel', 'table_number', 'customer_name', 'customer_phone', 'cash_received', 'change_amount']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('type', ['takeaway', 'delivery'])->change();
        });
    }
};
