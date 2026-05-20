<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('preferred_date')->nullable()->after('address_id'); // วันที่ต้องการให้เข้าบริการ
            $table->text('note')->nullable()->after('preferred_date'); // หมายเหตุเพิ่มเติม
            $table->string('coupon_code')->nullable()->after('note'); // โค้ดส่วนลด
            $table->string('attachment_url')->nullable()->after('coupon_code'); // ไฟล์แนบรูปภาพหรือวิดีโอ
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['preferred_date', 'note', 'coupon_code', 'attachment_url']);
        });
    }
};