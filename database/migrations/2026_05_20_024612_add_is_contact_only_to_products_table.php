<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // เพิ่มคอลัมน์เช็คว่า "ต้องติดต่อฝ่ายขายเท่านั้นหรือไม่" (Default = false คือซื้อได้ปกติ)
            $table->boolean('is_contact_only')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_contact_only');
        });
    }
};