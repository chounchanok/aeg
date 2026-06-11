<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_reviews', function (Blueprint $table) {
            // เพิ่มฟิลด์สำหรับรีวิวฝ่ายขาย และที่เก็บไฟล์รูป/วิดีโอ (JSON array)
            $table->text('sales_review_text')->nullable()->after('sales_rating');
            $table->json('media_paths')->nullable()->after('sales_review_text')->comment('เก็บ Path รูปหรือวิดีโอ');
        });
    }

    public function down(): void
    {
        Schema::table('package_reviews', function (Blueprint $table) {
            $table->dropColumn(['sales_review_text', 'media_paths']);
        });
    }
};