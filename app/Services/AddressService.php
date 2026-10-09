<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ลบที่อยู่ลูกค้า (ใช้ร่วมกันทั้ง API มือถือและหน้าเว็บ)
 * - ที่อยู่ที่เคยถูกอ้างอิงในออเดอร์/แจ้งซ่อม/จองตู้เซฟ/ของรางวัล → soft delete (ตั้ง deleted_at) เพื่อเก็บประวัติ
 * - ที่อยู่ที่ไม่เคยถูกใช้ → ลบจริง
 * - ถ้าลบที่อยู่หลัก (is_default) จะตั้งที่อยู่ล่าสุดที่เหลือเป็นที่อยู่หลักแทน
 */
class AddressService
{
    /** ตารางที่อาจอ้างอิง customer_addresses.id ผ่านคอลัมน์ address_id */
    private const REFERENCING_TABLES = ['orders', 'service_requests', 'locker_bookings', 'customer_reward_codes', 'quote_requests'];

    /**
     * @return string|null 'deleted' | 'archived' หรือ null ถ้าไม่พบที่อยู่
     */
    public static function delete(int $userId, int $addressId): ?string
    {
        return DB::transaction(function () use ($userId, $addressId) {
            $query = DB::table('customer_addresses')->where('id', $addressId)->where('user_id', $userId);
            if (Schema::hasColumn('customer_addresses', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }
            $address = $query->lockForUpdate()->first();
            if (!$address) return null;

            $inUse = false;
            foreach (self::REFERENCING_TABLES as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'address_id')
                    && DB::table($table)->where('address_id', $addressId)->exists()) {
                    $inUse = true;
                    break;
                }
            }

            if ($inUse && Schema::hasColumn('customer_addresses', 'deleted_at')) {
                DB::table('customer_addresses')->where('id', $addressId)->update([
                    'deleted_at' => now(),
                    'is_default' => false,
                    'updated_at' => now(),
                ]);
                $mode = 'archived';
            } else {
                DB::table('customer_addresses')->where('id', $addressId)->delete();
                $mode = 'deleted';
            }

            if (!empty($address->is_default)) {
                $next = self::activeQuery($userId)->orderByDesc('created_at')->first();
                if ($next) {
                    DB::table('customer_addresses')->where('id', $next->id)->update(['is_default' => true, 'updated_at' => now()]);
                }
            }

            return $mode;
        });
    }

    /** query ที่อยู่ที่ยังไม่ถูกลบของลูกค้า */
    public static function activeQuery(int $userId)
    {
        $query = DB::table('customer_addresses')->where('user_id', $userId);
        if (Schema::hasColumn('customer_addresses', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
        return $query;
    }
}
