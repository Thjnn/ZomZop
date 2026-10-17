<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tồn kho thật: null = không giới hạn (mặc định), 0 = hết hàng, đặt hàng thì trừ
    public function up(): void
    {
        Schema::table('branch_menu_items', function (Blueprint $table) {
            $table->unsignedInteger('stock_qty')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('branch_menu_items', function (Blueprint $table) {
            $table->unsignedInteger('stock_qty')->default(0)->nullable(false)->change();
        });
    }
};
