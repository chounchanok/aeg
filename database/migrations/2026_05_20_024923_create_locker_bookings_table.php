<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locker_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique(); // รหัสการจอง
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('smart_locker_id')->constrained('smart_lockers')->onDelete('cascade');
            $table->date('start_date')->nullable(); // วันที่เริ่มเช่า
            $table->date('end_date')->nullable(); // วันที่สิ้นสุดเช่า
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_gateway');
            $table->enum('status', ['pending_payment', 'paid', 'active', 'expired', 'cancelled'])->default('pending_payment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locker_bookings');
    }
};