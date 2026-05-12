<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            // เพิ่มคอลัมน์ is_active เป็น boolean ค่าเริ่มต้นคือ true (แสดงผล)
            $table->boolean('is_active')->default(true)->after('points_required'); 
            // หมายเหตุ: after(...) เป็นการจัดลำดับคอลัมน์ให้ต่อท้าย points_required หรือฟิลด์อื่นๆ ที่คุณมี
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};