<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. เพิ่มคอลัมน์ของเฟส 4 เข้าไปในตาราง service_requests เดิม
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('ticket_number')->nullable()->after('id');
            $table->foreignId('customer_product_id')->nullable()->constrained('customer_products')->onDelete('cascade');
            $table->text('problem_description')->nullable();
            $table->foreignId('address_id')->nullable()->constrained('customer_addresses')->onDelete('set null');
            $table->text('custom_address_text')->nullable();
            $table->enum('time_slot', ['09:00-12:00', '13:00-18:00'])->nullable();
        });

        // 2. สร้างตารางเก็บรูปภาพประกอบ (เพราะยังไม่เคยสร้าง)
        Schema::create('service_request_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
            $table->string('image_url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_images');
        
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_number', 'customer_product_id', 'problem_description', 
                'address_id', 'custom_address_text', 'time_slot'
            ]);
        });
    }
};