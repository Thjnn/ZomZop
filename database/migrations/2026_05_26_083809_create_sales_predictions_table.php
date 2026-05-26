<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->date('predicted_date');
            $table->unsignedInteger('predicted_qty');
            $table->unsignedInteger('actual_qty')->nullable();
            $table->decimal('confidence', 5, 2)->nullable()->comment('Phần trăm độ tin cậy');
            $table->timestamps();

            $table->unique(['branch_id', 'menu_item_id', 'predicted_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_predictions');
    }
};
