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
        // 1. Users & Authentication
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['customer', 'technician', 'admin', 'super_admin', 'content_admin'])->default('customer');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 2. Profiles
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('technician_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->json('skills')->nullable();
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_long', 11, 8)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // 3. Loyalty System
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_spending', 12, 2);
            $table->decimal('point_multiplier', 3, 2)->default(1.00);
            $table->timestamps();
        });

        Schema::create('customer_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('current_tier_id')->constrained('loyalty_tiers');
            $table->decimal('total_spending', 12, 2)->default(0.00);
            $table->integer('current_points')->default(0);
            $table->timestamps();
        });

        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('amount');
            $table->enum('type', ['earn', 'redeem', 'adjust']);
            $table->string('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 4. Service Requests & Jobs
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('service_type');
            $table->text('description')->nullable();
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_long', 11, 8)->nullable();
            $table->text('site_address')->nullable();
            $table->dateTime('preferred_date')->nullable();
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('users');
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->tinyInteger('customer_rating')->nullable();
            $table->text('customer_comment')->nullable();
            $table->timestamps();
        });

        Schema::create('job_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->string('photo_url');
            $table->enum('type', ['before', 'after', 'document']);
            $table->timestamp('uploaded_at')->useCurrent();
        });

        // 5. Products & Payments
        Schema::create('customer_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('product_name');
            $table->string('serial_number')->unique()->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expire_date')->nullable();
            $table->enum('status', ['active', 'expired', 'void'])->default('active');
            $table->string('policy_document_url')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('THB');
            $table->enum('payment_method', ['credit_card', 'qr_code', 'bank_transfer']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_ref')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamps();
        });

        // 6. CMS Contents
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('category', ['news', 'activity', 'project', 'promotion']);
            $table->longText('body')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'published'])->default('draft');
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ Table ย้อนลำดับเพื่อป้องกัน Foreign Key Error
        Schema::dropIfExists('contents');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('customer_products');
        Schema::dropIfExists('job_photos');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('customer_wallets');
        Schema::dropIfExists('loyalty_tiers');
        Schema::dropIfExists('technician_profiles');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
