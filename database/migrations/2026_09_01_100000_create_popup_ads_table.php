<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ตาราง popup_ads: เก็บรูปโฆษณาแบบ popup ที่แสดงตอนเปิดหน้าแรกของเว็บครั้งแรก
     * กำหนดรูปภาพและลิงก์ปลายทาง (ถ้ามี) ได้จากฝั่งแอดมิน
     */
    public function up(): void
    {
        Schema::create('popup_ads', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // ชื่อเรียกภายใน (สำหรับแอดมินดูอ้างอิงเท่านั้น ไม่แสดงหน้าเว็บ)
            $table->string('image_url');
            $table->string('link_url')->nullable(); // ถ้ากำหนดไว้ กดที่รูปแล้วจะลิงก์ไปหน้านี้ (เปิดแท็บใหม่)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_ads');
    }
};
