<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('topic'); // บริการที่กดเข้ามา (เช่น ประกัน, ตู้เซฟ)
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email');
            $table->string('preferred_time'); // morning, afternoon, anytime
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // รอดำเนินการ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
