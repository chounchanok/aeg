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
                'title_th' => 'โปรโมชั่นติดกล้องวงจรปิด ลด 50%',
                'title_en' => '50% Off CCTV Installation Promo',
                'image_url' => 'https://example.com/banners/main_promo1.jpg',
                'location' => 'main',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title_th' => 'บริการดูแลระบบรายเดือน เริ่มต้น 990.-',
                'title_en' => 'Monthly Maintenance Service Starting at 990.-',
                'image_url' => 'https://example.com/banners/main_promo2.jpg',
                'location' => 'main',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title_th' => 'EASE CLUB แลกพอยท์สุดคุ้มเดือนนี้',
                'title_en' => 'EASE CLUB: Best Point Redemptions this Month',
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
                'title_th' => 'ส่วนลด 500 บาท สำหรับค่าบริการรายเดือน',
                'title_en' => '500 THB Discount for Monthly Service',
                'description_th' => 'ใช้เป็นส่วนลดค่าบริการดูแลระบบรายเดือนของ AEG',
                'description_en' => 'Can be used as a discount for AEG monthly maintenance service.',
                'points_required' => 500,
                'image_url' => 'https://example.com/rewards/discount500.jpg',
                'stock_quantity' => 100,
                'minimum_tier_required' => null,
                'created_at' => Carbon::now(),
            ],
            [
                'category_id' => 1,
                'title_th' => 'Starbucks e-Coupon 200 บาท',
                'title_en' => 'Starbucks e-Coupon 200 THB',
                'description_th' => 'บัตรกำนัลสตาร์บัคส์มูลค่า 200 บาท',
                'description_en' => 'Starbucks gift voucher worth 200 THB',
                'points_required' => 200,
                'image_url' => 'https://example.com/rewards/starbucks.jpg',
                'stock_quantity' => 50,
                'minimum_tier_required' => null,
                'created_at' => Carbon::now(),
            ],
            // ของรางวัลพิเศษ (เฉพาะ Advance ขึ้นไป)
            [
                'category_id' => 2,
                'title_th' => 'ACONATIC สมาร์ททีวี 43 นิ้ว',
                'title_en' => 'ACONATIC Smart TV 43"',
                'description_th' => 'ACONATIC สมาร์ททีวี 43 นิ้ว รุ่น 43HS701AN ปี 2024 (สิทธิพิเศษเฉพาะสมาชิกระดับ Advance ขึ้นไป)',
                'description_en' => 'ACONATIC Smart TV 43" Model 43HS701AN 2024 (Exclusive for Advance tier and above)',
                'points_required' => 4000,
                'image_url' => 'https://example.com/rewards/tv43.jpg',
                'stock_quantity' => 5,
                'minimum_tier_required' => 'Advance',
                'created_at' => Carbon::now(),
            ],
            [
                'category_id' => 2,
                'title_th' => 'กล้องวงจรปิดไร้สาย AEG Smart IP Camera',
                'title_en' => 'AEG Smart IP Camera (Wireless)',
                'description_th' => 'กล้องวงจรปิดหมุนได้ 360 องศา (สิทธิพิเศษเฉพาะสมาชิกระดับ Advance ขึ้นไป)',
                'description_en' => '360-degree rotating CCTV camera (Exclusive for Advance tier and above)',
                'points_required' => 2000,
                'image_url' => 'https://example.com/rewards/ipcamera.jpg',
                'stock_quantity' => 10,
                'minimum_tier_required' => 'Advance',
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}