<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบ "ขอใบเสนอราคา" (RFQ) สำหรับสินค้ากลุ่มที่ต้องสำรวจหน้างานก่อน (is_contact_only = true)
 * ตามเมลข้อ 3.2 — แยกออกจาก contact_admin_requests (แบบฟอร์มติดต่อทั่วไป) เพราะต้องมีข้อมูล
 * เฉพาะทาง เช่น รูปหน้างาน วันนัดสำรวจ และสินค้าที่สนใจ พร้อมหมายเลขคำขอให้ติดตามสถานะได้
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique()->comment('หมายเลขคำขอ เช่น RFQ-20260904-0001 สำหรับให้ลูกค้าติดตามสถานะ');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->integer('quantity')->default(1);

            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();

            $table->text('site_address')->comment('สถานที่ที่ต้องการให้สำรวจหน้างาน');
            $table->string('site_image_url')->nullable()->comment('รูปภาพหน้างานที่แนบมา');
            $table->date('preferred_survey_date')->nullable()->comment('วันที่สะดวกให้เข้าสำรวจ');
            $table->text('detail')->nullable()->comment('รายละเอียด/ความต้องการเพิ่มเติมจากลูกค้า');

            $table->enum('status', ['pending', 'contacted', 'quoted', 'approved', 'rejected', 'cancelled'])
                ->default('pending');
            $table->decimal('quoted_price', 12, 2)->nullable()->comment('ราคาที่ฝ่ายขายเสนอกลับไป (กรอกทีหลังจากหลังบ้าน)');
            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
