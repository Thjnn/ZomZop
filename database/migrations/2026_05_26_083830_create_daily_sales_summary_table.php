<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_sales_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('day_of_week')->comment('0=Sun, 1=Mon, ..., 6=Sat');
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('total_revenue', 15, 0)->default(0);
            $table->json('order_type_breakdown')->nullable()->comment('{"takeaway": 10, "delivery": 5}');
            $table->timestamps();

            $table->unique(['branch_id', 'menu_item_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_sales_summary');
    }
};
