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
            // support_chats.reply = สิทธิ์เข้าเมนูแชท ส่วน "เห็นหัวข้อไหนบ้าง" กรองอีกชั้นตาม role ใน config/support_chat.php
            ['key' => 'support_chats.reply', 'name' => 'ตอบแชทติดต่อสอบถามลูกค้า (เฉพาะหัวข้อที่แผนกตัวเองรับผิดชอบ)', 'module' => 'support_chats'],
            ['key' => 'cms.manage', 'name' => 'จัดการแบนเนอร์/ป๊อปอัพ/สิทธิพิเศษ/FAQ/รีวิว', 'module' => 'cms'],
            ['key' => 'staff.manage', 'name' => 'จัดการรายชื่อพนักงาน/ช่างซ่อม และมอบหมายแผนก', 'module' => 'staff'],
            ['key' => 'customers.view', 'name' => 'ดูข้อมูลลูกค้าและแพ็กเกจ (ดูได้ทุกแผนก แก้ไขไม่ได้)', 'module' => 'customers'],
            // 🌟 สิทธิ์ "เขียน" หน้าลูกค้า (เพิ่มสินค้า/ประกัน/ตู้เซฟให้ลูกค้า, ใช้คูปองแทนลูกค้า, นำเข้าแต้ม)
            // แยกออกมาเพื่อให้หน้าลูกค้าเป็น Read-only สำหรับทุกแผนกตามข้อกำหนด — ค่าเริ่มต้นมีแค่ IT (full access)
            // ถ้าอนาคตอยากให้แผนกไหนทำได้ ให้เพิ่ม key นี้ใน 'permissions' ของ role นั้นแล้วรัน seeder ซ้ำ
            ['key' => 'customers.manage', 'name' => 'แก้ไขข้อมูลลูกค้า (เพิ่มสินค้า/ประกัน/ตู้เซฟให้ลูกค้า, ใช้คูปอง, นำเข้าแต้ม)', 'module' => 'customers'],

            // 🌟 รายการติดต่อจากลูกค้า (ฟอร์มติดต่อหน้าเว็บ/แอป) — แยกสิทธิ์ตามช่องทาง ให้แต่ละแผนกเห็นเฉพาะของตัวเอง
            ['key' => 'contacts.insurance', 'name' => 'รายการติดต่อเรื่องประกันภัย (insurance_contacts)', 'module' => 'contacts'],
            ['key' => 'contacts.safe', 'name' => 'รายการติดต่อเรื่องตู้เซฟนิรภัย (safe_contacts)', 'module' => 'contacts'],
            ['key' => 'contacts.product', 'name' => 'รายการติดต่อเรื่องสินค้า/บริการจากหน้าเว็บ (product_contacts)', 'module' => 'contacts'],
            ['key' => 'contacts.sales', 'name' => 'คำขอติดต่อฝ่ายขายจากแอปมือถือ (contact_admin_requests)', 'module' => 'contacts'],
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
                'description' => 'ระบุ Locker ประเภทตู้เซฟ ราคาเช่า/เดือน และสถานะการเช่า พร้อมตอบแชท/รายการติดต่อเรื่องตู้เซฟ',
                // 🌟 support_chats.reply: เห็นเฉพาะแชทหัวข้อ "ตู้เซฟนิรภัย" (mapping หัวข้อ→แผนก อยู่ใน config/support_chat.php)
                'permissions' => ['smart_lockers.manage', 'contacts.safe', 'support_chats.reply', 'customers.view'],
            ],
            'insurance_admin' => [
                'name' => 'Insurance',
                'description' => 'เพิ่ม/ลดบริการ และแก้ไขเนื้อหารายละเอียดประกันภัยเบื้องต้น พร้อมตอบแชทลูกค้าเรื่องประกัน',
                'permissions' => ['insurances.manage', 'contacts.insurance', 'support_chats.reply', 'customers.view'],
            ],
            'sales_admin' => [
                'name' => 'Sales Admin',
                'description' => 'ตอบแชทติดต่อสอบถามลูกค้า และดูแลรายการติดต่อจากหน้าสินค้า/ฟอร์มติดต่อฝ่ายขาย',
                'permissions' => ['support_chats.reply', 'contacts.product', 'contacts.sales', 'customers.view'],
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
