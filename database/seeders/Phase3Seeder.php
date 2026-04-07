<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Phase3Seeder extends Seeder
{
    public function run(): void
    {
        // ดึงข้อมูล User 'customer01' 
        $customer = DB::table('users')->where('username', 'customer01')->first();

        if ($customer) {
            // 1. อัปเดตรูปโปรไฟล์จำลองให้ลูกค้า
            DB::table('customer_profiles')->where('user_id', $customer->id)->update([
                'profile_image_url' => 'https://ui-avatars.com/api/?name=Jai+Dee&background=0D8ABC&color=fff',
                'updated_at' => Carbon::now(),
            ]);

            // 2. สร้างรายการโปรดจำลอง (Favorites) สมมติว่าชอบสินค้า ID 1 และ 3
            // ใช้ insertOrIgnore ป้องกัน Error กรณีรัน Seeder ซ้ำ
            DB::table('favorites')->insertOrIgnore([
                [
                    'user_id' => $customer->id,
                    'product_id' => 1, 
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'user_id' => $customer->id,
                    'product_id' => 3, 
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ]);

            // 3. สร้าง Device Token จำลอง
            DB::table('device_tokens')->insertOrIgnore([
                'user_id' => $customer->id,
                'token' => 'fcm_test_token_ios_998877665544332211',
                'device_type' => 'ios',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 4. สร้างการแจ้งเตือนจำลอง (Notifications)
            DB::table('notifications')->insert([
                [
                    'user_id' => $customer->id,
                    'title' => 'ยินดีต้อนรับสู่ AEG Application',
                    'body' => 'ขอบคุณที่ไว้วางใจใช้บริการของเรา ท่านสามารถจัดการบริการต่างๆ ได้ผ่านแอปพลิเคชัน',
                    'type' => 'system',
                    'is_read' => true,
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(4),
                ],
                [
                    'user_id' => $customer->id,
                    'title' => 'โปรโมชั่นพิเศษประจำเดือน!',
                    'body' => 'ลดทันที 15% สำหรับบริการล้างแอร์และกล้องวงจรปิด กดดูรายละเอียดเลย',
                    'type' => 'promotion',
                    'is_read' => false,
                    'created_at' => Carbon::now()->subHours(2),
                    'updated_at' => Carbon::now()->subHours(2),
                ],
                [
                    'user_id' => $customer->id,
                    'title' => 'แจ้งเตือนวันครบกำหนดรับบริการ',
                    'body' => 'แพ็กเกจดูแลรักษากล้องวงจรปิดของท่าน ใกล้ถึงกำหนดเข้ารับบริการครั้งถัดไปแล้ว',
                    'type' => 'service',
                    'is_read' => false,
                    'created_at' => Carbon::now()->subMinutes(15),
                    'updated_at' => Carbon::now()->subMinutes(15),
                ]
            ]);
        }
    }
}