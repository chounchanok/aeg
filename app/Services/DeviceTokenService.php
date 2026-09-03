<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * รับ/บันทึก FCM device token ที่ฝั่ง Mobile แนบมากับ request
 *
 * ทีม Mobile แจ้งว่าจะแนบ token ของ Firebase Cloud Messaging มาพร้อมกับทุก login API
 * (นอกเหนือจาก endpoint เดิม POST /api/user/device-token ที่มีอยู่แล้วและยังใช้งานได้ปกติ)
 * แต่ชื่อ field ที่ทีม Mobile จะส่งมาจริงๆ ยังไม่ได้ยืนยันตายตัว (มีการเรียกว่า "TokenFCM")
 * เพื่อความปลอดภัยไว้ก่อน ฟังก์ชันนี้จึงเช็คชื่อ field ที่เป็นไปได้หลายแบบ (case/รูปแบบต่างๆ)
 * ถ้าทีม Mobile ยืนยันชื่อ field ที่แน่นอนแล้ว ให้เพิ่ม/แก้ในค่าคงที่ TOKEN_KEYS ด้านล่างนี้ได้เลย
 */
class DeviceTokenService
{
    protected const TOKEN_KEYS = [
        'TokenFCM', 'token_fcm', 'tokenFcm', 'FcmToken', 'fcm_token',
        'device_token', 'deviceToken', 'DeviceToken', 'fcm_registration_token',
    ];

    protected const DEVICE_TYPE_KEYS = [
        'device_type', 'deviceType', 'DeviceType', 'platform', 'Platform',
    ];

    /**
     * ถ้า request มี field ที่เข้าเงื่อนไขว่าเป็น FCM token ให้บันทึก/อัปเดตลงตาราง device_tokens
     * ผูกกับ user คนที่เพิ่ง login/สมัครสมาชิกสำเร็จ ถ้าไม่มี field เหล่านี้มาด้วยก็จะไม่ทำอะไร (no-op เงียบๆ)
     *
     * ไม่ throw exception ออกไป เพื่อไม่ให้ login/register ล้มเหลวเพราะเรื่องนี้
     */
    public static function captureFromRequest(Request $request, int $userId): void
    {
        try {
            $token = self::firstNonEmpty($request, self::TOKEN_KEYS);

            if (!$token) {
                return;
            }

            $deviceType = self::firstNonEmpty($request, self::DEVICE_TYPE_KEYS);
            if ($deviceType) {
                $deviceType = strtolower($deviceType);
                if (!in_array($deviceType, ['ios', 'android', 'web'], true)) {
                    $deviceType = null;
                }
            }

            $existing = DB::table('device_tokens')->where('token', $token)->first();

            if ($existing) {
                // token เดิมอาจเคยผูกกับ user คนอื่น (เช่น ล็อกเอาท์แล้วอีกคนมาล็อกอินเครื่องเดียวกัน) ให้ย้ายมาเป็นของ user ปัจจุบัน
                DB::table('device_tokens')->where('token', $token)->update([
                    'user_id' => $userId,
                    'device_type' => $deviceType ?? $existing->device_type,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('device_tokens')->insert([
                    'user_id' => $userId,
                    'token' => $token,
                    'device_type' => $deviceType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[DeviceTokenService] บันทึก FCM token จาก login ล้มเหลว: ' . $e->getMessage());
        }
    }

    protected static function firstNonEmpty(Request $request, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $request->input($key);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }
        return null;
    }
}
