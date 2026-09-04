<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มข้อมูลที่ QA ระบุว่า "จำเป็นต่อการตัดสินใจซื้อ" บนหน้ารายละเอียดสินค้า
 * (เมลข้อ 3: ระบบซื้ออุปกรณ์ผ่านแอปพลิเคชัน) — ก่อนหน้านี้ตาราง products มีแค่
 * name/description/type/price/point_earn/is_active/is_contact_only เท่านั้น
 *
 * หมายเหตุ: ใช้คอลัมน์ is_contact_only ที่มีอยู่แล้วเป็นตัวกำหนดว่าสินค้ารายการนี้
 * เข้ากลุ่ม "ต้องสำรวจหน้างาน/ขอใบเสนอราคา" (3.2) แทนที่จะซื้อผ่านตะกร้าได้ทันที (3.1)
 * ไม่ได้เพิ่มคอลัมน์ใหม่ซ้ำซ้อน
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_quantity')->nullable()->after('price')
                ->comment('จำนวนคงเหลือ — null = ไม่จำกัด/ไม่ใช่สินค้าที่นับสต็อก (เช่น บริการ)');
            $table->string('brand')->nullable()->after('name_en');
            $table->string('model')->nullable()->after('brand');
            $table->integer('warranty_months')->nullable()->comment('ระยะเวลารับประกัน (เดือน)');
            $table->text('return_policy_th')->nullable()->comment('เงื่อนไขการคืนหรือเปลี่ยนสินค้า');
            $table->decimal('shipping_fee', 10, 2)->nullable()->comment('ค่าจัดส่ง (แยกจากราคาสินค้า)');
            $table->decimal('install_fee', 10, 2)->nullable()->comment('ค่าติดตั้ง (แยกจากราคาสินค้า)');
            $table->text('compatible_with')->nullable()->comment('อุปกรณ์/รุ่นที่ใช้งานร่วมกันได้');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'stock_quantity', 'brand', 'model', 'warranty_months',
                'return_policy_th', 'shipping_fee', 'install_fee', 'compatible_with',
            ]);
        });
    }
};
