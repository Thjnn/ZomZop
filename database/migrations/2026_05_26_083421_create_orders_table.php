<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('kitchen_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['takeaway', 'delivery']);
            $table->enum('status', ['pending', 'confirmed', 'cooking', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 12, 0)->default(0);
            $table->decimal('discount', 12, 0)->default(0);
            $table->decimal('total', 12, 0)->default(0);
            $table->enum('payment_method', ['cash', 'momo', 'vnpay'])->default('cash');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->text('delivery_address')->nullable();
            $table->timestamp('estimated_time')->nullable();
            $table->string('pickup_code', 10)->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
