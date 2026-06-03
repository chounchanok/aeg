<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id'); // เชื่อมกับแพ็กเกจที่รีวิว
            $table->unsignedBigInteger('user_id'); // คนรีวิว
            $table->integer('install_rating')->default(5); // คะแนนการติดตั้ง (1-5 ดาว)
            $table->integer('sales_rating')->default(5); // คะแนนพนักงานขาย (1-5 ดาว)
            $table->text('review_text')->nullable(); // ข้อความรีวิว
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_reviews');
    }
};
