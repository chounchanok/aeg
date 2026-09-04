<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * สร้าง user ทดสอบ 1 คนต่อ 1 แผนก (RBAC role) สำหรับทดสอบสิทธิ์การเข้าถึงเมนู/route ต่างๆ
 * รันหลัง RolePermissionSeeder เสมอ (ต้องมี roles ในระบบก่อน)
 *
 * ⚠️ Username/Password ชุดนี้มีไว้สำหรับทดสอบบนเครื่อง dev/staging เท่านั้น
 * ห้ามปล่อยให้ยังใช้งานได้จริงบน production — ควรลบหรือเปลี่ยนรหัสผ่านก่อนขึ้นระบบจริง
 */
class StaffTestSeeder extends Seeder
{
    public function run(): void
    {
        $testPassword = 'Test@1234';

        $staffs = [
            ['username' => 'test_security',  'name' => 'ทดสอบ แอดมิน Security', 'role_key' => 'security_admin'],
            ['username' => 'test_accounting', 'name' => 'ทดสอบ บัญชี',           'role_key' => 'accounting'],
            ['username' => 'test_marketing',  'name' => 'ทดสอบ Marketing',       'role_key' => 'marketing'],
            ['username' => 'test_locker',     'name' => 'ทดสอบ Smart Locker',    'role_key' => 'smart_locker'],
            ['username' => 'test_insurance',  'name' => 'ทดสอบ Insurance',       'role_key' => 'insurance_admin'],
            ['username' => 'test_sales',      'name' => 'ทดสอบ Sales Admin',     'role_key' => 'sales_admin'],
            ['username' => 'test_it',         'name' => 'ทดสอบ แผนก IT',         'role_key' => 'it'],
        ];

        foreach ($staffs as $index => $s) {
            // 🌟 ตาราง users มี unique constraint บนคอลัมน์ phone (users_phone_unique) — ต้องให้แต่ละ
            // user ทดสอบมีเบอร์ไม่ซ้ำกัน (เดิมใช้ '0800000000' ซ้ำกันทุกคน insert คนที่ 2 ขึ้นไปเลย error)
            $phone = '080000' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            $user = User::updateOrCreate(
                ['username' => $s['username']],
                [
                    'email' => $s['username'] . '@aeg.test',
                    'password' => Hash::make($testPassword),
                    'phone' => $phone,
                    'role' => 'admin', // ผ่าน middleware 'admin' เดิมได้ (role !== customer) — RBAC มาคุมสิทธิ์ต่อ
                    'is_active' => true,
                ]
            );

            DB::table('customer_profiles')->updateOrInsert(
                ['user_id' => $user->id],
                ['first_name' => $s['name'], 'phone' => $phone, 'updated_at' => now(), 'created_at' => now()]
            );

            $role = Role::where('key', $s['role_key'])->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }

        $this->command->info('สร้าง user ทดสอบ ' . count($staffs) . ' คน (คนละแผนก) — username: test_security, test_accounting, test_marketing, test_locker, test_insurance, test_sales, test_it');
        $this->command->info('รหัสผ่านทดสอบทุกคน: ' . $testPassword . ' (ใช้บน dev/staging เท่านั้น)');
    }
}
