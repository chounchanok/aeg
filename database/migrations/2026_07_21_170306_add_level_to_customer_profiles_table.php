<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('customer_profiles', function (Blueprint $table) {
            // เพิ่มคอลัมน์ level โดยให้ค่าเริ่มต้นเป็น Standard
            $table->string('level')->default('Standard')->after('last_name');
        });
    }

    public function down(): void {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};