<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // เพิ่มในตารางตะกร้าสินค้า
        Schema::table('cart_items', function (Blueprint $table) {
            $table->integer('duration_months')->nullable()->after('price')->comment('ระยะเวลาแพ็กเกจ (เดือน)');
        });

        // เพิ่มในตารางรายละเอียดออเดอร์
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('duration_months')->nullable()->after('price')->comment('ระยะเวลาแพ็กเกจ (เดือน)');
        });
    }

    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('duration_months');
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('duration_months');
        });
    }
};
