<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

/**
 * ตัวกลางสำหรับส่ง Push Notification ผ่าน Firebase Cloud Messaging (FCM) ไปยังมือถือของลูกค้า
 *
 * 🌟 หมายเหตุสำคัญ: ตอนนี้ยังไม่ได้ตั้งค่าไฟล์ Service Account JSON ของ Firebase
 * (FIREBASE_CREDENTIALS ใน .env ยังว่างอยู่) โค้ดส่วนนี้จึงเป็นแค่ "โครงสร้างเตรียมไว้" (scaffold)
 * เท่านั้น — ถ้ายังไม่ได้ตั้งค่า credentials ฟังก์ชันจะไม่ทำอะไร (no-op แบบเงียบๆ ไม่ throw error)
 * และจะบันทึกไว้ใน log ว่าข้ามการส่งเพราะอะไร
 *
 * เมื่อได้ไฟล์ Service Account JSON จาก Firebase Console (Project Settings > Service accounts
 * > Generate new private key) แล้ว ให้:
 *   1. นำไฟล์ไปวางไว้บนเซิร์ฟเวอร์ในตำแหน่งที่เข้าถึงจากภายนอกไม่ได้ (นอกโฟลเดอร์ public/)
 *   2. ตั้งค่าใน .env: FIREBASE_CREDENTIALS=/path/เต็ม/ไปยัง/ไฟล์.json และ FIREBASE_PROJECT_ID=ชื่อโปรเจกต์
 *   3. รัน composer install เพื่อติดตั้งแพ็กเกจ kreait/firebase-php (เพิ่มไว้ใน composer.json แล้ว)
 * จากนั้นระบบจะเริ่มส่ง push จริงได้ทันทีโดยไม่ต้องแก้โค้ดเพิ่ม
 */
class PushNotificationService
{
    /**
     * ส่ง push notification ไปยังทุกอุปกรณ์ (device_tokens) ของ user คนหนึ่ง
     *
     * @param  int    $userId
     * @param  string $title
     * @param  string $body
     * @param  array  $data ข้อมูลเพิ่มเติมที่จะแนบไปกับ push (เช่น service_request_id)
     *                      เพื่อให้แอปมือถือใช้นำทางไปหน้าที่เกี่ยวข้องเมื่อผู้ใช้กดแตะการแจ้งเตือน
     *
     * @return array รายการผลลัพธ์ต่อ 1 token เช่น [['token' => '...', 'success' => true, 'message_id' => '...'], ...]
     *               (ใช้เช็คผลตอนทดสอบผ่าน `php artisan push:test` — ที่อื่นในระบบไม่จำเป็นต้องสนใจค่าที่คืนมาก็ได้)
     */
    public static function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        $results = [];

        if (!self::isConfigured()) {
            Log::info("[PushNotificationService] ข้ามการส่ง push (ยังไม่ได้ตั้งค่า Firebase credentials) user_id={$userId}, title=\"{$title}\"");
            return $results;
        }

        $tokens = DB::table('device_tokens')->where('user_id', $userId)->pluck('token');

        if ($tokens->isEmpty()) {
            return $results;
        }

        try {
            $messaging = self::messaging();
        } catch (\Throwable $e) {
            Log::error('[PushNotificationService] ไม่สามารถเชื่อมต่อ Firebase ได้: ' . $e->getMessage());
            return $results;
        }

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(FirebaseNotification::create($title, $body))
                    ->withData(array_map('strval', $data));

                $messageId = $messaging->send($message);

                // 🌟 log ยืนยันตอนสำเร็จด้วย (ก่อนหน้านี้สำเร็จแล้วจะเงียบ ทำให้แยกไม่ออกว่า "ยังไม่ส่ง" หรือ "ส่งแล้วแค่ไม่บอก")
                Log::info("[PushNotificationService] ส่ง push สำเร็จ user_id={$userId}, token=" . substr($token, 0, 12) . "..., message_id={$messageId}");

                $results[] = ['token' => $token, 'success' => true, 'message_id' => $messageId];
            } catch (\Throwable $e) {
                // ไม่ throw ต่อ เพื่อไม่ให้ token เสีย/หมดอายุตัวเดียว ทำให้การส่งไปยังอุปกรณ์อื่นของ user คนเดียวกันหยุดไปด้วย
                Log::warning("[PushNotificationService] ส่ง push ไปยัง token ล้มเหลว (user_id={$userId}): " . $e->getMessage());
                $results[] = ['token' => $token, 'success' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * เช็คว่ามีการตั้งค่า Firebase credentials พร้อมใช้งานจริงหรือยัง
     */
    protected static function isConfigured(): bool
    {
        $credentials = config('services.firebase.credentials');
        return !empty($credentials) && is_string($credentials) && file_exists($credentials);
    }

    protected static function messaging()
    {
        $factory = (new Factory())->withServiceAccount(config('services.firebase.credentials'));

        if ($projectId = config('services.firebase.project_id')) {
            $factory = $factory->withProjectId($projectId);
        }

        return $factory->createMessaging();
    }
}
