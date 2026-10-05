<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('late_reason', 255)->nullable()->after('note')->comment('Lý do đi trễ (> 5 phút)');
            $table->string('early_reason', 255)->nullable()->after('late_reason')->comment('Lý do ra ca sớm (> 10 phút)');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', fn (Blueprint $table) => $table->dropColumn(['late_reason', 'early_reason']));
    }
};
