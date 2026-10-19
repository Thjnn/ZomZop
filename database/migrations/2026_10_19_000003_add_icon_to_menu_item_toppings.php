<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tên file trong public/images/toppings, null = chấm tròn mặc định
    public function up(): void
    {
        Schema::table('menu_item_toppings', function (Blueprint $table) {
            $table->string('icon', 100)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('menu_item_toppings', fn (Blueprint $table) => $table->dropColumn('icon'));
    }
};
