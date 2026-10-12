<?php

namespace App\Support;

/**
 * 🌟 เลือกข้อความจากฐานข้อมูลตามภาษาของหน้าเว็บ (session app_locale)
 *
 *   Loc::f($product, 'name')         → name_en (ถ้าภาษาอังกฤษและมีค่า) ไม่งั้น name_th
 *   Loc::f($category, 'name')        → reward_categories: name_en / name
 *   Loc::f($insurance, 'description') → description_en / description_th
 *
 * ถ้าฝั่งภาษาอังกฤษยังว่าง จะ fallback เป็นภาษาไทยเสมอ (หน้าเว็บไม่โชว์ช่องว่าง)
 * รองรับทั้ง object (stdClass / Model) และ array
 */
class Loc
{
    public static function isEn(): bool
    {
        return str_starts_with((string) app()->getLocale(), 'en');
    }

    public static function f($row, string $base, $default = null)
    {
        if ($row === null) {
            return $default;
        }

        $en = self::get($row, $base . '_en');
        $th = self::get($row, $base . '_th');
        if (self::blank($th)) {
            $th = self::get($row, $base);
        }

        if (self::isEn() && !self::blank($en)) {
            return $en;
        }
        if (!self::blank($th)) {
            return $th;
        }
        return !self::blank($en) ? $en : $default;
    }

    /** @var array<string, object|null> */
    private static array $masterCache = [];

    /**
     * ชื่อของแพ็กเกจ/บริการที่ลูกค้าซื้อแล้ว (customer_products เก็บชื่อภาษาไทยไว้ตอนซื้อ)
     * → ถ้าหน้าเว็บเป็นภาษาอังกฤษ ดึงชื่อ EN จากตารางหลัก (products / insurances / smart_lockers) ตาม reference_type
     */
    public static function customerProductName($pkg, $default = null)
    {
        $name = is_array($pkg) ? ($pkg['product_name'] ?? null) : ($pkg->product_name ?? null);
        if (!self::isEn()) {
            return self::blank($name) ? $default : $name;
        }

        $type = (is_array($pkg) ? ($pkg['reference_type'] ?? null) : ($pkg->reference_type ?? null)) ?: 'product';
        $refId = is_array($pkg) ? ($pkg['reference_id'] ?? null) : ($pkg->reference_id ?? null);
        [$table, $base] = match ($type) {
            'insurance' => ['insurances', 'title'],
            'locker' => ['smart_lockers', 'title'],
            default => ['products', 'name'],
        };

        if ($refId) {
            $key = $table . ':' . $refId;
            if (!array_key_exists($key, self::$masterCache)) {
                self::$masterCache[$key] = \Illuminate\Support\Facades\DB::table($table)->where('id', $refId)->first();
            }
            $master = self::$masterCache[$key];
            $en = $master ? self::get($master, $base . '_en') : null;
            if (!self::blank($en)) {
                return $en;
            }
        }

        return self::blank($name) ? $default : $name;
    }

    private static function get($row, string $key)
    {
        if (is_array($row)) {
            return $row[$key] ?? null;
        }
        return $row->{$key} ?? null;
    }

    private static function blank($v): bool
    {
        return $v === null || (is_string($v) && trim($v) === '');
    }
}
