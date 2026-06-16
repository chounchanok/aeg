<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. เพิ่มช่องความคุ้มครองในตาราง insurances
        Schema::table('insurances', function (Blueprint $table) {
            $table->text('insurance_coverage')->nullable()->after('description_en');
        });

        // 2. เพิ่มช่องอ้างอิง ID กลับไปที่ตารางหลัก ในตาราง customer_products
        Schema::table('customer_products', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('product_name')->comment('product, insurance, locker');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
        });
    }

    public function down(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn('insurance_coverage');
        });

        Schema::table('customer_products', function (Blueprint $table) {
            $table->dropColumn(['reference_type', 'reference_id']);
        });
    }
};