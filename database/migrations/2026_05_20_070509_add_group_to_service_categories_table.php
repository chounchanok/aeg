<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            // เพิ่มคอลัมน์กลุ่มประเภท เพื่อแยกหมวดหมู่ออกจากกันชัดเจน
            $table->enum('group', ['equipment', 'package', 'service'])->default('package')->before('title_th');
        });
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};