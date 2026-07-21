<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. ตารางเก็บคนติดต่อเรื่อง ประกันภัย
        Schema::create('insurance_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insurance_id')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('contact_time');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        // 2. ตารางเก็บคนติดต่อเรื่อง ตู้เซฟ
        Schema::create('safe_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('smart_locker_id')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('contact_time');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('insurance_contacts');
        Schema::dropIfExists('safe_contacts');
    }
};