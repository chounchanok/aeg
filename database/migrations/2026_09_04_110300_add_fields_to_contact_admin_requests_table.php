<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ขยายฟอร์ม "ติดต่อแอดมิน/ฝ่ายขาย" ตามที่เมล QA ข้อ 6 ระบุไว้:
 * - แยกจังหวัด/เขต-อำเภอ/แขวง-ตำบล/รหัสไปรษณีย์ (เดิมรวมอยู่ใน address_full ช่องเดียว)
 * - แนบสินค้า+จำนวนที่ลูกค้าสนใจได้ (ถ้าเข้ามาจากหน้าสินค้า)
 * - แนบรูปภาพ/รายละเอียดสถานที่ติดตั้ง
 * - ออกหมายเลขคำขอ + สถานะ ให้ติดตามได้ (เดิมไม่มีเลยแม้แต่คอลัมน์ status)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_admin_requests', function (Blueprint $table) {
            $table->string('request_number')->unique()->nullable()->after('id');

            // แยกที่อยู่เป็นฟิลด์ย่อย — คงคอลัมน์ address_full เดิมไว้เป็น nullable เพื่อ backward-compat
            // กับข้อมูลเก่าที่เคยบันทึกไว้ก่อนหน้านี้ (ไม่ลบทิ้ง กันข้อมูลเก่าเสียหาย)
            $table->string('province')->nullable()->after('address_full');
            $table->string('district')->nullable()->after('province');
            $table->string('subdistrict')->nullable()->after('district');
            $table->string('zipcode', 10)->nullable()->after('subdistrict');

            $table->foreignId('product_id')->nullable()->after('zipcode')->constrained('products')->onDelete('set null');
            $table->integer('quantity')->nullable()->after('product_id');

            $table->text('detail')->nullable()->comment('รายละเอียดเพิ่มเติม/ความต้องการของลูกค้า');
            $table->string('image_url')->nullable()->comment('รูปภาพสถานที่ติดตั้งที่แนบมา');

            $table->enum('status', ['pending', 'contacted', 'closed'])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('contact_admin_requests', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'request_number', 'province', 'district', 'subdistrict', 'zipcode',
                'product_id', 'quantity', 'detail', 'image_url', 'status',
            ]);
        });
    }
};
