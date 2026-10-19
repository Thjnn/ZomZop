<?php

use App\Models\MenuItemTopping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Topping tạo trước khi có icon: tự gắn icon theo tên (không khớp thì để trống)
    public function up(): void
    {
        MenuItemTopping::whereNull('icon')->get()->each(fn ($t) => $t->update(['icon' => MenuItemTopping::guessIcon($t->name)]));
    }

    public function down(): void
    {
    }
};
