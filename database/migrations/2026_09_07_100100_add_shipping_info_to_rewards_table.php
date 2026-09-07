<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มข้อมูลเงื่อนไข/การจัดส่งของรางวัล EASE CLUB ให้ mobile แสดงบนหน้ารายละเอียดรางวัล
 * (GET /ease-club/rewards/{rewardId}) และให้แอดมินกรอกได้จากหลังบ้าน (/admin/cms/ease-club):
 * - return_policy     : เงื่อนไขการยกเลิกหรือคืนคะแนน
 * - shipping_fee      : ค่าจัดส่ง (บาท) — 0 = จัดส่งฟรี
 * - delivery_estimate : ระยะเวลาจัดส่ง เช่น "3-5 วันทำการ"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            if (!Schema::hasColumn('rewards', 'return_policy')) {
                $table->text('return_policy')->nullable()->after('description_en')->comment('เงื่อนไขการยกเลิกหรือคืนคะแนน');
            }

            if (!Schema::hasColumn('rewards', 'shipping_fee')) {
                $table->decimal('shipping_fee', 10, 2)->default(0)->after('return_policy')->comment('ค่าจัดส่ง (บาท) 0 = ส่งฟรี');
            }

            if (!Schema::hasColumn('rewards', 'delivery_estimate')) {
                $table->string('delivery_estimate', 100)->nullable()->after('shipping_fee')->comment('ระยะเวลาจัดส่ง เช่น 3-5 วันทำการ');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $columns = array_filter(
                ['return_policy', 'shipping_fee', 'delivery_estimate'],
                fn ($column) => Schema::hasColumn('rewards', $column)
            );

            if (!empty($columns)) {
                $table->dropColumn(array_values($columns));
            }
        });
    }
};
