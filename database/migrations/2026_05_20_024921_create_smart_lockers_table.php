<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_lockers', function (Blueprint $table) {
            $table->id();
            $table->string('locker_number')->unique(); // เลขตู้ เช่น PR-001, PV-001
            $table->enum('type', ['PRIME', 'PRIVILEGE']); // ประเภทตู้
            $table->string('title_th');
            $table->string('title_en')->nullable();
            $table->text('description_th')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('price', 10, 2); // ราคาเช่า
            $table->string('image_url')->nullable(); // รูปภาพตู้
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available'); // สถานะ ว่าง/ถูกเช่า/ซ่อมบำรุง
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_lockers');
    }
};