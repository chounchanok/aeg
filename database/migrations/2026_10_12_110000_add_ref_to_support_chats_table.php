<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🌟 แนบสินค้า/บริการ/แพ็กเกจของฉัน/ของรางวัล/ออเดอร์ ไปกับข้อความแชทติดต่อสอบถาม
 * ref_type + ref_id = รายการที่แนบ, ref_snapshot = ชื่อ/รูป/สถานะ ณ ตอนส่ง (JSON)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_chats', function (Blueprint $table) {
            if (!Schema::hasColumn('support_chats', 'ref_type')) {
                $table->string('ref_type', 30)->nullable()->after('message');
            }
            if (!Schema::hasColumn('support_chats', 'ref_id')) {
                $table->unsignedBigInteger('ref_id')->nullable()->after('ref_type');
            }
            if (!Schema::hasColumn('support_chats', 'ref_snapshot')) {
                $table->json('ref_snapshot')->nullable()->after('ref_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_chats', function (Blueprint $table) {
            foreach (['ref_snapshot', 'ref_id', 'ref_type'] as $col) {
                if (Schema::hasColumn('support_chats', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
