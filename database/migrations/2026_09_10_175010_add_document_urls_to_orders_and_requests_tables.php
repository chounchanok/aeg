<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // เพิ่มฟิลด์ในตาราง orders
        Schema::table('orders', function (Blueprint $table) {
            $table->text('quotation_url')->nullable()->after('status')->comment('ลิงก์ไฟล์ใบเสนอราคา');
            $table->text('receipt_url')->nullable()->after('quotation_url')->comment('ลิงก์ไฟล์ใบเสร็จรับเงิน');
        });

        // เพิ่มฟิลด์ในตาราง service_requests
        Schema::table('service_requests', function (Blueprint $table) {
            $table->text('quotation_url')->nullable()->after('status')->comment('ลิงก์ไฟล์ใบเสนอราคา');
            $table->text('receipt_url')->nullable()->after('quotation_url')->comment('ลิงก์ไฟล์ใบเสร็จรับเงิน');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['quotation_url', 'receipt_url']);
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['quotation_url', 'receipt_url']);
        });
    }
};