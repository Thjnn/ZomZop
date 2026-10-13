<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_menu_items', function (Blueprint $table) {
            $table->boolean('is_chef_pick')->default(false)->after('is_bestseller');
        });
    }

    public function down(): void
    {
        Schema::table('branch_menu_items', function (Blueprint $table) {
            $table->dropColumn('is_chef_pick');
        });
    }
};
