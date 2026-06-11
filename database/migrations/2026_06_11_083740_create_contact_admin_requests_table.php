<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_admin_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->default('personal')->comment('business หรือ personal');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address_full'); // จังหวัด, เขต, แขวง, รหัสไปรษณีย์
            $table->string('email');
            $table->string('phone');
            $table->string('company_name')->nullable();
            $table->string('preferred_contact_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_admin_requests');
    }
};