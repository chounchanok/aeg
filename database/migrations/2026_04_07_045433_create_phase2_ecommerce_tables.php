<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ตารางสินค้าและบริการ (Products / Packages)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['service', 'package', 'equipment'])->default('service');
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable(); // ราคาเต็ม (สำหรับทำส่วนลด)
            $table->string('image_url')->nullable();
            $table->integer('point_earn')->default(0); // แต้มที่จะได้รับเมื่อซื้อ
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. ตารางที่อยู่ของลูกค้า (รองรับการมีหลายที่อยู่ เช่น บ้าน, ที่ทำงาน)
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title'); // เช่น 'บ้าน', 'ออฟฟิศ'
            $table->string('contact_name');
            $table->string('contact_phone', 20);
            $table->text('address_line');
            $table->string('province', 100);
            $table->string('district', 100);
            $table->string('subdistrict', 100);
            $table->string('zipcode', 10);
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 3. ตารางตะกร้าสินค้า (Cart)
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // 4. ตารางคำสั่งซื้อ (Orders)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // เลขที่ออเดอร์ (เช่น ORD-202604-001)
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('address_id')->nullable()->constrained('customer_addresses');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending_payment', 'paid', 'processing', 'completed', 'cancelled'])->default('pending_payment');
            $table->string('payment_gateway')->nullable(); // เช่น 'omise', '2c2p'
            $table->string('gateway_transaction_id')->nullable(); // Ref. จาก Gateway
            $table->json('gateway_response')->nullable(); // เก็บ Log จาก Gateway เผื่อตรวจสอบ
            $table->timestamps();
        });

        // 5. ตารางรายละเอียดคำสั่งซื้อ (Order Items)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->string('product_name'); // เก็บชื่อกันกรณีสินค้าเปลี่ยนชื่อในอนาคต
            $table->decimal('price', 12, 2);
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('products');
    }
};