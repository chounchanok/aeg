<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * 🌟 ซิงค์แผนก (roles) และสิทธิ์ (permissions) ของระบบ RBAC ให้ตรงกับ RolePermissionSeeder อัตโนมัติ
 * ตอน php artisan migrate — เพื่อไม่ให้สิทธิ์ใหม่ (customers.manage, contacts.*, support_chats.reply ของ
 * Smart Locker ฯลฯ) "หาย" เพราะลืมรัน db:seed แล้วเมนูไม่ขึ้นสำหรับบางแผนก
 *
 * idempotent: รันซ้ำได้ ไม่กระทบ user_roles (role ที่ assign ให้พนักงานไว้แล้ว)
 * ถ้าแก้ RolePermissionSeeder ในอนาคต ให้กดปุ่ม "ซิงค์สิทธิ์" ในหน้า /admin/staff หรือรัน
 * php artisan db:seed --class=RolePermissionSeeder อีกครั้ง
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_has_permissions')) {
            return; // ตาราง RBAC ยังไม่ถูกสร้าง (migration 2026_09_04_110000 จะรันก่อนเสมอตามลำดับชื่อไฟล์)
        }

        RolePermissionSeeder::sync();
    }

    public function down(): void
    {
        // ไม่ย้อนกลับ — เป็นการซิงค์ข้อมูล ไม่ใช่โครงสร้าง
    }
};
