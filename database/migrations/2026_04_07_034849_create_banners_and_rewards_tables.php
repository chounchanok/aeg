<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ตารางแบนเนอร์
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_url');
            $table->enum('location', ['main', 'ease_club', 'service'])->default('main');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ตารางหมวดหมู่ของรางวัล
        Schema::create('reward_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon_url')->nullable();
            $table->timestamps();
        });

        // ตารางของรางวัล
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('reward_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('points_required');
            $table->string('image_url')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('minimum_tier_required')->nullable(); // เช่น 'Advance', 'Platinum'
            $table->timestamps();
        });

        // ประวัติการแลกของรางวัล
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reward_id')->constrained('rewards')->onDelete('cascade');
            $table->integer('points_used');
            $table->enum('status', ['pending', 'success', 'failed'])->default('success');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('reward_categories');
        Schema::dropIfExists('banners');
    }
};