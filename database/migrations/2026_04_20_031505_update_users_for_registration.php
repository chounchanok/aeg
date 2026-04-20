<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. เพิ่มคอลัมน์ในตาราง users
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->nullable()->after('email');
            $table->string('google_id')->unique()->nullable()->after('password');
            $table->string('line_id')->unique()->nullable()->after('google_id');
            $table->timestamp('phone_verified_at')->nullable();
        });

        // 2. ตารางเก็บรหัส OTP
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('code');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'google_id', 'line_id', 'phone_verified_at']);
        });
    }
};