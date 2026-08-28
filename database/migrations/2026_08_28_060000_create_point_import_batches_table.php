<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เก็บประวัติการ "นำเข้าแต้มลูกค้าเป็นชุดผ่านไฟล์ Excel" ของแอดมิน
     * แต่ละแถวคือ 1 ไฟล์ที่อัปโหลด — เก็บสรุปว่าสำเร็จ/ไม่สำเร็จกี่รายการ และรายละเอียดแถวที่ผิดพลาด (fail_details เป็น JSON)
     */
    public function up(): void
    {
        Schema::create('point_import_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable(); // แอดมินที่นำเข้า
            $table->string('original_filename')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('fail_count')->default(0);
            $table->longText('fail_details')->nullable(); // JSON: [{row, phone, reason}, ...]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_import_batches');
    }
};
