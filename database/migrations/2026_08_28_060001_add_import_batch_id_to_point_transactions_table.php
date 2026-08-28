<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ผูกแต่ละแถวใน point_transactions เข้ากับ batch การนำเข้า Excel (ถ้ามี)
     * เพื่อให้ดูได้ว่ารายการนี้มาจากการนำเข้าไฟล์ไหน — เป็น nullable เพราะ transaction ปกติ (earn/redeem จากการใช้งานแอป) ไม่มี batch
     */
    public function up(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->foreignId('import_batch_id')
                ->nullable()
                ->after('id')
                ->constrained('point_import_batches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropForeign(['import_batch_id']);
            $table->dropColumn('import_batch_id');
        });
    }
};
