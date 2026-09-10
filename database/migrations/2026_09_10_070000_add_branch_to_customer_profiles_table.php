<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // หมายเหตุ: คอลัมน์ tax_id (เลขผู้เสียภาษี) มีอยู่แล้วในตาราง customer_profiles
        // เพิ่มเฉพาะคอลัมน์ branch (สาขา) ที่ยังไม่มี
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->string('branch', 100)->nullable()->after('tax_id');
        });
    }

    public function down(): void {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropColumn('branch');
        });
    }
};
