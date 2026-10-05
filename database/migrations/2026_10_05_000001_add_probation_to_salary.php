<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('started_at')->nullable()->after('branch_id')->comment('Ngày bắt đầu làm; thử việc 7 ngày đầu');
        });
        Schema::table('salary_configs', function (Blueprint $table) {
            $table->decimal('probation_rate', 12, 0)->nullable()->after('rate')->comment('Lương thử việc/giờ');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('started_at'));
        Schema::table('salary_configs', fn (Blueprint $table) => $table->dropColumn('probation_rate'));
    }
};
