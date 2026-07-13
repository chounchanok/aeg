<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_reward_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reward_id')->constrained('rewards')->onDelete('cascade');
            
            $table->string('code')->unique(); // ตัวโค้ด เช่น RWD-A1B2C3D4
            $table->decimal('discount_amount', 10, 2); // มูลค่าส่วนลดตอนที่กดแลกมา
            $table->enum('status', ['active', 'used'])->default('active'); // สถานะ
            $table->timestamp('used_at')->nullable(); // วันที่กดใช้โค้ด
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_reward_codes');
    }
};