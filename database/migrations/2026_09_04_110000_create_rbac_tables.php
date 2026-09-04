<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบ RBAC (Role-Based Access Control) ใหม่ สำหรับแบ่งสิทธิ์การใช้งานหลังบ้านตามแผนก
 * ตามที่ฝ่ายบริหารกำหนด (11 โมดูล) โดย "ไม่แตะ" คอลัมน์ users.role เดิม
 * (customer/technician/admin/super_admin/content_admin) เพื่อไม่ให้ middleware เดิม
 * (CheckAdminRole ที่เช็ค role !== 'customer') พังไปด้วย — RBAC ชุดนี้ทำงานเป็นชั้นที่ 2
 * ต่อจากนั้น ให้สิทธิ์ละเอียดขึ้นระดับ "โมดูล/ฟีเจอร์" (fine-grained permission)
 *
 * โครงสร้าง: user หนึ่งคนมีได้หลาย role (user_roles), role หนึ่งอันมีได้หลาย permission
 * (role_has_permissions) — แบบเดียวกับแนวทางของ spatie/laravel-permission แต่เขียนเอง
 * เพื่อไม่ต้อง composer install เพิ่ม
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. ตารางบทบาท (Role) — เช่น security_admin, accounting, marketing, ...
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('ชื่ออ้างอิงในโค้ด เช่น security_admin, accounting, it');
            $table->string('name')->comment('ชื่อแสดงผล เช่น แอดมิน Security');
            $table->text('description')->nullable();
            $table->boolean('is_full_access')->default(false)->comment('true = ข้ามการเช็ค permission ทั้งหมด (ใช้กับ IT)');
            $table->timestamps();
        });

        // 2. ตารางสิทธิ์ย่อยระดับโมดูล (Permission) — เช่น service_requests.manage, orders.view
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('เช่น products.manage, orders.view');
            $table->string('name')->comment('ชื่อแสดงผล เช่น จัดการสินค้าและบริการ');
            $table->string('module')->comment('กลุ่มเมนูที่ permission นี้สังกัด เช่น products, orders');
            $table->timestamps();
        });

        // 3. Pivot: role <-> permission (หนึ่ง role มีได้หลาย permission)
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. Pivot: user <-> role (พนักงานหนึ่งคนอาจสวมได้หลาย role พร้อมกัน เช่น เป็นทั้ง marketing และ insurance)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
