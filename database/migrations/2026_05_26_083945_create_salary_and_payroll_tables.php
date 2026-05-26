<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['hourly', 'fixed']);
            $table->decimal('rate', 12, 0)->comment('Lương/giờ hoặc lương cố định/tháng');
            $table->date('effective_from');
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->unsignedInteger('total_days')->default(0);
            $table->decimal('base_salary', 12, 0)->default(0);
            $table->decimal('bonus', 12, 0)->default(0);
            $table->decimal('deduction', 12, 0)->default(0);
            $table->decimal('total', 12, 0)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'paid'])->default('draft');
            $table->timestamps();

            $table->unique(['user_id', 'branch_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('salary_configs');
    }
};
