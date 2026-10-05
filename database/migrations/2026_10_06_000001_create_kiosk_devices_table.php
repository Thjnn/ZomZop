<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosk_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('token_hash', 64)->unique()->comment('SHA-256 của token; token gốc chỉ hiện 1 lần');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('face_confidence')->comment('Ảnh bằng chứng lúc chấm vào; ảnh ra cùng thư mục, đuôi -out');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', fn (Blueprint $table) => $table->dropColumn('photo_path'));
        Schema::dropIfExists('kiosk_devices');
    }
};
