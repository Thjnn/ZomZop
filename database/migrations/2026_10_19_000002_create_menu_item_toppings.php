<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Topping riêng của từng món, giá chung toàn chuỗi
        Schema::create('menu_item_toppings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->string('name', 80);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Có dòng = topping đang hết ở chi nhánh đó (quản lý tắt)
        Schema::create('branch_unavailable_toppings', function (Blueprint $table) {
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('menu_item_topping_id')->constrained('menu_item_toppings')->cascadeOnDelete();
            $table->primary(['branch_id', 'menu_item_topping_id']);
        });

        // Bản chụp topping lúc đặt: [{id, name, price}]; price_snapshot đã gồm tiền topping
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('toppings')->nullable()->after('price_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('toppings'));
        Schema::dropIfExists('branch_unavailable_toppings');
        Schema::dropIfExists('menu_item_toppings');
    }
};
