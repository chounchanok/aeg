<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // เพิ่มคอลัมน์ technician_id เพื่อเก็บว่าช่างคนไหนรับผิดชอบงานนี้
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn('technician_id');
        });
    }
};