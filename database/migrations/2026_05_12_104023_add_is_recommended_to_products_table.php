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
        Schema::table('products', function (Blueprint $table) {
            // เพิ่มคอลัมน์ is_recommended เป็น boolean ค่าเริ่มต้นคือ false
            $table->boolean('is_recommended')->default(false)->after('description_en'); // หรือ after('คอลัมน์ที่คุณต้องการให้อยู่ต่อท้าย')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // ลบคอลัมน์ทิ้งหากมีการ Rollback
            $table->dropColumn('is_recommended');
        });
    }
};