<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. เพิ่มคอลัมน์รูปโปรไฟล์ในตาราง customer_profiles เดิม
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->string('profile_image_url')->nullable()->after('user_id');
        });

        // 2. ตารางรายการโปรด (Favorites เฉพาะสินค้า/บริการ)
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
            
            // ป้องกันการกดหัวใจซ้ำให้สินค้าเดิม
            $table->unique(['user_id', 'product_id']); 
        });

        // 3. ตารางเก็บ Device Token สำหรับยิง Push Notification (FCM)
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token')->unique();
            $table->enum('device_type', ['ios', 'android', 'web'])->nullable();
            $table->timestamps();
        });

        // 4. ตารางประวัติการแจ้งเตือน (Notifications In-app)
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->string('type')->nullable(); // เช่น 'promotion', 'service', 'system'
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('favorites');
        
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropColumn('profile_image_url');
        });
    }
};