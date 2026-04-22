<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Phase5Seeder extends Seeder
{
    public function run(): void
    {
        // 1. สร้างข้อมูล FAQ
        DB::table('faqs')->insert([
            [
                'category' => 'บริการ', 
                'question_th' => 'ต้องเตรียมตัวอย่างไรก่อนช่างเข้าติดตั้ง?', 
                'question_en' => 'How should I prepare before the technician arrives?', 
                'answer_th' => 'กรุณาเก็บสิ่งของมีค่า และเตรียมพื้นที่บริเวณที่จะทำการติดตั้งให้โล่งครับ', 
                'answer_en' => 'Please secure your valuables and clear the area where the installation will take place.', 
                'sort_order' => 1, 
                'created_at' => Carbon::now()
            ],
            [
                'category' => 'ประกัน', 
                'question_th' => 'ระยะเวลารับประกันสินค้ากี่ปี?', 
                'question_en' => 'How long is the warranty period?', 
                'answer_th' => 'สินค้าของ AEG มีระยะเวลารับประกันมาตรฐาน 1 ปีนับจากวันที่ติดตั้งครับ', 
                'answer_en' => 'AEG products come with a standard 1-year warranty starting from the installation date.', 
                'sort_order' => 2, 
                'created_at' => Carbon::now()
            ],
        ]);

        // 2. ดึงใบแจ้งซ่อมใบแรกมาจำลองระบบ Chat และ Tracking
        $request = DB::table('service_requests')->first();
        $admin = DB::table('users')->where('role', 'super_admin')->first(); // แอดมิน
        
        if ($request && $admin) {
            // จำลองประวัติ Chat
            DB::table('service_request_chats')->insert([
                ['service_request_id' => $request->id, 'user_id' => $request->customer_id, 'sender_type' => 'customer', 'message' => 'สวัสดีครับ อยากทราบว่าช่างจะเข้ามาประมาณกี่โมงครับ', 'created_at' => Carbon::now()->subHours(2)],
                ['service_request_id' => $request->id, 'user_id' => $admin->id, 'sender_type' => 'admin', 'message' => 'สวัสดีค่ะ แอดมินตรวจสอบคิวช่างให้ เบื้องต้นช่างจะเข้าถึงหน้างานประมาณ 10:30 น. ค่ะ', 'created_at' => Carbon::now()->subHours(1)->subMinutes(50)],
            ]);

            // จำลองประวัติ Tracking
            DB::table('service_request_tracking')->insert([
                ['service_request_id' => $request->id, 'status' => 'รับเรื่องแจ้งซ่อม', 'description' => 'ระบบได้รับข้อมูลการแจ้งซ่อมของท่านแล้ว', 'created_at' => Carbon::now()->subDays(1)],
                ['service_request_id' => $request->id, 'status' => 'จัดสรรช่างเรียบร้อย', 'description' => 'แอดมินจ่ายงานให้ช่างสมชาย สำหรับเข้าบริการตามวันเวลาที่กำหนด', 'created_at' => Carbon::now()->subHours(5)],
            ]);
        }
    }
}