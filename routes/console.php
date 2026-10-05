<?php

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tạo tài khoản quản lý cho một chi nhánh (chưa có trang Admin để làm việc này).
// VD: php artisan zomzop:manager "Bến Tre" ql.bentre@zomzop.com --name="Trần Văn A"
Artisan::command('zomzop:manager {branch : ID hoặc một phần tên chi nhánh} {email} {--name= : Họ tên (mặc định "Quản lý <chi nhánh>")} {--password= : Bỏ trống để tạo ngẫu nhiên}', function () {
    $key = trim($this->argument('branch'));
    $branches = ctype_digit($key)
        ? Branch::whereKey((int) $key)->get()
        : Branch::all()->filter(fn ($b) => mb_stripos($b->name, $key) !== false)->values();

    if ($branches->count() !== 1) {
        $this->error($branches->isEmpty()
            ? "Không tìm thấy chi nhánh \"{$key}\"."
            : "\"{$key}\" khớp nhiều chi nhánh: " . $branches->pluck('name')->implode(', ') . '. Ghi rõ hơn hoặc dùng ID.');
        $this->line('Các chi nhánh: ' . Branch::orderBy('id')->get()->map(fn ($b) => "{$b->id}. {$b->name}")->implode(' | '));

        return 1;
    }
    $branch = $branches->first();

    $password  = $this->option('password') ?: Str::password(12, symbols: false);
    $validator = Validator::make(['email' => $this->argument('email'), 'password' => $password], [
        'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
        'password' => ['min:8'],
    ], [
        'email.email'  => 'Email không hợp lệ.',
        'email.unique' => 'Email này đã có người sử dụng.',
        'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
    ]);
    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $message) {
            $this->error($message);
        }

        return 1;
    }

    $user = User::create([
        'name'      => $this->option('name') ?: "Quản lý {$branch->name}",
        'email'     => $this->argument('email'),
        'password'  => $password,   // cast 'hashed' trong User tự mã hoá
        'role'      => 'manager',
        'branch_id' => $branch->id,
        'is_active' => true,
    ]);

    $this->info("Đã tạo quản lý {$user->name} cho chi nhánh {$branch->name}.");
    $this->line("Email: {$user->email}");
    if (!$this->option('password')) {
        $this->line("Mật khẩu: {$password}  (gửi cho người quản lý và nhắc đổi sau khi đăng nhập)");
    }

    return 0;
})->purpose('Tạo tài khoản quản lý cho một chi nhánh');
