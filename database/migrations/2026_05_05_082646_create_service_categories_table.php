<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. สร้างตารางหมวดหมู่บริการ
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title_th'); // ชื่อหมวดหมู่ (ไทย)
            $table->string('title_en')->nullable(); // ชื่อหมวดหมู่ (อังกฤษ)
            $table->string('image_url')->nullable(); // รูปภาพไอคอนหมวดหมู่
            $table->integer('sort_order')->default(0); // ลำดับการแสดงผล
            $table->boolean('is_active')->default(true); // สถานะเปิด/ปิด
            $table->timestamps();
        });

        // 2. เพิ่มคอลัมน์ในตาราง products เพื่อให้รู้ว่าสินค้านี้อยู่หมวดไหน
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('service_category_id')->nullable()->constrained('service_categories')->onDelete('set null')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['service_category_id']);
            $table->dropColumn('service_category_id');
        });
        Schema::dropIfExists('service_categories');
    }
};