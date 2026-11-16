<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🌟 ทีม + QR Code ดาวน์โหลดแอป และการนับยอด
 * - app_download_teams  : ทีม (แต่ละทีมมีลิงก์/QR ของตัวเอง https://app.aeginc.co/d/{code})
 * - app_download_clicks : ทุกครั้งที่มีคนสแกน QR / กดลิงก์ของทีม (แยก iOS / Android / อื่นๆ)
 * - app_installs        : แอปรายงานการติดตั้งตอนเปิดครั้งแรก (POST /api/app-installs) พร้อมผูกกับทีม
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_download_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique(); // ใช้ในลิงก์ /d/{code} และ campaign ของสโตร์
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('app_download_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('app_download_teams')->cascadeOnDelete();
            $table->string('platform', 10); // ios, android, other
            $table->string('ip_hash', 64)->nullable(); // sha256 ของ IP (ไม่เก็บ IP จริง)
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['team_id', 'created_at']);
            $table->index(['platform', 'ip_hash', 'created_at']);
        });

        Schema::create('app_installs', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 10); // ios, android
            $table->string('device_id', 191); // id ที่แอปสร้างเก็บไว้ในเครื่อง (ส่งซ้ำได้ ไม่นับซ้ำ)
            $table->foreignId('team_id')->nullable()->constrained('app_download_teams')->nullOnDelete();
            $table->string('attribution', 20)->default('none'); // referrer (Android ยืนยัน), team_code, ip_match (iOS โดยประมาณ), none
            $table->string('install_referrer', 500)->nullable();
            $table->string('app_version', 30)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->unique(['platform', 'device_id']);
            $table->index(['team_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_installs');
        Schema::dropIfExists('app_download_clicks');
        Schema::dropIfExists('app_download_teams');
    }
};
