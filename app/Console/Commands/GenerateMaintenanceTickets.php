<?php

namespace App\Console\Commands;

use App\Services\MaintenancePlanService;
use Illuminate\Console\Command;

/**
 * 🌟 เปิดใบงานบำรุงรักษาตามรอบ (รันทุกวันจาก routes/console.php)
 *   php artisan maintenance:generate-tickets
 */
class GenerateMaintenanceTickets extends Command
{
    protected $signature = 'maintenance:generate-tickets';
    protected $description = 'เปิดใบงานบำรุงรักษาตามแผนของลูกค้าที่ถึงช่วงเปิดล่วงหน้า';

    public function handle(): int
    {
        $r = MaintenancePlanService::generateDue();
        $this->info("เปิดใบงานใหม่ {$r['created']} ใบ (ข้าม {$r['skipped']} แผน)");
        foreach ($r['errors'] as $e) $this->error($e);
        return self::SUCCESS;
    }
}
