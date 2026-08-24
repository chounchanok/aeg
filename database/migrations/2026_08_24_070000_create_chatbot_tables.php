<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ตารางสำหรับแชทบอทเมนูปุ่มกด (Chat Bot) ของ AEG
     * โครงสร้าง: หมวดหลัก (topics) > บริการย่อย (services) > คำถามที่พบบ่อยของบริการนั้น (service_faqs)
     * รวมถึงธนาคารคำถาม-คำตอบสำหรับจับคำ keyword จากข้อความอิสระ (keyword_faqs)
     * และตารางเก็บคะแนนความพึงพอใจ / ลูกค้าที่สนใจซื้อบริการหรือแจ้งเคลม
     */
    public function up(): void
    {
        Schema::create('chatbot_topics', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // เช่น security-system, insurance
            $table->string('name_th');
            $table->string('name_en')->nullable();
            $table->string('icon')->nullable(); // font-awesome class
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chatbot_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('chatbot_topics')->cascadeOnDelete();
            $table->string('key');
            $table->string('name_th');
            $table->longText('info_th')->nullable();
            $table->longText('info_en')->nullable();
            $table->json('extra_info')->nullable(); // หัวข้อย่อยเพิ่มเติม เช่น การเคลม, ช่องทางชำระเงิน
            $table->boolean('has_technician_contact')->default(true); // แสดงปุ่ม "ติดต่อช่าง"
            $table->boolean('has_purchase_interest')->default(true); // แสดงปุ่ม "สนใจซื้อบริการ"
            $table->boolean('has_claim')->default(false); // แสดงปุ่ม "แจ้งเคลม" (ประกันภัย)
            $table->string('purchase_link_route')->nullable(); // ชื่อ route ที่จะพาไปหน้าซื้อ/จอง (ถ้ามี)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chatbot_service_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('chatbot_services')->cascadeOnDelete();
            $table->string('question_th');
            $table->longText('answer_th');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chatbot_keyword_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('chatbot_topics')->cascadeOnDelete();
            $table->string('group_label')->nullable(); // เช่น "แบตเตอรี่/ไฟ", "CCTV", "Alarm"
            $table->json('keywords'); // รายการคำ/วลีที่ใช้จับคู่
            $table->string('question_label'); // คำถามตัวแทนที่แสดงผล
            $table->longText('answer_th');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chatbot_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('chatbot_purchase_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type')->default('purchase'); // 'purchase' หรือ 'claim'
            $table->string('topic_key')->nullable();
            $table->string('service_name')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new'); // new, contacted, closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_purchase_leads');
        Schema::dropIfExists('chatbot_ratings');
        Schema::dropIfExists('chatbot_keyword_faqs');
        Schema::dropIfExists('chatbot_service_faqs');
        Schema::dropIfExists('chatbot_services');
        Schema::dropIfExists('chatbot_topics');
    }
};
