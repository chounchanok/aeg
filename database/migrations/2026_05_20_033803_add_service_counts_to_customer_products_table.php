<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_products', function (Blueprint $table) {
            // จำนวนครั้งที่บริการได้ทั้งหมด (เช่น ซื้อแพ็กเกจรายปี โทรเรียกได้ 4 ครั้ง)
            $table->integer('total_service_count')->default(0)->after('status');
            // จำนวนครั้งที่ใช้ไปแล้ว
            $table->integer('used_service_count')->default(0)->after('total_service_count');
        });
    }

    public function down(): void
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->dropColumn(['total_service_count', 'used_service_count']);
        });
    }
};