<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_lockers', function (Blueprint $table) {
            // สร้างเป็น ENUM ให้เลือกได้แค่ 2 ค่า และตั้งค่าเริ่มต้นไว้เผื่อข้อมูลเก่า
            if (!Schema::hasColumn('smart_lockers', 'category')) {
                $table->enum('category', ['smartlocker', 'safetylocker'])->default('smartlocker')->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('smart_lockers', function (Blueprint $table) {
            if (Schema::hasColumn('smart_lockers', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};