<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->decimal('price', 12, 0)->nullable()->comment('Override giá theo chi nhánh, null = dùng base_price');
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'menu_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_menu_items');
    }
};
