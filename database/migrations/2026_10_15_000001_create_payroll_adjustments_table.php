<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Từng khoản thưởng / phạt kèm lý do; payrolls.bonus / deduction là tổng của các khoản này
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->enum('type', ['bonus', 'deduction']);
            $table->decimal('amount', 12, 0);
            $table->string('reason', 255);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Số thưởng/phạt nhập trước đây chưa có lý do → giữ lại thành một khoản để tổng không đổi
        $now = now();
        foreach (DB::table('payrolls')->where('bonus', '>', 0)->orWhere('deduction', '>', 0)->get() as $p) {
            foreach (['bonus' => 'Thưởng', 'deduction' => 'Phạt'] as $type => $label) {
                if ($p->$type > 0) {
                    DB::table('payroll_adjustments')->insert(['payroll_id' => $p->id, 'type' => $type, 'amount' => $p->$type,
                        'reason' => "$label nhập trước khi có ghi lý do", 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
