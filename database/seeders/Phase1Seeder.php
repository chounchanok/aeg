<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Phase1Seeder extends Seeder
{
    public function run(): void
    {
        // 1. ข้อมูล Banners (หน้า Main และ EASE CLUB)
        DB::table('banners')->insert([
            [
                'title' => 'โปรโมชั่นติดกล้องวงจรปิด ลด 50%',
                'image_url' => 'https://example.com/banners/main_promo1.jpg',
                'location' => 'main',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'บริการดูแลระบบรายเดือน เริ่มต้น 990.-',
                'image_url' => 'https://example.com/banners/main_promo2.jpg',
                'location' => 'main',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'EASE CLUB แลกพอยท์สุดคุ้มเดือนนี้',
                'image_url' => 'https://example.com/banners/ease_club_promo.jpg',
                'location' => 'ease_club',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        // 2. ข้อมูลหมวดหมู่ของรางวัล (Reward Categories)
        DB::table('reward_categories')->insert([
            ['id' => 1, 'name' => 'ส่วนลดและเวาเชอร์', 'icon_url' => 'https://example.com/icons/voucher.png', 'created_at' => Carbon::now()],
            ['id' => 2, 'name' => 'แกดเจ็ตและไอที', 'icon_url' => 'https://example.com/icons/gadget.png', 'created_at' => Carbon::now()],
            ['id' => 3, 'name' => 'ไลฟ์สไตล์', 'icon_url' => 'https://example.com/icons/lifestyle.png', 'created_at' => Carbon::now()],
        ]);

        // 3. ข้อมูลของรางวัล (Rewards)
        DB::table('rewards')->insert([
            // ของรางวัลทั่วไป (แลกได้ทุก Tier)
            [
                'category_id' => 1,
                'title' => 'ส่วนลด 500 บาท สำหรับค่าบริการรายเดือน',
                'description' => 'ใช้เป็นส่วนลดค่าบริการดูแลระบบรายเดือนของ AEG',
                'points_required' => 500,
                'image_url' => 'https://example.com/rewards/discount500.jpg',
                'stock_quantity' => 100,
                'minimum_tier_required' => null,
                'created_at' => Carbon::now(),
            ],
            [
                'category_id' => 1,
                'title' => 'Starbucks e-Coupon 200 บาท',
                'description' => 'บัตรกำนัลสตาร์บัคส์มูลค่า 200 บาท',
                'points_required' => 200,
                'image_url' => 'https://example.com/rewards/starbucks.jpg',
                'stock_quantity' => 50,
                'minimum_tier_required' => null,
                'created_at' => Carbon::now(),
            ],
            // ของรางวัลพิเศษ (เฉพาะ Advance ขึ้นไป)
            [
                'category_id' => 2,
                'title' => 'ACONATIC สมาร์ททีวี 43 นิ้ว',
                'description' => 'ACONATIC สมาร์ททีวี 43 นิ้ว รุ่น 43HS701AN ปี 2024 (สิทธิพิเศษเฉพาะสมาชิกระดับ Advance ขึ้นไป)',
                'points_required' => 4000,
                'image_url' => 'https://example.com/rewards/tv43.jpg',
                'stock_quantity' => 5,
                'minimum_tier_required' => 'Advance',
                'created_at' => Carbon::now(),
            ],
            [
                'category_id' => 2,
                'title' => 'กล้องวงจรปิดไร้สาย AEG Smart IP Camera',
                'description' => 'กล้องวงจรปิดหมุนได้ 360 องศา (สิทธิพิเศษเฉพาะสมาชิกระดับ Advance ขึ้นไป)',
                'points_required' => 2000,
                'image_url' => 'https://example.com/rewards/ipcamera.jpg',
                'stock_quantity' => 10,
                'minimum_tier_required' => 'Advance',
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}