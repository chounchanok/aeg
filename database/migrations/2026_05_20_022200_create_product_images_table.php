<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. สร้างตารางเก็บรูปภาพหลายรูป
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('image_url');
            $table->integer('sort_order')->default(0); // ลำดับการแสดงผลของรูป
            $table->timestamps();
        });

        // 2. 🌟 ย้ายรูปภาพเดิมจากตาราง products เข้ามาในตารางนี้ให้อัตโนมัติ (Data Migration)
        $products = DB::table('products')->whereNotNull('image_url')->where('image_url', '!=', '')->get();
        foreach ($products as $product) {
            DB::table('product_images')->insert([
                'product_id' => $product->id,
                'image_url' => $product->image_url,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};