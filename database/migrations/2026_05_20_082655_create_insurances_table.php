<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->string('title_th'); // ชื่อประกันภัย (ภาษาไทย)
            $table->string('title_en')->nullable(); // ชื่อประกันภัย (ภาษาอังกฤษ)
            $table->longText('description_th')->nullable(); // รายละเอียด (ภาษาไทย)
            $table->longText('description_en')->nullable(); // รายละเอียด (ภาษาอังกฤษ)
            $table->string('image_url')->nullable(); // รูปภาพหน้าปก
            $table->boolean('is_active')->default(true); // สถานะการแสดงผล
            $table->integer('sort_order')->default(0); // ลำดับการจัดเรียง
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};