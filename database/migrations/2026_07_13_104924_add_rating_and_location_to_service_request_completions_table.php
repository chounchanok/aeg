<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_request_completions', function (Blueprint $table) {
            $table->integer('rating')->default(5)->after('customer_signature_path'); // เก็บคะแนน 0-5
            $table->decimal('latitude', 10, 8)->nullable()->after('rating');       // เก็บพิกัด Lat
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');     // เก็บพิกัด Long
        });
    }

    public function down(): void
    {
        Schema::table('service_request_completions', function (Blueprint $table) {
            $table->dropColumn(['rating', 'latitude', 'longitude']);
        });
    }
};