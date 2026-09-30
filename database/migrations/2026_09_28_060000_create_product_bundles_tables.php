<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * สินค้าจับกลุ่ม (Bundle) — แอดมินเลือกสินค้าตั้งแต่ 2 ชิ้นขึ้นไปมารวมเป็นชุด แล้วตั้ง "ราคาชุด" ใหม่เอง
 * (ไม่ใช่ % ส่วนลด — ตามที่ตกลงกันไว้) มือถือฝั่งตะกร้าจะเช็คสินค้าที่มีอยู่แล้ว แล้วแนะนำให้ซื้อเพิ่มเพื่อรับราคาชุด
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name_th');
            $table->string('name_en')->nullable();
            $table->decimal('bundle_price', 12, 2)->comment('ราคาชุดที่แอดมินกำหนดเอง (ไม่ใช่ % ส่วนลด)');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_bundle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_bundle_id')->constrained('product_bundles')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1)->comment('จำนวนของสินค้าชิ้นนี้ที่ต้องมีในตะกร้าเพื่อให้ครบชุด');
            $table->timestamps();

            $table->unique(['product_bundle_id', 'product_id']);
        });

        // 🌟 เก็บส่วนลดจากบันเดิลแยกจากช่อง discount รวม เพื่อรายงาน/ตรวจสอบย้อนหลังได้ว่าส่วนลดมาจากไหน
        // orders.discount ยังคงเป็นยอดรวมส่วนลดทั้งหมด (reward + bundle) เหมือนเดิม ไม่กระทบสูตรคำนวณเดิม
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('bundle_discount', 12, 2)->default(0)->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('bundle_discount');
        });
        Schema::dropIfExists('product_bundle_items');
        Schema::dropIfExists('product_bundles');
    }
};
