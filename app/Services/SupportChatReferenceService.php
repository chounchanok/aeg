<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * 🌟 แนบ "สินค้า / บริการ / แพ็กเกจของฉัน / ของรางวัล / ออเดอร์ / งานแจ้งซ่อม" ไปกับข้อความแชทติดต่อสอบถาม
 *
 * แอป/เว็บส่ง ref_type + ref_id มากับ POST /support-chats/send → ระบบตรวจสิทธิ์ แล้วเก็บ "snapshot" ของรายการ
 * (ชื่อ รูป รหัส สถานะ ณ ตอนส่ง) ไว้ที่ support_chats.ref_snapshot — ประวัติแชทจึงแสดงการ์ดได้เสมอ
 * แม้ภายหลังสินค้าจะถูกแก้ชื่อหรือลบไปแล้ว
 */
class SupportChatReferenceService
{
    /** ref_type ที่รองรับ => หัวข้อแชทที่ใช้อัตโนมัติเมื่อแอปไม่ได้ส่ง topic มา */
    public const TYPES = [
        'product' => 'security-system',          // สินค้า/แพ็กเกจในแคตตาล็อก (products)
        'insurance' => 'insurance',              // ประกันในแคตตาล็อก (insurances)
        'locker' => 'locker',                    // ตู้เซฟในแคตตาล็อก (smart_lockers)
        'customer_product' => null,              // แพ็กเกจ/บริการที่ลูกค้าซื้อแล้ว (customer_products) — หัวข้อตามประเภท
        'reward' => 'ease-club',                 // ของรางวัล EASE CLUB (rewards)
        'reward_code' => 'ease-club',            // คูปอง/ของรางวัลที่ลูกค้าแลกแล้ว (customer_reward_codes)
        'order' => 'general',                    // ออเดอร์ (orders)
        'service_request' => 'technician-service', // งานแจ้งซ่อม/เรียกช่าง (service_requests)
        'locker_booking' => 'locker',            // การจองตู้เซฟ (locker_bookings)
    ];

    public static function types(): array
    {
        return array_keys(self::TYPES);
    }

    /** หัวข้อแชทที่เหมาะกับรายการนี้ (ใช้เมื่อแอปไม่ได้ส่ง topic) */
    public static function topicFor(string $type, array $snapshot): string
    {
        if ($type === 'customer_product') {
            return match ($snapshot['package_type'] ?? 'product') {
                'insurance' => 'insurance',
                'locker' => 'locker',
                default => 'security-system',
            };
        }
        return self::TYPES[$type] ?? SupportChatTopicService::defaultTopic();
    }

    /**
     * ดึงข้อมูลรายการที่แนบ — คืน null ถ้าไม่พบ หรือเป็นรายการส่วนตัวที่ไม่ใช่ของลูกค้าคนนี้
     */
    public static function resolve(string $type, int $id, int $userId): ?array
    {
        switch ($type) {
            case 'product':
                $r = DB::table('products')->where('id', $id)->first();
                return $r ? self::card($type, $id, $r->name_th, $r->name_en ?? null, $r->image_url ?? null,
                    isset($r->price) ? '฿' . number_format((float) $r->price, 2) : null) : null;

            case 'insurance':
                $r = DB::table('insurances')->where('id', $id)->first();
                return $r ? self::card($type, $id, $r->title_th, $r->title_en ?? null, $r->image_url ?? null) : null;

            case 'locker':
                $r = DB::table('smart_lockers')->where('id', $id)->first();
                return $r ? self::card($type, $id, $r->title_th ?? ('Locker ' . ($r->locker_number ?? $id)), $r->title_en ?? null,
                    $r->image_url ?? null, !empty($r->locker_number) ? 'Locker ' . $r->locker_number : null) : null;

            case 'customer_product':
                $r = DB::table('customer_products')->where('id', $id)->where('customer_id', $userId)->first();
                if (!$r) return null;
                $refType = $r->reference_type ?? 'product';
                $master = null;
                if (!empty($r->reference_id)) {
                    $table = ['product' => 'products', 'insurance' => 'insurances', 'locker' => 'smart_lockers'][$refType] ?? null;
                    $master = $table ? DB::table($table)->where('id', $r->reference_id)->first() : null;
                }
                return self::card($type, $id, $r->product_name, $master->name_en ?? $master->title_en ?? null,
                    $r->image_url ?? $master->image_url ?? null,
                    !empty($r->serial_number) ? 'S/N ' . $r->serial_number : null,
                    $r->status ?? null,
                    [
                        'package_type' => $refType,
                        'reference_id' => $r->reference_id ?? null,
                        'serial_number' => $r->serial_number ?? null,
                        'warranty_expire_date' => $r->warranty_expire_date ?? null,
                    ]);

            case 'reward':
                $r = DB::table('rewards')->where('id', $id)->first();
                return $r ? self::card($type, $id, $r->title_th, $r->title_en ?? null, $r->image_url ?? null,
                    isset($r->points_required) ? number_format((int) $r->points_required) . ' points' : null,
                    null, ['reward_type' => $r->reward_type ?? null]) : null;

            case 'reward_code':
                $r = DB::table('customer_reward_codes as c')->join('rewards as r', 'c.reward_id', '=', 'r.id')
                    ->where('c.id', $id)->where('c.user_id', $userId)
                    ->select('c.*', 'r.title_th', 'r.title_en', 'r.image_url', 'r.reward_type')->first();
                return $r ? self::card($type, $id, $r->title_th, $r->title_en ?? null, $r->image_url ?? null,
                    $r->code ? 'Code ' . $r->code : null, $r->status,
                    ['reward_id' => $r->reward_id, 'reward_type' => $r->reward_type ?? null, 'tracking_number' => $r->tracking_number ?? null]) : null;

            case 'order':
                $r = DB::table('orders')->where('id', $id)->where('user_id', $userId)->first();
                if (!$r) return null;
                $first = DB::table('order_items as i')->leftJoin('products as p', 'i.product_id', '=', 'p.id')
                    ->where('i.order_id', $id)->orderBy('i.id')->select('i.product_name', 'p.image_url')->first();
                $count = DB::table('order_items')->where('order_id', $id)->count();
                return self::card($type, $id, 'Order ' . ($r->order_number ?? $id), null, $first->image_url ?? null,
                    self::join([($first->product_name ?? '') . ($count > 1 ? ' +' . ($count - 1) : ''), '฿' . number_format((float) ($r->total_amount ?? 0), 2)]),
                    $r->status ?? null, ['order_number' => $r->order_number ?? null]);

            case 'service_request':
                $r = DB::table('service_requests')->where('id', $id)->where('customer_id', $userId)->first();
                return $r ? self::card($type, $id, ($r->ticket_number ?? null) ? 'Ticket ' . $r->ticket_number : ('#' . $id), null, null,
                    $r->service_type ?? null, $r->status ?? null,
                    ['ticket_number' => $r->ticket_number ?? null, 'customer_product_id' => $r->customer_product_id ?? null]) : null;

            case 'locker_booking':
                $r = DB::table('locker_bookings as b')->leftJoin('smart_lockers as l', 'b.smart_locker_id', '=', 'l.id')
                    ->where('b.id', $id)->where('b.user_id', $userId)
                    ->select('b.*', 'l.title_th', 'l.title_en', 'l.image_url', 'l.locker_number')->first();
                return $r ? self::card($type, $id, 'Booking ' . ($r->booking_number ?? $id), $r->title_en ?? null, $r->image_url ?? null,
                    self::join([$r->title_th ?? '', $r->start_date . ' – ' . $r->end_date]), $r->status ?? null,
                    ['booking_number' => $r->booking_number ?? null, 'smart_locker_id' => $r->smart_locker_id]) : null;
        }
        return null;
    }

    /** แปลงแถว support_chats ให้มี field "ref" (object หรือ null) สำหรับส่งออก API / Pusher */
    public static function present($row)
    {
        if (!$row) return $row;
        $snap = property_exists($row, 'ref_snapshot') && $row->ref_snapshot ? json_decode($row->ref_snapshot, true) : null;
        $row->ref = $snap ?: null;
        unset($row->ref_snapshot);
        return $row;
    }

    private static function join(array $parts): ?string
    {
        $parts = array_values(array_filter(array_map('strval', $parts), fn ($p) => trim($p) !== ''));
        return $parts ? implode(' · ', $parts) : null;
    }

    private static function card(string $type, int $id, ?string $title, ?string $titleEn, ?string $image,
                                 ?string $subtitle = null, ?string $status = null, array $extra = []): array
    {
        return array_merge([
            'type' => $type,
            'id' => $id,
            'title' => $title,
            'title_en' => $titleEn ?: $title,
            'subtitle' => $subtitle,
            'image_url' => $image,
            'status' => $status,
        ], $extra);
    }
}
