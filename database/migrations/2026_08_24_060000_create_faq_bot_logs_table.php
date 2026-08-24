<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ตารางเก็บ log คำถามที่ลูกค้าถามแชทบอท (FAQ Bot)
     * ใช้ดูว่าบอทตอบได้/ไม่ได้ และช่วยให้แอดมินรู้ว่าควรเพิ่ม FAQ เรื่องอะไร
     */
    public function up(): void
    {
        Schema::create('faq_bot_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // null = ผู้ใช้ที่ยังไม่ล็อกอิน
            $table->string('question', 500);
            $table->foreignId('faq_id')->nullable()->constrained('faqs')->nullOnDelete();
            $table->boolean('is_matched')->default(false);
            $table->float('match_score')->nullable();
            $table->timestamps();

            $table->index('is_matched');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_bot_logs');
    }
};
