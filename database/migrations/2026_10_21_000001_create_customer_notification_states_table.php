<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_notification_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source', 16);
            $table->unsignedBigInteger('source_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('hidden_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source', 'source_id'], 'customer_notice_source_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('customer_notification_states');
    }
};
