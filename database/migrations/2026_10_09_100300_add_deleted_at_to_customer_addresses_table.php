<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * API ลบที่อยู่ (คอมเมนต์ข้อ 8)
 * ที่อยู่ที่เคยถูกใช้ในออเดอร์/แจ้งซ่อม/จองตู้เซฟ/ของรางวัล ลบจริงไม่ได้ (มี foreign key และต้องเก็บไว้เป็นประวัติ)
 * จึงใช้ soft delete: ตั้ง deleted_at แล้วซ่อนจากรายการที่อยู่ของลูกค้า — ที่อยู่ที่ไม่เคยถูกใช้จะลบจริง
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_addresses', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('customer_addresses', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
