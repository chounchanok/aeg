<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_wallets', function (Blueprint $table) {
            // เพิ่ม Member ID (เช่น AEG-100001) และ วันหมดอายุคะแนน
            $table->string('member_id')->unique()->nullable()->after('user_id');
            $table->date('points_expiry_date')->nullable()->after('current_points');
        });
    }

    public function down(): void
    {
        Schema::table('customer_wallets', function (Blueprint $table) {
            $table->dropColumn(['member_id', 'points_expiry_date']);
        });
    }
};