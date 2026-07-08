<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // สเตปที่ 1: จัดการลบของเก่า และสร้างคอลัมน์ใหม่ (ถ้ายังไม่มี)
        Schema::table('smart_lockers', function (Blueprint $table) {
            // ลบ enum ตัวเก่าทิ้ง
            if (Schema::hasColumn('smart_lockers', 'category')) {
                $table->dropColumn('category');
            }

            // เช็คว่าถ้าคราวก่อนสร้างคอลัมน์ category_id ค้างไว้ จะได้ไม่สร้างซ้ำ
            if (!Schema::hasColumn('smart_lockers', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('type');
            }
        });

        // สเตปที่ 2: ผูกเส้นความสัมพันธ์ (Foreign Key)
        Schema::table('smart_lockers', function (Blueprint $table) {
            $table->foreign('category_id')
                  ->references('id')
                  ->on('smart_locker_categories')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('smart_lockers', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            // คืนค่า enum กลับมาถ้าเกิดการ Rollback
            $table->enum('category', ['smartlocker', 'safetylocker'])->default('smartlocker')->after('type');
        });
    }
};