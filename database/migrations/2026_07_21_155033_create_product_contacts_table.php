<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('contact_time'); // ช่วงเวลาที่สะดวก
            $table->text('message')->nullable(); // ข้อความเพิ่มเติม
            $table->string('status')->default('pending'); // pending, contacted
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('product_contacts');
    }
};