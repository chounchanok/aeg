<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            // เพิ่มมูลค่าส่วนลด (เป็นตัวเลขทศนิยมเผื่อมีเศษ หรือจะใช้ integer ก็ได้ครับ)
            $table->decimal('discount_amount', 10, 2)->default(0)->after('points_required');
        });
    }

    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });
    }
};