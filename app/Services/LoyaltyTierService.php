<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 🌟 ระดับสมาชิก (Tier) + การเพิ่ม/หักแต้ม — ใช้ร่วมกันทั้งหลังบ้าน หน้าเว็บ และ API
 *
 * กติกา
 * 1) ตัดสินจาก "แต้มที่ได้รับสะสม" ในรอบ 12 เดือนล่าสุด (นับ type earn/adjust — การแลกของรางวัลไม่ทำให้ Tier ตก)
 *    ตามเกณฑ์ loyalty_tiers.min_points (ค่าเริ่มต้น Advance 0 / Platinum 2,000 / Beyond 10,000)
 * 2) หรือ "ยอดซื้อ" ในรอบ 12 เดือน ถึง loyalty_tiers.min_spending → ขึ้น Tier ได้ทันที
 *    ยอดซื้อ = ออเดอร์ที่ชำระแล้ว + ใบแจ้งหนี้รายเดือน (สัญญา) ที่ชำระแล้ว + การจองตู้เซฟที่ชำระแล้ว
 *    (ใบสรุปยอด statement ไม่นับซ้ำ เพราะออเดอร์ในใบนั้นนับไปแล้ว)
 * 3) ขึ้น Tier ทันทีเมื่อถึงเกณฑ์ → สถานะอยู่ได้ 1 ปีนับจากวันเปลี่ยน
 * 4) ครบ 1 ปี → ประเมินใหม่จากรอบ 12 เดือนล่าสุด (ถึง = ต่ออายุอีก 1 ปี, ไม่ถึง = ลดเป็นระดับที่ถึง)
 */
class LoyaltyTierService
{
    public const VALID_MONTHS = 12;
    public const WINDOW_MONTHS = 12;

    public const PAID_ORDER_STATUSES = ['paid', 'processing', 'completed'];
    public const PAID_LOCKER_STATUSES = ['paid', 'active', 'completed', 'expired'];

    /** Tier ทั้งหมด เรียงจากต่ำ → สูง */
    public static function tiers()
    {
        static $tiers = null;
        if ($tiers === null) {
            $tiers = DB::table('loyalty_tiers')
                ->orderByRaw('COALESCE(min_points, 0) asc')
                ->orderByRaw('COALESCE(min_spending, 0) asc')
                ->orderBy('id')
                ->get()->values();
        }
        return $tiers;
    }

    public static function rankOf($tierId): int
    {
        foreach (self::tiers() as $i => $t) {
            if ((int) $t->id === (int) $tierId) return $i;
        }
        return 0;
    }

    public static function windowStart(): Carbon
    {
        return now()->subMonths(self::WINDOW_MONTHS);
    }

    /** แต้มที่ได้รับสะสมในรอบ 12 เดือน (หักการปรับลดของแอดมิน ไม่หักการแลกของรางวัล) */
    public static function pointsInWindow(int $userId): int
    {
        return (int) DB::table('point_transactions')
            ->where('user_id', $userId)
            ->whereIn('type', ['earn', 'adjust'])
            ->where('created_at', '>=', self::windowStart())
            ->sum('amount');
    }

    /** ยอดซื้อในรอบ 12 เดือน */
    public static function spendingInWindow(int $userId): float
    {
        $from = self::windowStart();

        $orders = (float) DB::table('orders')->where('user_id', $userId)
            ->whereIn('status', self::PAID_ORDER_STATUSES)
            ->where('created_at', '>=', $from)
            ->sum('total_amount');

        $invoices = 0.0;
        try {
            $invoices = (float) DB::table('invoices')->where('user_id', $userId)
                ->where('type', 'contract')->where('status', 'paid')
                ->where('paid_at', '>=', $from)
                ->sum('total_amount');
        } catch (\Throwable $e) {
            // ยังไม่มีระบบใบแจ้งหนี้ในฐานข้อมูลนี้
        }

        $lockers = (float) DB::table('locker_bookings')->where('user_id', $userId)
            ->whereIn('status', self::PAID_LOCKER_STATUSES)
            ->where('created_at', '>=', $from)
            ->sum('total_amount');

        return round($orders + $invoices + $lockers, 2);
    }

    /** Tier ที่ถึงเกณฑ์ (สูงสุด) + เหตุผล ('points' | 'spending' | 'base') */
    public static function qualifyingTier(int $points, float $spending): array
    {
        $tiers = self::tiers();
        $best = $tiers->first();
        $by = 'base';
        foreach ($tiers as $t) {
            $byPoints = $t->min_points !== null && $points >= (int) $t->min_points;
            $bySpend = $t->min_spending !== null && (float) $t->min_spending > 0 && $spending >= (float) $t->min_spending;
            if ($byPoints || $bySpend) {
                $best = $t;
                $by = $byPoints ? 'points' : 'spending';
            }
        }
        return [$best, $by];
    }

    /** สร้างกระเป๋าแต้มให้ลูกค้าที่ยังไม่มี (Tier ต่ำสุด) */
    public static function ensureWallet(int $userId)
    {
        $wallet = DB::table('customer_wallets')->where('user_id', $userId)->first();
        if ($wallet) return $wallet;

        $base = self::tiers()->first();
        DB::table('customer_wallets')->insert([
            'user_id' => $userId,
            'current_tier_id' => $base->id ?? null,
            'current_points' => 0,
            'tier_started_at' => now(),
            'tier_expires_at' => now()->addMonths(self::VALID_MONTHS),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return DB::table('customer_wallets')->where('user_id', $userId)->first();
    }

    /**
     * ประเมิน Tier ของลูกค้า — เรียกหลังได้แต้ม/ชำระเงิน และตอนเปิดดูโปรไฟล์ (เช็คครบ 1 ปี)
     * @return array status (ดู status())
     */
    public static function evaluate(int $userId, ?int $changedBy = null): array
    {
        $wallet = self::ensureWallet($userId);
        $points = self::pointsInWindow($userId);
        $spending = self::spendingInWindow($userId);
        [$qualify, $by] = self::qualifyingTier($points, $spending);

        if ($qualify) {
            $currentRank = self::rankOf($wallet->current_tier_id);
            $qualifyRank = self::rankOf($qualify->id);
            $expires = $wallet->tier_expires_at ? Carbon::parse($wallet->tier_expires_at) : null;

            if ($qualifyRank > $currentRank) {
                // ⬆️ ถึงเกณฑ์ระดับที่สูงกว่า → ขึ้นทันที อยู่ได้ 1 ปี
                self::setTier($wallet, $qualify, $by === 'spending' ? 'upgrade_spending' : 'upgrade_points', $points, $spending, $changedBy);
            } elseif ($expires === null) {
                // ลูกค้าเดิมก่อนมีระบบนี้ → เริ่มนับอายุสถานะจากวันนี้
                DB::table('customer_wallets')->where('id', $wallet->id)->update([
                    'tier_started_at' => now(), 'tier_expires_at' => now()->addMonths(self::VALID_MONTHS), 'updated_at' => now(),
                ]);
                self::history($userId, $wallet->current_tier_id, $wallet->current_tier_id, 'init', $points, $spending, now()->addMonths(self::VALID_MONTHS), $changedBy);
            } elseif ($expires->lte(now())) {
                // ⏰ ครบ 1 ปี → ประเมินใหม่ (ถึงเกณฑ์เดิม = ต่ออายุ, ไม่ถึง = ลดระดับ)
                self::setTier($wallet, $qualify, $qualifyRank < $currentRank ? 'downgrade' : 'renew', $points, $spending, $changedBy);
            }
        }

        return self::status($userId, $points, $spending);
    }

    /** เรียก evaluate แบบไม่ให้ error ไปทำให้งานหลัก (ชำระเงิน/แจกแต้ม) พัง */
    public static function evaluateQuietly(?int $userId): void
    {
        if (!$userId) return;
        try {
            self::evaluate($userId);
        } catch (\Throwable $e) {
            Log::warning('[LoyaltyTier] evaluate user ' . $userId . ' failed: ' . $e->getMessage());
        }
    }

    private static function setTier($wallet, $tier, string $reason, int $points, float $spending, ?int $changedBy): void
    {
        $expires = now()->addMonths(self::VALID_MONTHS);
        DB::table('customer_wallets')->where('id', $wallet->id)->update([
            'current_tier_id' => $tier->id,
            'tier_started_at' => now(),
            'tier_expires_at' => $expires,
            'updated_at' => now(),
        ]);
        self::history($wallet->user_id, $wallet->current_tier_id, $tier->id, $reason, $points, $spending, $expires, $changedBy);

        if ((int) $wallet->current_tier_id !== (int) $tier->id) {
            try {
                $up = self::rankOf($tier->id) > self::rankOf($wallet->current_tier_id);
                CustomerNotificationService::notify(
                    (int) $wallet->user_id,
                    $up ? 'ยินดีด้วย! คุณได้เลื่อนระดับสมาชิก' : 'ระดับสมาชิกของคุณมีการเปลี่ยนแปลง',
                    'ระดับสมาชิกของคุณคือ ' . $tier->name . ' ใช้ได้ถึงวันที่ ' . $expires->format('d/m/Y'),
                    'privilege'
                );
            } catch (\Throwable $e) {
                Log::warning('[LoyaltyTier] notify failed: ' . $e->getMessage());
            }
        }
    }

    private static function history(int $userId, $fromId, $toId, string $reason, int $points, float $spending, $expires, ?int $changedBy, ?string $note = null): void
    {
        DB::table('customer_tier_histories')->insert([
            'user_id' => $userId,
            'from_tier_id' => $fromId,
            'to_tier_id' => $toId,
            'reason' => $reason,
            'points_12m' => $points,
            'spending_12m' => $spending,
            'expires_at' => $expires,
            'changed_by' => $changedBy,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * ข้อมูลระดับสมาชิกสำหรับแสดงผล (API / หน้าเว็บ / หลังบ้าน)
     */
    public static function status(int $userId, ?int $points = null, ?float $spending = null): array
    {
        $wallet = DB::table('customer_wallets')->where('user_id', $userId)->first();
        $points = $points ?? self::pointsInWindow($userId);
        $spending = $spending ?? self::spendingInWindow($userId);
        $tiers = self::tiers();
        $current = $wallet ? $tiers->firstWhere('id', $wallet->current_tier_id) : $tiers->first();
        $rank = $current ? self::rankOf($current->id) : 0;
        $next = $tiers->get($rank + 1);

        return [
            'tier_id' => $current->id ?? null,
            'tier_name' => $current->name ?? null,
            'tier_started_at' => $wallet->tier_started_at ?? null,
            'tier_expires_at' => $wallet->tier_expires_at ?? null,
            'current_points' => (int) ($wallet->current_points ?? 0), // แต้มคงเหลือที่ใช้แลกได้
            'points_12m' => $points,                                  // แต้มสะสมในรอบ 12 เดือน (ใช้ตัดสิน Tier)
            'spending_12m' => $spending,                              // ยอดซื้อในรอบ 12 เดือน
            'next_tier' => $next ? [
                'tier_id' => $next->id,
                'tier_name' => $next->name,
                'min_points' => $next->min_points !== null ? (int) $next->min_points : null,
                'points_needed' => $next->min_points !== null ? max(0, (int) $next->min_points - $points) : null,
                'min_spending' => $next->min_spending !== null && (float) $next->min_spending > 0 ? (float) $next->min_spending : null,
                'spending_needed' => $next->min_spending !== null && (float) $next->min_spending > 0 ? max(0, round((float) $next->min_spending - $spending, 2)) : null,
            ] : null,
            'tiers' => $tiers->map(fn ($t) => [
                'tier_id' => $t->id,
                'tier_name' => $t->name,
                'min_points' => $t->min_points !== null ? (int) $t->min_points : null,
                'min_spending' => $t->min_spending !== null && (float) $t->min_spending > 0 ? (float) $t->min_spending : null,
            ])->values()->all(),
        ];
    }

    /**
     * เพิ่ม/หักแต้มแบบรายคน (หลังบ้าน) หรือจากการนำเข้าไฟล์ — บันทึก point_transactions แล้วประเมิน Tier ใหม่
     * $amount บวก = เพิ่ม, ลบ = หัก (หักเกินแต้มคงเหลือไม่ได้)
     */
    public static function adjustPoints(int $userId, int $amount, string $description, ?int $adminId = null, ?string $referenceId = null, ?int $importBatchId = null): array
    {
        if ($amount === 0) {
            return ['ok' => false, 'message' => 'จำนวนแต้มต้องไม่เป็น 0'];
        }

        $result = DB::transaction(function () use ($userId, $amount, $description, $referenceId, $importBatchId) {
            $wallet = self::ensureWallet($userId);
            $wallet = DB::table('customer_wallets')->where('id', $wallet->id)->lockForUpdate()->first();

            if ($amount < 0 && $wallet->current_points + $amount < 0) {
                return ['ok' => false, 'message' => 'แต้มคงเหลือไม่พอให้หัก (คงเหลือ ' . number_format($wallet->current_points) . ' แต้ม)'];
            }

            DB::table('customer_wallets')->where('id', $wallet->id)->update([
                'current_points' => DB::raw('current_points + ' . (int) $amount),
                'updated_at' => now(),
            ]);

            $tx = [
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'adjust',
                'reference_id' => $referenceId,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($importBatchId) $tx['import_batch_id'] = $importBatchId;
            DB::table('point_transactions')->insert($tx);

            return ['ok' => true, 'balance' => $wallet->current_points + $amount];
        });

        if ($result['ok']) {
            $before = DB::table('customer_wallets')->where('user_id', $userId)->value('current_tier_id');
            $status = self::evaluate($userId, $adminId);
            $result['tier'] = $status;
            $result['tier_changed'] = (int) $before !== (int) $status['tier_id'];
            $result['message'] = ($amount > 0 ? 'เพิ่ม ' : 'หัก ') . number_format(abs($amount)) . ' แต้มเรียบร้อยแล้ว (คงเหลือ ' . number_format($result['balance']) . ' แต้ม)'
                . ($result['tier_changed'] ? ' · ระดับสมาชิกเปลี่ยนเป็น ' . $status['tier_name'] : '');
        }
        return $result;
    }

    public static function reasonLabel(string $reason): string
    {
        return [
            'upgrade_points' => 'เลื่อนระดับ (แต้มถึงเกณฑ์)',
            'upgrade_spending' => 'เลื่อนระดับ (ยอดซื้อถึงเกณฑ์)',
            'renew' => 'ครบ 1 ปี — ต่ออายุสถานะ',
            'downgrade' => 'ครบ 1 ปี — ปรับลดระดับ',
            'init' => 'เริ่มนับอายุสถานะ',
            'admin' => 'แอดมินปรับ',
        ][$reason] ?? $reason;
    }
}
