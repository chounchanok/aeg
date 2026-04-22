<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ตารางคำถามที่พบบ่อย (FAQ)
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable(); 
            $table->string('question_th'); // 🌟 คำถามไทย
            $table->string('question_en')->nullable(); // 🌟 คำถามอังกฤษ
            $table->text('answer_th'); // 🌟 คำตอบไทย
            $table->text('answer_en')->nullable(); // 🌟 คำตอบอังกฤษ
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. ตารางประวัติการแชท (ผูกติดกับใบแจ้งซ่อม)
        Schema::create('service_request_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ID ของคนส่ง (ลูกค้า หรือ แอดมิน)
            $table->enum('sender_type', ['customer', 'admin']); // ระบุให้ชัดว่าเป็นข้อความจากใคร
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 3. ตารางประวัติการติดตามสถานะ (Tracking Log)
        Schema::create('service_request_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
            $table->string('status'); // เช่น 'รับเรื่องแจ้งซ่อมแล้ว', 'ช่างกำลังเดินทาง'
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_tracking');
        Schema::dropIfExists('service_request_chats');
        Schema::dropIfExists('faqs');
    }
};