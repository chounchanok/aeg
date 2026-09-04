<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

/**
 * สร้าง Role (แผนก) และ Permission (สิทธิ์ระดับโมดูล) ตามที่ฝ่ายบริหารกำหนดไว้ 11 โมดูล
 * (ดูรายละเอียดที่มาของแต่ละ mapping ใน routes/web.php และ AppServiceProvider.php)
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['key' => 'service_requests.manage', 'name' => 'จัดการแจ้งซ่อม (นัดหมาย/มอบหมายช่าง/อัปเดตสถานะ/แชทงานซ่อม)', 'module' => 'service_requests'],
            ['key' => 'orders.manage', 'name' => 'ดู/จัดการประวัติคำสั่งซื้อ ยอดรวม ส่วนลด', 'module' => 'orders'],
            ['key' => 'products.manage', 'name' => 'จัดการสินค้าและบริการ ราคา และแต้มที่ได้รับ', 'module' => 'products'],
            ['key' => 'service_categories.manage', 'name' => 'จัดการหมวดหมู่บริการ', 'module' => 'service_categories'],
            ['key' => 'smart_lockers.manage', 'name' => 'จัดการตู้เซฟนิรภัย (ประเภท/ราคาเช่า/สถานะการเช่า)', 'module' => 'smart_lockers'],
            ['key' => 'insurances.manage', 'name' => 'จัดการข้อมูลประกันภัย', 'module' => 'insurances'],
            ['key' => 'notifications.manage', 'name' => 'Broadcast แจ้งเตือน ทั่วไป/โปรโมชัน/สิทธิพิเศษ', 'module' => 'notifications'],
            ['key' => 'support_chats.reply', 'name' => 'ตอบแชทติดต่อสอบถามลูกค้า', 'module' => 'support_chats'],
            ['key' => 'cms.manage', 'name' => 'จัดการแบนเนอร์/ป๊อปอัพ/สิทธิพิเศษ/FAQ/รีวิว', 'module' => 'cms'],
            ['key' => 'staff.manage', 'name' => 'จัดการรายชื่อพนักงาน/ช่างซ่อม และมอบหมายแผนก', 'module' => 'staff'],
            ['key' => 'customers.view', 'name' => 'ดูข้อมูลลูกค้าและแพ็กเกจ (ดูได้ทุกแผนก แก้ไขไม่ได้)', 'module' => 'customers'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['key' => $p['key']], $p);
        }

        $allPermissionKeys = array_column($permissions, 'key');

        $roles = [
            'security_admin' => [
                'name' => 'แอดมิน Security',
                'description' => 'ดูรายการแจ้งซ่อม นัดหมาย มอบหมายงานให้ช่าง อัปเดตสถานะงาน แชทงานซ่อม และดูประวัติคำสั่งซื้อ',
                'permissions' => ['service_requests.manage', 'orders.manage', 'support_chats.reply', 'customers.view'],
            ],
            'accounting' => [
                'name' => 'บัญชี',
                'description' => 'ดูประวัติการสั่งซื้อสินค้าและบริการทุกประเภท ยอดรวม ส่วนลด',
                'permissions' => ['orders.manage', 'customers.view'],
            ],
            'marketing' => [
                'name' => 'Marketing',
                'description' => 'จัดการสินค้า/บริการ หมวดหมู่บริการ แจ้งเตือน Broadcast และ CMS (แบนเนอร์/FAQ/รีวิว/สิทธิพิเศษ)',
                'permissions' => ['products.manage', 'service_categories.manage', 'notifications.manage', 'cms.manage', 'support_chats.reply', 'customers.view'],
            ],
            'smart_locker' => [
                'name' => 'Smart Locker',
                'description' => 'ระบุ Locker ประเภทตู้เซฟ ราคาเช่า/เดือน และสถานะการเช่า',
                'permissions' => ['smart_lockers.manage', 'customers.view'],
            ],
            'insurance_admin' => [
                'name' => 'Insurance',
                'description' => 'เพิ่ม/ลดบริการ และแก้ไขเนื้อหารายละเอียดประกันภัยเบื้องต้น พร้อมตอบแชทลูกค้าเรื่องประกัน',
                'permissions' => ['insurances.manage', 'support_chats.reply', 'customers.view'],
            ],
            'sales_admin' => [
                'name' => 'Sales Admin',
                'description' => 'ตอบแชทติดต่อสอบถามลูกค้า',
                'permissions' => ['support_chats.reply', 'customers.view'],
            ],
            'it' => [
                'name' => 'แผนก IT',
                'description' => 'เข้าถึงได้ทุกโมดูล จัดการรายชื่อพนักงานและช่างซ่อม พร้อมมอบหมายแผนก (Role) ให้พนักงานคนอื่น',
                'is_full_access' => true,
                // แนบทุก permission ไว้ด้วย (เผื่อใช้แสดงผลรายการสิทธิ์ใน UI ในอนาคต) — ตัวที่ทำให้
                // ผ่านทุกอย่างจริงๆ คือ flag is_full_access ใน CheckPermission/User::hasPermission()
                'permissions' => $allPermissionKeys,
            ],
        ];

        foreach ($roles as $key => $data) {
            $role = Role::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_full_access' => $data['is_full_access'] ?? false,
                ]
            );

            $permissionIds = Permission::whereIn('key', $data['permissions'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        }

        $this->command->info('สร้าง Role/Permission (RBAC) เรียบร้อยแล้ว: ' . implode(', ', array_keys($roles)));
    }
}
