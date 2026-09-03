<?php

namespace App\Console\Commands;

use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * คำสั่งสำหรับทดสอบว่าระบบ Push Notification (FCM) เชื่อมต่อและส่งได้จริงหรือไม่
 * โดยไม่ต้องไปไล่กดเปลี่ยนสถานะออเดอร์/งานซ่อมจริงในระบบ
 *
 * วิธีใช้: php artisan push:test {user_id}
 * ตัวอย่าง: php artisan push:test 15
 */
class TestPushNotification extends Command
{
    protected $signature = 'push:test
                            {user_id : ID ของ user (ในตาราง users) ที่จะทดสอบส่ง push ไปหา}
                            {--title=ทดสอบการแจ้งเตือน : หัวข้อการแจ้งเตือนที่จะส่ง}
                            {--body=นี่คือข้อความทดสอบจากระบบ AEG EASE CLUB : เนื้อหาการแจ้งเตือนที่จะส่ง}';

    protected $description = 'ทดสอบส่ง Push Notification (FCM) ไปยัง device token ทั้งหมดของ user คนหนึ่ง เพื่อเช็คว่าตั้งค่า Firebase ถูกต้องหรือยัง';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');

        // 1. เช็คการตั้งค่า Firebase ก่อน
        $credentials = config('services.firebase.credentials');
        $projectId = config('services.firebase.project_id');

        $this->info('ตรวจสอบการตั้งค่า Firebase:');
        $this->line('  FIREBASE_CREDENTIALS = ' . ($credentials ?: '(ยังไม่ได้ตั้งค่า)'));
        $this->line('  ไฟล์มีอยู่จริงไหม     = ' . (($credentials && file_exists($credentials)) ? 'มี ✅' : 'ไม่มี/หาไม่เจอ ❌'));
        $this->line('  FIREBASE_PROJECT_ID   = ' . ($projectId ?: '(ยังไม่ได้ตั้งค่า)'));
        $this->line('  แพ็กเกจ kreait/firebase-php = ' . (class_exists(\Kreait\Firebase\Factory::class) ? 'ติดตั้งแล้ว ✅' : 'ยังไม่ได้ composer install/update ❌'));

        // 2. เช็ค device token ของ user คนนี้
        $tokens = DB::table('device_tokens')->where('user_id', $userId)->get();
        $this->newLine();
        $this->info("Device tokens ของ user_id={$userId}: พบ {$tokens->count()} รายการ");
        foreach ($tokens as $t) {
            $shortToken = substr($t->token, 0, 24) . '...';
            $this->line("  - {$shortToken} (device_type: " . ($t->device_type ?? '-') . ", updated_at: {$t->updated_at})");
        }

        if ($tokens->isEmpty()) {
            $this->warn('ไม่มี device token ของ user นี้เลย ต้องให้แอปมือถือ login (พร้อมแนบ FCM token) หรือเรียก POST /api/user/device-token ก่อน ถึงจะทดสอบส่งได้');
            return self::FAILURE;
        }

        // 3. ยิงทดสอบจริง
        $this->newLine();
        $this->info('กำลังส่ง push notification ทดสอบ...');

        $results = PushNotificationService::sendToUser(
            $userId,
            $this->option('title'),
            $this->option('body'),
            ['test' => 'true']
        );

        $this->newLine();

        if (empty($results)) {
            $this->error('ไม่มีผลลัพธ์กลับมาเลย (ไม่ได้ยิงส่งจริง) — เช็คหัวข้อ "ตรวจสอบการตั้งค่า Firebase" ด้านบนอีกครั้งว่ามีอะไรเป็น ❌ หรือไม่');
            return self::FAILURE;
        }

        $successCount = 0;
        foreach ($results as $r) {
            $shortToken = substr($r['token'], 0, 24) . '...';
            if ($r['success']) {
                $successCount++;
                $this->info("  ✅ สำเร็จ: {$shortToken} (message_id: {$r['message_id']})");
            } else {
                $this->error("  ❌ ล้มเหลว: {$shortToken} — {$r['error']}");
                if (isset($r['error_class'], $r['error_location'])) {
                    $this->line("     (exception: {$r['error_class']} ที่ {$r['error_location']})");
                }
            }
        }

        $this->newLine();
        if ($successCount === count($results)) {
            $this->info('Firebase รับข้อความไปส่งต่อเรียบร้อยแล้วทุก token (server-side สำเร็จ 100%)');
            $this->line('ถ้ามือถือยังไม่มี notification เด้งขึ้นมา ให้เช็คฝั่งแอป/อุปกรณ์แทน เช่น:');
            $this->line('  - แอปเปิดสิทธิ์แจ้งเตือน (notification permission) ไว้หรือไม่');
            $this->line('  - ถ้าแอปเปิดอยู่หน้าจอ (foreground) ตอนทดสอบ บางแอปต้องเขียนโค้ดแสดง notification เองจาก onMessageReceived ไม่ได้เด้งอัตโนมัติเหมือนตอนแอปอยู่เบื้องหลัง/ปิดอยู่ — ลองปิดแอปแล้วทดสอบซ้ำดู');
            $this->line('  - token ที่ทดสอบเป็นของเครื่อง/แอปเวอร์ชันที่ยังใช้งานอยู่จริงหรือไม่');
        } else {
            $this->warn('มีบาง token ที่ส่งไม่สำเร็จ ดูข้อความ error ด้านบนประกอบ (เช่น token หมดอายุ/ไม่ถูกต้อง ก็จะขึ้น error บอกตรงนี้เลย)');
        }

        return self::SUCCESS;
    }
}
