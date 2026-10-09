<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ศูนย์รวม logic ของรางวัล EASE CLUB
 * - Tier ของลูกค้า (อ่านจาก customer_wallets.current_tier_id → loyalty_tiers) และการกรองสิทธิพิเศษตาม Tier (คอมเมนต์ข้อ 3)
 * - การแลกของรางวัล / สถานะคูปอง / การจัดส่ง (คอมเมนต์ข้อ 2)
 */
class RewardService
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_VOUCHER = 'voucher';
    public const TYPE_DISCOUNT = 'discount';

    public const TYPE_LABELS = [
        self::TYPE_PRODUCT => 'สินค้า (จัดส่ง)',
        self::TYPE_VOUCHER => 'วอยเชอร์ (แอดมินส่งรหัส)',
        self::TYPE_DISCOUNT => 'ส่วนลดในแอป',
    ];

    public const STATUS_LABELS = [
        'active' => 'ยังไม่ได้ใช้',
        'shipping_confirm' => 'ยืนยันการจัดส่ง',
        'processing' => 'กำลังดำเนินการ',
        'shipping' => 'กำลังจัดส่ง',
        'delivered' => 'จัดส่งสำเร็จ',
        'used' => 'ใช้แล้ว',
        'cancelled' => 'ยกเลิก',
    ];

    /** ลำดับสถานะสินค้าที่แอดมินเปลี่ยนได้ (จาก → ไป) — ตั้งแต่ processing เปลี่ยนได้จากหลังบ้านเท่านั้น */
    public const PRODUCT_ADMIN_TRANSITIONS = [
        'shipping_confirm' => ['processing'],
        'processing' => ['shipping'],
        'shipping' => ['delivered'],
    ];

    // ==========================================
    // Tier
    // ==========================================

    /** รายชื่อ Tier เรียงจากต่ำไปสูง (ตาม min_spending) → ['advance' => 0, 'platinum' => 1, 'beyond' => 2] */
    public static function tierRanks(): array
    {
        static $ranks = null;
        if ($ranks === null) {
            $ranks = DB::table('loyalty_tiers')->orderBy('min_spending')->orderBy('id')->pluck('name')
                ->values()->mapWithKeys(fn ($name, $i) => [strtolower(trim($name)) => $i])->all();
        }
        return $ranks;
    }

    /** Tier ปัจจุบันของลูกค้าจาก customer_wallets (ไม่มี wallet = Tier ต่ำสุด) */
    public static function userTier(?int $userId): ?object
    {
        if (!$userId) return null;

        $tier = DB::table('customer_wallets')
            ->join('loyalty_tiers', 'customer_wallets.current_tier_id', '=', 'loyalty_tiers.id')
            ->where('customer_wallets.user_id', $userId)
            ->select('loyalty_tiers.id', 'loyalty_tiers.name')
            ->first();

        return $tier ?: DB::table('loyalty_tiers')->orderBy('min_spending')->orderBy('id')->select('id', 'name')->first();
    }

    public static function tierRank(?string $tierName): int
    {
        if ($tierName === null || trim($tierName) === '') return 0;
        return self::tierRanks()[strtolower(trim($tierName))] ?? 0;
    }

    /**
     * จำกัด query ของ rewards ให้เหลือเฉพาะรายการที่ Tier ของลูกค้าถึง (minimum_tier_required <= tier ลูกค้า)
     * - ไม่ล็อกอิน (guest) → เห็นเฉพาะรางวัลที่ไม่กำหนด Tier หรือกำหนดเป็น Tier ต่ำสุด
     * - รางวัลที่ minimum_tier_required ว่าง = ทุก Tier เห็น
     */
    public static function applyTierFilter($query, ?int $userId, string $column = 'rewards.minimum_tier_required')
    {
        $rank = self::tierRank(optional(self::userTier($userId))->name);
        $allowed = collect(self::tierRanks())->filter(fn ($r) => $r <= $rank)->keys()->all();

        return $query->where(function ($q) use ($column, $allowed) {
            $q->whereNull($column)->orWhere($column, '');
            if (!empty($allowed)) {
                $q->orWhereIn(DB::raw("LOWER(TRIM($column))"), $allowed);
            }
        });
    }

    public static function canAccessReward(object $reward, ?int $userId): bool
    {
        if (empty($reward->minimum_tier_required)) return true;
        // ชื่อ Tier ที่ไม่มีในตาราง loyalty_tiers → ซ่อน (ให้ตรงกับ applyTierFilter)
        if (!array_key_exists(strtolower(trim($reward->minimum_tier_required)), self::tierRanks())) return false;
        return self::tierRank($reward->minimum_tier_required) <= self::tierRank(optional(self::userTier($userId))->name);
    }

    // ==========================================
    // ประเภทรางวัล / การแสดงผลคูปอง
    // ==========================================

    public static function rewardType(object $reward): string
    {
        $type = $reward->reward_type ?? null;
        if (in_array($type, [self::TYPE_PRODUCT, self::TYPE_VOUCHER, self::TYPE_DISCOUNT], true)) {
            return $type;
        }
        // fallback ตาม logic เดิม (ก่อนมีคอลัมน์ reward_type)
        return ($reward->category_id == 1 && ($reward->discount_amount ?? 0) > 0) ? self::TYPE_DISCOUNT : self::TYPE_PRODUCT;
    }

    /** แปลงแถว customer_reward_codes (join rewards แล้ว) เป็นรูปแบบที่ส่งให้แอป */
    public static function presentCode(object $row, bool $withLogs = false): array
    {
        $type = $row->reward_type ?? self::TYPE_PRODUCT;
        $status = $row->status;

        // รหัสที่ให้ลูกค้าเห็น: วอยเชอร์ = รหัสที่แอดมินกรอก (ยังไม่กรอก = null), ส่วนลด = รหัสที่แอดมินกรอกทับ หรือ RWD-xxxx เดิม
        $displayCode = match ($type) {
            self::TYPE_VOUCHER => $row->voucher_code ?: null,
            self::TYPE_DISCOUNT => $row->voucher_code ?: $row->code,
            default => null,
        };

        $canUse = $status === 'active' && match ($type) {
            self::TYPE_VOUCHER => !empty($row->voucher_code),
            default => true,
        };

        $address = null;
        if (!empty($row->address_id)) {
            $address = DB::table('customer_addresses')->where('id', $row->address_id)->first();
        }

        $data = [
            'id' => $row->id,
            'reward_id' => $row->reward_id,
            'reward_title' => $row->reward_title ?? null,
            'reward_title_en' => $row->reward_title_en ?? null,
            'image_url' => $row->image_url ?? null,
            'category_id' => $row->category_id ?? null,
            'reward_type' => $type,
            'reward_type_label' => self::TYPE_LABELS[$type] ?? $type,
            'is_coupon' => $type !== self::TYPE_PRODUCT,
            'code' => $row->code, // เลขอ้างอิงการแลก (RWD-xxxx)
            'display_code' => $displayCode, // รหัสที่ให้แสดงบนแอป (null = ยังไม่มีรหัส)
            'voucher_code' => $row->voucher_code ?? null,
            'voucher_note' => $row->voucher_note ?? null,
            'is_waiting_code' => $type === self::TYPE_VOUCHER && empty($row->voucher_code) && $status === 'active',
            'discount_amount' => (float) ($row->discount_amount ?? 0),
            'status' => $status,
            'status_label' => self::STATUS_LABELS[$status] ?? $status,
            'can_use' => $canUse,
            'redeemed_date' => $row->redeemed_date ?? $row->created_at ?? null,
            'requested_at' => $row->requested_at ?? null,
            'used_at' => $row->used_at ?? null,
            'delivered_at' => $row->delivered_at ?? null,
            'shipping' => $type === self::TYPE_PRODUCT ? [
                'customer_name' => $row->customer_name ?? null,
                'customer_phone' => $row->customer_phone ?? null,
                'address_id' => $row->address_id ?? null,
                'address_text' => $row->address_text ?? null,
                'address' => $address,
                'carrier' => $row->shipping_carrier ?? null,
                'tracking_number' => $row->tracking_number ?? null,
            ] : null,
            // คงชื่อ field เดิมไว้เพื่อไม่ให้แอปเวอร์ชันเก่าพัง
            'customer_name' => $row->customer_name ?? null,
            'customer_phone' => $row->customer_phone ?? null,
            'address_id' => $row->address_id ?? null,
            'address_text' => $row->address_text ?? null,
        ];

        if ($withLogs) {
            $data['timeline'] = DB::table('customer_reward_code_logs')
                ->where('customer_reward_code_id', $row->id)
                ->orderBy('created_at')
                ->get(['status', 'note', 'actor', 'created_at'])
                ->map(fn ($log) => [
                    'status' => $log->status,
                    'status_label' => self::STATUS_LABELS[$log->status] ?? $log->status,
                    'note' => $log->note,
                    'actor' => $log->actor,
                    'created_at' => $log->created_at,
                ]);
        }

        return $data;
    }

    /** query พื้นฐาน customer_reward_codes + rewards สำหรับ API/หลังบ้าน */
    public static function codesQuery()
    {
        return DB::table('customer_reward_codes')
            ->join('rewards', 'customer_reward_codes.reward_id', '=', 'rewards.id')
            ->select(
                'customer_reward_codes.*',
                'customer_reward_codes.created_at as redeemed_date',
                'rewards.category_id',
                'rewards.reward_type',
                'rewards.title_th as reward_title',
                'rewards.title_en as reward_title_en',
                'rewards.image_url'
            );
    }

    public static function log(int $codeId, string $status, ?string $note = null, string $actor = 'admin', ?int $changedBy = null): void
    {
        DB::table('customer_reward_code_logs')->insert([
            'customer_reward_code_id' => $codeId,
            'status' => $status,
            'note' => $note,
            'actor' => $actor,
            'changed_by' => $changedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ==========================================
    // แลกของรางวัล (ใช้ร่วมกันทั้ง /rewards/redeem และ /ease-club/rewards/{id}/redeem)
    // ==========================================

    /**
     * @return array{ok: bool, status: int, message: string, data?: array}
     */
    public static function redeem(int $userId, int $rewardId, array $input): array
    {
        $reward = DB::table('rewards')->where('id', $rewardId)->where('is_active', true)->first();
        if (!$reward) {
            return ['ok' => false, 'status' => 404, 'message' => 'ไม่พบของรางวัลนี้'];
        }

        // 🌟 สิทธิพิเศษผูกกับ Tier ของลูกค้า (customer_wallets.current_tier_id)
        if (!self::canAccessReward($reward, $userId)) {
            return ['ok' => false, 'status' => 403, 'message' => 'ของรางวัลนี้สำหรับสมาชิกระดับ ' . $reward->minimum_tier_required . ' ขึ้นไปเท่านั้น'];
        }

        $type = self::rewardType($reward);
        $isCoupon = $type !== self::TYPE_PRODUCT;

        // address_id ต้องเป็นที่อยู่ของลูกค้าคนนี้เท่านั้น (presentCode แสดงที่อยู่เต็มจาก address_id)
        if (!$isCoupon && !empty($input['address_id'])
            && !AddressService::activeQuery($userId)->where('id', $input['address_id'])->exists()) {
            return ['ok' => false, 'status' => 422, 'message' => 'ไม่พบที่อยู่ที่เลือก'];
        }

        DB::beginTransaction();
        try {
            // ล็อกแถว wallet กันการกดยืนยันซ้ำ/ยิงพร้อมกันจนแต้มติดลบ
            $wallet = DB::table('customer_wallets')->where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet || $wallet->current_points < $reward->points_required) {
                DB::rollBack();
                return ['ok' => false, 'status' => 400, 'message' => 'คะแนน EASE Coins ของคุณไม่เพียงพอ'];
            }

            DB::table('customer_wallets')->where('user_id', $userId)->decrement('current_points', $reward->points_required);

            DB::table('point_transactions')->insert([
                'user_id' => $userId,
                'amount' => ($reward->points_required * -1),
                'type' => 'redeem',
                'description' => 'แลกรับของรางวัล: ' . $reward->title_th,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('reward_redemptions')->insert([
                'user_id' => $userId,
                'reward_id' => $reward->id,
                'points_used' => $reward->points_required,
                'status' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $code = 'RWD-' . strtoupper(Str::random(8));
            $codeId = DB::table('customer_reward_codes')->insertGetId([
                'user_id' => $userId,
                'reward_id' => $reward->id,
                'code' => $code,
                'discount_amount' => $type === self::TYPE_DISCOUNT ? $reward->discount_amount : 0,
                'status' => 'active',
                // สินค้า: เก็บที่อยู่ที่ส่งมาตอนแลกไว้ก่อน (แก้ไขได้อีกครั้งตอนกด "ใช้คูปอง")
                'customer_name' => $isCoupon ? null : ($input['customer_name'] ?? null),
                'customer_phone' => $isCoupon ? null : ($input['customer_phone'] ?? null),
                'address_id' => $isCoupon ? null : ($input['address_id'] ?? null),
                'address_text' => $isCoupon ? null : ($input['address_text'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            self::log($codeId, 'active', 'แลกของรางวัล ใช้ ' . $reward->points_required . ' แต้ม', 'customer', $userId);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['ok' => false, 'status' => 500, 'message' => 'เกิดข้อผิดพลาดในการแลกของรางวัล: ' . $e->getMessage()];
        }

        return ['ok' => true, 'status' => 200, 'message' => 'แลกของรางวัลสำเร็จ', 'data' => [
            'id' => $codeId,
            'code' => $code,
            'display_code' => $type === self::TYPE_DISCOUNT ? $code : null,
            'discount_amount' => $type === self::TYPE_DISCOUNT ? (float) $reward->discount_amount : 0,
            'reward_title' => $reward->title_th,
            'reward_type' => $type,
            'reward_point' => $wallet->current_points - $reward->points_required,
            'is_coupon' => $isCoupon,
            'status' => 'active',
        ]];
    }

    /**
     * ลูกค้ากด "ใช้คูปอง" ในแอป
     * - สินค้า: active → shipping_confirm (ยืนยันการจัดส่ง) พร้อมที่อยู่ที่จะให้แอดมินตรวจสอบ
     * - วอยเชอร์/ส่วนลด: active → used (วอยเชอร์ต้องได้รับรหัสจากแอดมินก่อน)
     *
     * @return array{ok: bool, status: int, message: string}
     */
    public static function customerUse(int $userId, int $codeId, array $input): array
    {
        return DB::transaction(function () use ($userId, $codeId, $input) {
            $row = DB::table('customer_reward_codes')->where('id', $codeId)->where('user_id', $userId)->lockForUpdate()->first();
            if (!$row) return ['ok' => false, 'status' => 404, 'message' => 'ไม่พบคูปองนี้'];

            $reward = DB::table('rewards')->where('id', $row->reward_id)->first();
            $type = $reward ? self::rewardType($reward) : self::TYPE_PRODUCT;

            if ($row->status !== 'active') {
                return ['ok' => false, 'status' => 400, 'message' => 'คูปองนี้ถูกใช้งานไปแล้ว (สถานะ: ' . (self::STATUS_LABELS[$row->status] ?? $row->status) . ')'];
            }

            if ($type === self::TYPE_PRODUCT) {
                $update = [
                    'customer_name' => $input['customer_name'] ?? $row->customer_name,
                    'customer_phone' => $input['customer_phone'] ?? $row->customer_phone,
                    // ส่งมาแค่ address_text (พิมพ์ที่อยู่เอง) → ไม่ใช้ address_id เดิม
                    'address_id' => array_key_exists('address_text', $input) && !array_key_exists('address_id', $input)
                        ? null : ($input['address_id'] ?? $row->address_id),
                    'address_text' => array_key_exists('address_id', $input) && !array_key_exists('address_text', $input)
                        ? null : ($input['address_text'] ?? $row->address_text),
                ];

                if (!empty($update['address_id'])) {
                    $addr = AddressService::activeQuery($userId)->where('id', $update['address_id'])->first();
                    if (!$addr) return ['ok' => false, 'status' => 422, 'message' => 'ไม่พบที่อยู่ที่เลือก'];
                    // เลือกที่อยู่จากสมุดที่อยู่ แต่ไม่ได้ส่งชื่อ/เบอร์มา → ใช้ผู้ติดต่อของที่อยู่นั้น
                    $update['customer_name'] = $update['customer_name'] ?: ($addr->contact_name ?? null);
                    $update['customer_phone'] = $update['customer_phone'] ?: ($addr->contact_phone ?? null);
                }
                if (empty($update['address_id']) && empty($update['address_text'])) {
                    return ['ok' => false, 'status' => 422, 'message' => 'กรุณาระบุที่อยู่สำหรับจัดส่งของรางวัล'];
                }
                if (empty($update['customer_name']) || empty($update['customer_phone'])) {
                    return ['ok' => false, 'status' => 422, 'message' => 'กรุณาระบุชื่อและเบอร์โทรศัพท์ผู้รับ'];
                }

                DB::table('customer_reward_codes')->where('id', $row->id)->update($update + [
                    'status' => 'shipping_confirm',
                    'requested_at' => now(),
                    'updated_at' => now(),
                ]);
                self::log($row->id, 'shipping_confirm', 'ลูกค้ากดใช้คูปอง รอเจ้าหน้าที่ตรวจสอบที่อยู่จัดส่ง', 'customer', $userId);

                return ['ok' => true, 'status' => 200, 'message' => 'ส่งคำขอจัดส่งเรียบร้อยแล้ว เจ้าหน้าที่จะตรวจสอบที่อยู่และดำเนินการจัดส่ง'];
            }

            if ($type === self::TYPE_VOUCHER && empty($row->voucher_code)) {
                return ['ok' => false, 'status' => 400, 'message' => 'กรุณารอเจ้าหน้าที่ส่งรหัสคูปองให้ก่อน'];
            }

            DB::table('customer_reward_codes')->where('id', $row->id)->update([
                'status' => 'used',
                'used_at' => now(),
                'updated_at' => now(),
            ]);
            self::log($row->id, 'used', 'ลูกค้ากดใช้คูปอง', 'customer', $userId);

            return ['ok' => true, 'status' => 200, 'message' => 'ใช้คูปองเรียบร้อยแล้ว'];
        });
    }
}
