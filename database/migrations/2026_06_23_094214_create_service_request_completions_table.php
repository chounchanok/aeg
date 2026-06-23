<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. อัปเดตตาราง service_requests เดิม (เพิ่มการเก็บเวลาแต่ละสเตป)
        Schema::table('service_requests', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('status')->comment('เวลากดรับงาน');
            $table->timestamp('traveling_at')->nullable()->after('accepted_at')->comment('เวลากดเดินทาง');
            $table->timestamp('arrived_at')->nullable()->after('traveling_at')->comment('เวลาถึงหน้างาน');
            $table->timestamp('started_at')->nullable()->after('arrived_at')->comment('เวลาเริ่มซ่อม');
            $table->timestamp('completed_at')->nullable()->after('started_at')->comment('เวลาส่งงาน');
        });

        // อัปเดต Enum ให้รองรับสถานะใหม่ของช่าง (ใช้ Raw SQL เพื่อความชัวร์ใน MySQL)
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'assigned', 'accepted', 'traveling', 'arrived', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");

        // 2. สร้างตารางใหม่สำหรับเก็บหลักฐานการปิดงาน (รูป/วิดีโอ/ลายเซ็น)
        Schema::create('service_request_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');

            // เก็บ Path ของไฟล์เป็น JSON Array เพื่อรองรับการอัปโหลดหลายไฟล์
            $table->json('before_media_paths')->nullable()->comment('รูป/วิดีโอก่อนซ่อม');
            $table->json('after_media_paths')->nullable()->comment('รูป/วิดีโอหลังซ่อม');

            // ลายเซ็นลูกค้า และหมายเหตุ
            $table->string('customer_signature_path')->nullable()->comment('รูปลายเซ็นลูกค้า');
            $table->text('technician_note')->nullable()->comment('หมายเหตุสรุปงานจากช่าง');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_completions');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_at',
                'traveling_at',
                'arrived_at',
                'started_at',
                'completed_at'
            ]);
        });

        // ย้อนกลับ Enum เป็นค่าเดิม
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
