<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * แจ้งเตือนภายในระบบไปยัง "แผนก" (RBAC role) ที่เกี่ยวข้องโดยอัตโนมัติ เมื่อมีรายการใหม่
 * เข้ามาในระบบ (แจ้งซ่อม/คำสั่งซื้อ/ขอใบเสนอราคา/ติดต่อฝ่ายขาย) — ดู migration
 * 2026_09_04_110400_create_staff_notifications_table.php สำหรับขอบเขตและข้อจำกัด
 *
 * ออกแบบให้ไม่ throw exception ออกไปนอก service (กันไม่ให้การแจ้งเตือนล้มเหลวไปทำให้
 * flow หลัก เช่น การสร้างออเดอร์/แจ้งซ่อม พังไปด้วย — เหมือนแนวทางเดียวกับ PushNotificationService)
 */
class StaffNotificationService
{
    /**
     * แจ้งเตือนไปยังทุกคนในแผนก (role) เดียว
     */
    public static function notifyRole(string $roleKey, string $title, string $body, ?string $linkUrl = null, ?string $type = null): void
    {
        try {
            $role = Role::where('key', $roleKey)->first();

            if (!$role) {
                Log::info("[StaffNotificationService] ไม่พบ role '{$roleKey}' ข้ามการแจ้งเตือน (title=\"{$title}\")");
                return;
            }

            DB::table('staff_notifications')->insert([
                'role_id' => $role->id,
                'user_id' => null,
                'title' => $title,
                'body' => $body,
                'link_url' => $linkUrl,
                'type' => $type,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[StaffNotificationService] แจ้งเตือนแผนกล้มเหลว: ' . $e->getMessage());
        }
    }

    /**
     * แจ้งเตือนไปยังหลายแผนกพร้อมกัน (เช่น orders.manage มีทั้ง security_admin และ accounting)
     */
    public static function notifyRoles(array $roleKeys, string $title, string $body, ?string $linkUrl = null, ?string $type = null): void
    {
        foreach ($roleKeys as $roleKey) {
            self::notifyRole($roleKey, $title, $body, $linkUrl, $type);
        }
    }
}
