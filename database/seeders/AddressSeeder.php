<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy khách hàng mẫu
        $customer = User::where('email', 'customer@zomzop.com')->first();

        if (!$customer) {
            $this->command->warn('Không tìm thấy customer@zomzop.com, bỏ qua AddressSeeder.');
            return;
        }

        // 2. Bổ sung ngày sinh, giới tính cho trang hồ sơ
        DB::table('users')->where('id', $customer->id)->update([
            'birthday' => '2000-05-15',
            'gender'   => 'male',
        ]);

        // 3. Đã có địa chỉ thì không chèn thêm (tránh trùng khi chạy lại)
        if (DB::table('addresses')->where('user_id', $customer->id)->exists()) {
            return;
        }

        $addresses = [
            [
                'label'      => 'Nhà',
                'name'       => $customer->name,
                'phone'      => '0901234567',
                'address'    => '123 Nguyễn Văn Linh, Phường Tân Phong, Quận 7, TP. Hồ Chí Minh',
                'note'       => 'Gọi trước khi giao',
                'is_default' => true,
            ],
            [
                'label'      => 'Công ty',
                'name'       => $customer->name,
                'phone'      => '0901234567',
                'address'    => '45 Lê Lợi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
                'note'       => 'Giao giờ hành chính, gửi lễ tân tầng 1',
                'is_default' => false,
            ],
        ];

        foreach ($addresses as $address) {
            DB::table('addresses')->insert($address + [
                'user_id'    => $customer->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
