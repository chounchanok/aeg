<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        // สร้างหมวดหมู่บริการ (Service Categories)
        DB::table('service_categories')->insert([
            [
                'id' => 1,
                'title_th' => 'เครื่องปรับอากาศ',
                'title_en' => 'Air Conditioner',
                'image_url' => 'https://example.com/icons/aircon.png',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'title_th' => 'กล้องวงจรปิด',
                'title_en' => 'CCTV',
                'image_url' => 'https://example.com/icons/cctv.png',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'title_th' => 'สัญญาณกันขโมย',
                'title_en' => 'Alarm System',
                'image_url' => 'https://example.com/icons/alarm.png',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}