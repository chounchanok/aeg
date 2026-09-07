<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ขยายฟอร์ม "ติดต่อฝ่ายขาย" (POST /user/contact-admin) ให้เก็บข้อมูลเพิ่มตามที่ฝั่ง mobile ต้องการ:
 * - topic          : หัวข้อที่ติดต่อ (controller บันทึกอยู่แล้วแต่ยังไม่เคยมี migration สร้างคอลัมน์นี้)
 * - product_name   : ชื่อสินค้าที่สนใจ (เผื่อกรอกเองโดยไม่ได้เลือกจากระบบ / กันกรณี product ถูกลบ)
 * - tax_id, branch : เลขประจำตัวผู้เสียภาษี + สาขา สำหรับลูกค้าประเภทธุรกิจ (ปิด gap จาก QA ข้อ 6)
 *
 * ใช้ Schema::hasColumn ครอบทุกคอลัมน์ เพื่อให้รันซ้ำ / รันบนฐานข้อมูลที่เคยเพิ่มคอลัมน์เองไว้แล้วได้โดยไม่ error
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_admin_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_admin_requests', 'topic')) {
                $table->string('topic')->nullable()->after('request_number')->comment('หัวข้อที่ติดต่อ เช่น ประกัน, ตู้เซฟ, สินค้า');
            }

            if (!Schema::hasColumn('contact_admin_requests', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_id')->comment('ชื่อสินค้าที่สนใจ (กรอกเองได้ ไม่ต้องผูกกับ product_id)');
            }

            if (!Schema::hasColumn('contact_admin_requests', 'tax_id')) {
                $table->string('tax_id', 20)->nullable()->after('company_name')->comment('เลขประจำตัวผู้เสียภาษี (กรณีธุรกิจ)');
            }

            if (!Schema::hasColumn('contact_admin_requests', 'branch')) {
                $table->string('branch', 100)->nullable()->after('tax_id')->comment('สาขา เช่น สำนักงานใหญ่ / 00001 (กรณีธุรกิจ)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_admin_requests', function (Blueprint $table) {
            $columns = array_filter(
                ['topic', 'product_name', 'tax_id', 'branch'],
                fn ($column) => Schema::hasColumn('contact_admin_requests', $column)
            );

            if (!empty($columns)) {
                $table->dropColumn(array_values($columns));
            }
        });
    }
};
