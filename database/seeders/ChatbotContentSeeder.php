<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * ChatbotContentSeeder
 *
 * โหลดเนื้อหาแชทบอท (หมวดหลัก > บริการย่อย > ข้อมูลบริการ/FAQ > ธนาคาร Keyword)
 * จากไฟล์ข้อมูลใน database/seeders/data/chatbot/*.php แล้ว insert ลงตาราง chatbot_*
 *
 * รันแยกจาก DatabaseSeeder หลัก เพื่อไม่ให้กระทบข้อมูลทดสอบอื่น ๆ:
 *   php artisan db:seed --class="Database\Seeders\ChatbotContentSeeder"
 *
 * รันซ้ำได้อย่างปลอดภัย (จะล้างข้อมูลแชทบอทเดิมแล้วใส่ใหม่ทั้งหมด)
 */
class ChatbotContentSeeder extends Seeder
{
    public function run(): void
    {
        $dataDir = database_path('seeders/data/chatbot');

        $files = [
            '01_security_system.php',
            '02_insurance.php',
            '03_locker.php',
            '04_technician.php',
            '05_ease_club.php',
            '06_application.php',
        ];

        // ล้างข้อมูลแชทบอทเดิมก่อน (รันซ้ำได้)
        Schema::disableForeignKeyConstraints();
        DB::table('chatbot_keyword_faqs')->truncate();
        DB::table('chatbot_service_faqs')->truncate();
        DB::table('chatbot_services')->truncate();
        DB::table('chatbot_topics')->truncate();
        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        foreach ($files as $file) {
            $path = $dataDir . DIRECTORY_SEPARATOR . $file;
            if (!file_exists($path)) {
                $this->command?->warn("ไม่พบไฟล์ข้อมูล: {$path}");
                continue;
            }

            $topic = require $path;

            $topicId = DB::table('chatbot_topics')->insertGetId([
                'key' => $topic['key'],
                'name_th' => $topic['name_th'],
                'name_en' => $topic['name_en'] ?? null,
                'icon' => $topic['icon'] ?? null,
                'sort_order' => $topic['sort_order'] ?? 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // --- บริการย่อยของหมวดนี้ ---
            foreach (($topic['services'] ?? []) as $serviceSort => $service) {
                $serviceId = DB::table('chatbot_services')->insertGetId([
                    'topic_id' => $topicId,
                    'key' => $service['key'],
                    'name_th' => $service['name_th'],
                    'info_th' => $service['info_th'] ?? null,
                    'info_en' => $service['info_en'] ?? null,
                    'extra_info' => isset($service['extra_info']) ? json_encode($service['extra_info'], JSON_UNESCAPED_UNICODE) : null,
                    'has_technician_contact' => $service['has_technician_contact'] ?? true,
                    'has_purchase_interest' => $service['has_purchase_interest'] ?? true,
                    'has_claim' => $service['has_claim'] ?? false,
                    'purchase_link_route' => $service['purchase_link_route'] ?? null,
                    'sort_order' => $serviceSort,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach (($service['faqs'] ?? []) as $faqSort => $faq) {
                    DB::table('chatbot_service_faqs')->insert([
                        'service_id' => $serviceId,
                        'question_th' => $faq['question_th'],
                        'answer_th' => $faq['answer_th'],
                        'sort_order' => $faqSort,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // --- ธนาคารคำถาม-คำตอบสำหรับจับ keyword ของหมวดนี้ ---
            $keywordSort = 0;
            foreach (($topic['keyword_groups'] ?? []) as $group) {
                foreach (($group['items'] ?? []) as $item) {
                    DB::table('chatbot_keyword_faqs')->insert([
                        'topic_id' => $topicId,
                        'group_label' => $group['group_label'] ?? null,
                        'keywords' => json_encode($item['keywords'] ?? [], JSON_UNESCAPED_UNICODE),
                        'question_label' => $item['question_label'],
                        'answer_th' => $item['answer_th'],
                        'sort_order' => $keywordSort++,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $this->command?->info("โหลดเนื้อหาแชทบอทหมวด: {$topic['name_th']} เรียบร้อย");
        }
    }
}
