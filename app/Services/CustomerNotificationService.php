<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * บันทึกแจ้งเตือนลูกค้า (กระดิ่งในแอป/เว็บ) + ยิง push พร้อมข้อมูลลิงก์ให้กดแล้วไปต่อได้ (คอมเมนต์ข้อ 5)
 *
 * แอปใช้ field เหล่านี้ตัดสินใจว่ากดแล้วไปไหน:
 *   - target_type + target_id : เปิดหน้าภายในแอป (order, service_request, invoice, reward_code, smart_locker_booking, reward, product)
 *   - url                     : ลิงก์ภายนอก เปิดผ่าน browser/WebView (ใช้เมื่อไม่มี target_type)
 */
class CustomerNotificationService
{
    public const TARGET_TYPES = [
        'order' => 'คำสั่งซื้อ',
        'service_request' => 'ใบแจ้งซ่อม',
        'invoice' => 'ใบแจ้งหนี้',
        'reward_code' => 'คูปอง/ของรางวัลของฉัน',
        'reward' => 'ของรางวัล EASE CLUB',
        'product' => 'สินค้า/บริการ',
        'smart_locker_booking' => 'การจองตู้เซฟ',
    ];

    public static function notify(
        int $userId,
        string $title,
        string $body,
        string $type = 'general',
        ?string $targetType = null,
        $targetId = null,
        ?string $url = null,
        bool $push = true
    ): ?int {
        try {
            $id = DB::table('notifications')->insertGetId([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'url' => $url,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[CustomerNotificationService] บันทึกแจ้งเตือนล้มเหลว: ' . $e->getMessage());
            return null;
        }

        if ($push) {
            try {
                PushNotificationService::sendToUser($userId, $title, $body, array_filter([
                    'type' => $type,
                    'notification_id' => (string) $id,
                    'target_type' => $targetType,
                    'target_id' => $targetId !== null ? (string) $targetId : null,
                    'url' => $url,
                ], fn ($v) => $v !== null && $v !== ''));
            } catch (\Throwable $e) {
                Log::warning('[CustomerNotificationService] ส่ง push ล้มเหลว: ' . $e->getMessage());
            }
        }

        return $id;
    }
}
