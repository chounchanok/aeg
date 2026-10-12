<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * 🌟 FAQ แชทบอทสองภาษา: เพิ่ม question_en / answer_en ใน chatbot_service_faqs
 *    แล้วใส่ภาษาอังกฤษจาก database/data/chatbot_service_faqs_en.php (ไฟล์ chatbot_service_faqs.xlsx)
 *
 * จับคู่แถว: 1) question_th ตรงกับในไฟล์ (ไม่สนช่องว่าง) 2) ไม่ตรง → ใช้ลำดับ id (แถวที่ k ↔ ข้อที่ k)
 * ไม่เขียนทับภาษาไทย และไม่ทับ question_en/answer_en ที่มีคนกรอกไว้แล้ว
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_service_faqs', function (Blueprint $table) {
            if (!Schema::hasColumn('chatbot_service_faqs', 'question_en')) {
                $table->string('question_en')->nullable()->after('question_th');
            }
            if (!Schema::hasColumn('chatbot_service_faqs', 'answer_en')) {
                $table->longText('answer_en')->nullable()->after('answer_th');
            }
        });

        $data = require database_path('data/chatbot_service_faqs_en.php');
        $norm = fn ($s) => preg_replace('/\s+/u', '', (string) $s);
        $byThai = [];
        foreach ($data as $item) {
            $byThai[$norm($item['question_th'])] = $item;
        }

        $rows = DB::table('chatbot_service_faqs')->orderBy('id')->get(['id', 'question_th', 'question_en', 'answer_en']);
        if ($rows->count() !== count($data)) {
            Log::warning('chatbot_service_faqs: จำนวนแถว (' . $rows->count() . ') ไม่เท่ากับไฟล์ภาษาอังกฤษ (' . count($data) . ') — ใช้เฉพาะแถวที่ question_th ตรงกัน');
        }

        foreach ($rows->values() as $k => $row) {
            $item = $byThai[$norm($row->question_th)]
                ?? ($rows->count() === count($data) ? ($data[$k] ?? null) : null);
            if (!$item) {
                continue;
            }

            $update = [];
            if (blank($row->question_en)) $update['question_en'] = $item['question_en'];
            if (blank($row->answer_en)) $update['answer_en'] = $item['answer_en'];
            if ($update) {
                DB::table('chatbot_service_faqs')->where('id', $row->id)->update($update);
            }
        }
    }

    public function down(): void
    {
        Schema::table('chatbot_service_faqs', function (Blueprint $table) {
            foreach (['question_en', 'answer_en'] as $col) {
                if (Schema::hasColumn('chatbot_service_faqs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
