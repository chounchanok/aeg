<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * แก้ปัญหา: คอลัมน์ favorites.product_id ถูกใช้เก็บทั้ง id ของ "product" และ "reward"
 * (แยกด้วยคอลัมน์ item_type) แต่ตอนสร้างตาราง (2026_04_07_055600_create_phase3_profile_tables.php)
 * ใส่ foreign key ผูกไว้กับตาราง products เพียงอย่างเดียว
 *
 * ผลคือ: กดหัวใจ (favorite) รายการ item_type=reward ไม่ได้เลย ถ้า id ของ reward
 * ไม่ตรงกับ id ที่มีอยู่จริงในตาราง products -> เจอ error
 * "Cannot add or update a child row: a foreign key constraint fails (favorites_product_id_foreign)"
 *
 * จึงต้องถอด foreign key นี้ออก แล้วให้โค้ดฝั่ง Controller เป็นผู้ตรวจสอบความถูกต้อง
 * ของ id เอง (เช็คตาราง products หรือ rewards ตาม item_type ก่อน insert แทน)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign('favorites_product_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
