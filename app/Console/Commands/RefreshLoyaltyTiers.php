<?php

namespace App\Console\Commands;

use App\Services\LoyaltyTierService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 🌟 ประเมินระดับสมาชิกของลูกค้าที่สถานะครบ 1 ปีแล้ว (ตั้งให้รันทุกวันใน routes/console.php)
 *   php artisan loyalty:refresh-tiers         → เฉพาะที่ครบกำหนด / ยังไม่เคยตั้งวันหมดอายุ
 *   php artisan loyalty:refresh-tiers --all   → ประเมินทุกคน (ใช้ครั้งแรกหลังติดตั้ง หรือหลังแก้เกณฑ์)
 */
class RefreshLoyaltyTiers extends Command
{
    protected $signature = 'loyalty:refresh-tiers {--all : ประเมินลูกค้าทุกคน}';
    protected $description = 'ประเมินระดับสมาชิก (Advance / Platinum / Beyond) ของลูกค้าที่ครบกำหนด';

    public function handle(): int
    {
        $query = DB::table('customer_wallets')->select('user_id');
        if (!$this->option('all')) {
            $query->where(fn ($q) => $q->whereNull('tier_expires_at')->orWhere('tier_expires_at', '<=', now()));
        }

        $count = 0;
        $query->orderBy('user_id')->chunk(200, function ($rows) use (&$count) {
            foreach ($rows as $row) {
                LoyaltyTierService::evaluateQuietly((int) $row->user_id);
                $count++;
            }
        });

        $this->info("ประเมินระดับสมาชิกแล้ว {$count} คน");
        return self::SUCCESS;
    }
}
