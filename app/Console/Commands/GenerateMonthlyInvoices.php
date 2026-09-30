<?php

namespace App\Console\Commands;

use App\Services\InvoiceGenerationService;
use Illuminate\Console\Command;

/**
 * 🌟 คำสั่งออกใบแจ้งหนี้รายเดือนอัตโนมัติ — ตั้ง schedule ให้รันทุกวันใน bootstrap/app.php (->withSchedule)
 * รันทุกวันเพราะสัญญาแต่ละอันมี billing_day (วันออกบิล) ไม่ตรงกัน และเผื่อ server ล่มในวันที่ควรรัน
 * ก็ยังไล่ตามออกบิลให้ในวันถัดไปของเดือนเดียวกันได้ (ดูตรรกะใน InvoiceGenerationService)
 *
 * วิธีใช้:
 *   php artisan invoices:generate                 → โหมดปกติ (cron) ดูวันที่ปัจจุบันเองว่าสัญญา/ลูกค้าคนไหนถึงกำหนดบิล
 *   php artisan invoices:generate --month=2026-09  → สร้างของเดือนที่ระบุตรงๆ (backfill/สร้างย้อนหลัง) ข้ามการเช็ค billing_day
 */
class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate {--month= : ระบุเดือนที่ต้องการสร้างย้อนหลัง รูปแบบ YYYY-MM เช่น 2026-09}';

    protected $description = 'สร้างใบแจ้งหนี้รายเดือนอัตโนมัติ (สัญญารายเดือน + สรุปยอดลูกค้าวางบิล) พร้อมออกเลขที่ใบแจ้งหนี้เรียงลำดับ';

    public function handle(InvoiceGenerationService $service): int
    {
        $month = $this->option('month');

        if ($month && !preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('รูปแบบ --month ไม่ถูกต้อง ต้องเป็น YYYY-MM เช่น 2026-09');
            return self::FAILURE;
        }

        $this->info('เริ่มสร้างใบแจ้งหนี้รายเดือน' . ($month ? " สำหรับเดือน {$month}" : ' (โหมด cron รายวัน)') . ' ...');

        $result = $service->generateForMonth($month, 'cron');

        $this->info("สัญญารายเดือนที่ออกบิลใหม่: {$result['contract_generated']} ใบ");
        $this->info("ใบสรุปยอดลูกค้าวางบิลที่ออกใหม่: {$result['statement_generated']} ใบ");
        $this->info("ข้าม (ออกบิลไปแล้ว/ยังไม่ถึงกำหนด): {$result['skipped']}");

        if (!empty($result['errors'])) {
            $this->error('พบข้อผิดพลาด ' . count($result['errors']) . ' รายการ:');
            foreach ($result['errors'] as $err) {
                $this->error('  - ' . $err);
            }
            return self::FAILURE;
        }

        $this->info('เสร็จสิ้น ✅');
        return self::SUCCESS;
    }
}
