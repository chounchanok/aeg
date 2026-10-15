<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 🌟 ลืมรหัสผ่านด้วยเบอร์มือถือ + OTP (ThaiBulkSMS) — ใช้ร่วมกันทั้งเว็บและ API
 *
 *   1) requestOtp(phone)               → ส่ง SMS OTP (เบอร์ต้องมีบัญชีอยู่แล้ว)
 *   2) verifyOtp(phone, pin)           → OTP ถูก → ได้ reset_token (อายุ 15 นาที ใช้ได้ครั้งเดียว)
 *   3) resetPassword(phone, token, pw) → ตั้งรหัสผ่านใหม่ + ออกจากระบบทุกอุปกรณ์ของแอป
 *
 * เก็บสถานะใน Cache (แยกจากตาราง otp_codes ของการสมัครสมาชิก จึงไม่ชนกัน)
 * ทุกเมธอดคืน ['ok' => bool, 'message' => string, 'status' => httpCode, ...]
 */
class PasswordResetOtpService
{
    public const OTP_TTL_MINUTES = 5;
    public const RESET_TTL_MINUTES = 15;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_VERIFY_ATTEMPTS = 5;
    public const PASSWORD_MIN = 6; // เท่ากับตอนสมัครสมาชิกผ่านแอป

    /** แปลง +66 / 66 / ไม่มี 0 นำหน้า → 0xxxxxxxxx (เหมือนหน้า login/สมัคร) */
    public static function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);
        if (str_starts_with($phone, '+66')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '66') && strlen($phone) == 11) {
            $phone = '0' . substr($phone, 2);
        }
        if (strlen($phone) == 9 && in_array(substr($phone, 0, 1), ['6', '8', '9'])) {
            $phone = '0' . $phone;
        }
        return $phone;
    }

    public static function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^0[0-9]{8,9}$/', $phone);
    }

    public static function requestOtp(string $rawPhone): array
    {
        $phone = self::normalizePhone($rawPhone);
        if (!self::isValidPhone($phone)) {
            return self::fail(__('รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง'), 422);
        }

        $user = self::findUser($phone);
        if (!$user) {
            return self::fail(__('ไม่พบบัญชีที่ใช้เบอร์มือถือนี้'), 404);
        }

        // กันกดส่งรัว ๆ (เปลืองเครดิต SMS)
        $cooldownKey = self::key('cooldown', $phone);
        if (Cache::has($cooldownKey)) {
            $wait = max(1, (int) Cache::get($cooldownKey) - time());
            return self::fail(__('กรุณารอ :sec วินาที ก่อนขอรหัส OTP ใหม่', ['sec' => $wait]), 429, ['retry_after' => $wait]);
        }

        try {
            $response = Http::asForm()->post('https://otp.thaibulksms.com/v2/otp/request', [
                'key' => env('THAIBULK_APP_KEY', '17828749558276'),
                'secret' => env('THAIBULK_APP_SECRET', '5fc3972aecabf0f433a9174bf885a0d5'),
                'msisdn' => $phone,
            ]);
            $data = $response->json() ?? [];
            Log::info('ThaiBulkSMS Forgot Password OTP:', $data);
        } catch (\Throwable $e) {
            Log::error('ThaiBulkSMS Forgot Password OTP error: ' . $e->getMessage());
            return self::fail(__('ระบบขัดข้อง ไม่สามารถเชื่อมต่อ SMS ได้'), 500);
        }

        if (!$response->successful() || empty($data['token'])) {
            $err = $data['error']['description'] ?? __('ระบบ SMS ขัดข้อง');
            return self::fail(__('ไม่สามารถส่ง SMS ได้:') . ' ' . $err, 500);
        }

        $refCode = $data['refno'] ?? $data['ref_code'] ?? 'N/A';
        Cache::put(self::key('otp', $phone), [
            'token' => $data['token'],
            'ref_code' => $refCode,
            'attempts' => 0,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));
        Cache::forget(self::key('reset', $phone)); // ขอ OTP ใหม่ = ยกเลิก reset_token เดิม
        Cache::put($cooldownKey, time() + self::RESEND_COOLDOWN_SECONDS, now()->addSeconds(self::RESEND_COOLDOWN_SECONDS));

        return [
            'ok' => true,
            'status' => 200,
            'message' => __('ส่งรหัส OTP แล้ว (Ref:') . ' ' . $refCode . ')',
            'phone' => $phone,
            'ref_code' => $refCode,
            'expires_in' => self::OTP_TTL_MINUTES * 60,
            'resend_in' => self::RESEND_COOLDOWN_SECONDS,
        ];
    }

    public static function verifyOtp(string $rawPhone, string $pin): array
    {
        $phone = self::normalizePhone($rawPhone);
        $otpKey = self::key('otp', $phone);
        $otp = Cache::get($otpKey);

        if (!$otp) {
            return self::fail(__('รหัส OTP หมดอายุหรือยังไม่ได้ขอรหัส กรุณาขอรหัสใหม่'), 410);
        }
        if (!preg_match('/^[0-9]{6}$/', $pin)) {
            return self::fail(__('รหัส OTP ต้องมี 6 หลักพอดี'), 422);
        }
        if ($otp['attempts'] >= self::MAX_VERIFY_ATTEMPTS) {
            Cache::forget($otpKey);
            return self::fail(__('กรอกรหัส OTP ผิดเกินจำนวนครั้งที่กำหนด กรุณาขอรหัสใหม่'), 429);
        }

        try {
            $response = Http::asForm()->post('https://otp.thaibulksms.com/v2/otp/verify', [
                'key' => env('THAIBULK_APP_KEY', '17828749558276'),
                'secret' => env('THAIBULK_APP_SECRET', '5fc3972aecabf0f433a9174bf885a0d5'),
                'token' => $otp['token'],
                'pin' => $pin,
            ]);
            $data = $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('ThaiBulkSMS Forgot Password verify error: ' . $e->getMessage());
            return self::fail(__('ระบบขัดข้อง ไม่สามารถตรวจสอบ OTP ได้'), 500);
        }

        if (!($response->successful() && ($data['status'] ?? null) === 'success')) {
            $otp['attempts']++;
            Cache::put($otpKey, $otp, now()->addMinutes(self::OTP_TTL_MINUTES));
            $left = max(0, self::MAX_VERIFY_ATTEMPTS - $otp['attempts']);
            return self::fail(__('รหัส OTP ไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง'), 422, ['attempts_left' => $left]);
        }

        // ✅ OTP ถูก → ออก reset_token ใช้ได้ครั้งเดียว
        Cache::forget($otpKey);
        $resetToken = Str::random(64);
        Cache::put(self::key('reset', $phone), hash('sha256', $resetToken), now()->addMinutes(self::RESET_TTL_MINUTES));

        return [
            'ok' => true,
            'status' => 200,
            'message' => __('ยืนยันรหัส OTP สำเร็จ กรุณาตั้งรหัสผ่านใหม่'),
            'phone' => $phone,
            'reset_token' => $resetToken,
            'expires_in' => self::RESET_TTL_MINUTES * 60,
        ];
    }

    public static function resetPassword(string $rawPhone, string $resetToken, string $password): array
    {
        $phone = self::normalizePhone($rawPhone);
        $resetKey = self::key('reset', $phone);
        $hash = Cache::get($resetKey);

        if (!$hash || !hash_equals($hash, hash('sha256', $resetToken))) {
            return self::fail(__('ลิงก์ตั้งรหัสผ่านหมดอายุหรือไม่ถูกต้อง กรุณาเริ่มขั้นตอนลืมรหัสผ่านใหม่'), 410);
        }

        $user = self::findUser($phone);
        if (!$user) {
            return self::fail(__('ไม่พบบัญชีที่ใช้เบอร์มือถือนี้'), 404);
        }

        $user->forceFill(['password' => Hash::make($password)])->save();
        Cache::forget($resetKey);

        // ความปลอดภัย: ให้ทุกอุปกรณ์ที่ล็อกอินแอปค้างไว้ต้องเข้าสู่ระบบใหม่
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return ['ok' => true, 'status' => 200, 'message' => __('ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่'), 'phone' => $phone];
    }

    private static function findUser(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    private static function key(string $type, string $phone): string
    {
        return 'pwreset:' . $type . ':' . $phone;
    }

    private static function fail(string $message, int $status, array $extra = []): array
    {
        return ['ok' => false, 'status' => $status, 'message' => $message] + $extra;
    }
}
