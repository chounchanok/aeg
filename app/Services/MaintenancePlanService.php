<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 🌟 แผนบำรุงรักษา (Maintenance Plan)
 *
 * - ค่าตั้งต้นอยู่ที่ "บริการแต่ละตัว" (products: contract_months, service_visits, visit_interval_months, ticket_lead_days, auto_schedule)
 * - ตอนลูกค้าซื้อ / แอดมินเพิ่มแพ็กเกจให้ลูกค้า → คัดลอกเป็นแผนของลูกค้ารายนั้น (customer_products) ปรับรายคนได้
 * - ทุกวันระบบเปิด ticket "บำรุงรักษาตามรอบ" ล่วงหน้า (ticket_lead_days) → แอดมิน assign ช่าง → loop ช่างเดิม
 *   แล้วเลื่อนวันครบรอบถัดไปให้อัตโนมัติ — หยุดเองเมื่อโควต้าหมด / สัญญาหมดอายุ / ปิดอัตโนมัติ
 */
class MaintenancePlanService
{
    public const TIME_SLOTS = ['09:00-12:00', '13:00-18:00'];

    /** วันครบรอบถัดไป: +interval เดือน แล้วปรับเป็นวันที่สะดวกของเดือนนั้น (ถ้ากำหนด) */
    public static function nextDate($from, int $intervalMonths, ?int $preferredDay = null): Carbon
    {
        $d = Carbon::parse($from)->startOfDay()->addMonthsNoOverflow(max(1, $intervalMonths));
        if ($preferredDay) {
            $d->day(min($preferredDay, $d->daysInMonth));
        }
        return $d;
    }

    /** แผนตั้งต้นของบริการ (null = บริการนี้ไม่มีแผนบำรุงรักษา) */
    public static function productDefaults($product): ?array
    {
        if (!$product || empty($product->visit_interval_months)) {
            return null;
        }
        return [
            'contract_months' => $product->contract_months ? (int) $product->contract_months : null,
            'service_visits' => $product->service_visits !== null ? (int) $product->service_visits : null,
            'visit_interval_months' => (int) $product->visit_interval_months,
            'ticket_lead_days' => (int) ($product->ticket_lead_days ?? 10),
            'auto_schedule' => (bool) ($product->auto_schedule ?? false),
        ];
    }

    /**
     * คัดลอกแผนจากบริการ (products) ไปเป็นแผนของลูกค้า — เรียกหลังสร้าง customer_products
     * วันเข้าบริการครั้งแรก = วันเริ่มสัญญา + ทุกกี่เดือน
     */
    public static function applyFromProduct(int $customerProductId): void
    {
        $cp = DB::table('customer_products')->where('id', $customerProductId)->first();
        if (!$cp || ($cp->reference_type ?? 'product') !== 'product' || !$cp->reference_id) return;

        $defaults = self::productDefaults(DB::table('products')->where('id', $cp->reference_id)->first());
        if (!$defaults) return;

        $start = $cp->purchase_date ?: $cp->created_at ?: now();
        DB::table('customer_products')->where('id', $customerProductId)->update([
            'visit_interval_months' => $defaults['visit_interval_months'],
            'ticket_lead_days' => $defaults['ticket_lead_days'],
            'auto_schedule' => $defaults['auto_schedule'],
            'next_service_date' => self::nextDate($start, $defaults['visit_interval_months'])->toDateString(),
            'updated_at' => now(),
        ]);
    }

    /** โควต้าคงเหลือ (null = ไม่จำกัด เมื่อ total_service_count = 0) */
    public static function remainingVisits($cp): ?int
    {
        $total = (int) ($cp->total_service_count ?? 0);
        return $total > 0 ? max(0, $total - (int) ($cp->used_service_count ?? 0)) : null;
    }

    /** ใบงานตามรอบที่ยังไม่ปิด (กันเปิดซ้ำ) */
    public static function openScheduledTicket(int $customerProductId)
    {
        return DB::table('service_requests')
            ->where('customer_product_id', $customerProductId)
            ->where('source', 'scheduled')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByDesc('id')->first();
    }

    /** เหตุผลที่แผนนี้ "ยังไม่ควร/ไม่สามารถ" เปิด ticket (null = เปิดได้) */
    public static function blockReason($cp, bool $ignoreWindow = false): ?string
    {
        if (($cp->status ?? 'active') !== 'active') return 'แพ็กเกจไม่ได้อยู่ในสถานะใช้งาน';
        if (empty($cp->visit_interval_months)) return 'ยังไม่ได้ตั้งความถี่การเข้าบริการ';
        if (empty($cp->next_service_date)) return 'ยังไม่ได้กำหนดวันครบรอบถัดไป';
        $remaining = self::remainingVisits($cp);
        if ($remaining !== null && $remaining <= 0) return 'โควต้าเข้าบริการหมดแล้ว';
        if ($cp->warranty_expire_date && Carbon::parse($cp->next_service_date)->gt(Carbon::parse($cp->warranty_expire_date))) {
            return 'รอบถัดไปเลยวันหมดอายุสัญญาแล้ว';
        }
        if (self::openScheduledTicket((int) $cp->id)) return 'มีใบงานตามรอบที่ยังไม่ปิดอยู่';
        if (!$ignoreWindow) {
            $lead = (int) ($cp->ticket_lead_days ?? 10);
            if (Carbon::parse($cp->next_service_date)->gt(now()->startOfDay()->addDays($lead))) return 'ยังไม่ถึงช่วงเปิดใบงานล่วงหน้า';
        }
        return null;
    }

    /**
     * เปิดใบงานบำรุงรักษาตามรอบ 1 ใบ แล้วเลื่อนวันครบรอบถัดไป
     * @return array ['ok' => bool, 'message' => string, 'request_id' => ?int]
     */
    public static function createTicket(int $customerProductId, ?int $adminId = null, bool $ignoreWindow = false): array
    {
        $cp = DB::table('customer_products')->where('id', $customerProductId)->first();
        if (!$cp) return ['ok' => false, 'message' => 'ไม่พบแพ็กเกจของลูกค้า', 'request_id' => null];

        if ($reason = self::blockReason($cp, $ignoreWindow)) {
            return ['ok' => false, 'message' => $reason, 'request_id' => null];
        }

        $serviceDate = Carbon::parse($cp->next_service_date);
        if ($serviceDate->lt(now()->startOfDay())) {
            $serviceDate = now()->startOfDay(); // เลยกำหนดแล้ว (เช่น เพิ่งเปิดแผน) → นัดเร็วที่สุด
        }

        $addressId = $cp->maintenance_address_id;
        if (!$addressId) {
            $addr = AddressService::activeQuery((int) $cp->customer_id)->orderByDesc('is_default')->orderByDesc('id')->first();
            $addressId = $addr->id ?? null;
        }

        $visitNo = DB::table('service_requests')->where('customer_product_id', $cp->id)->where('source', 'scheduled')->count() + 1;
        $total = (int) ($cp->total_service_count ?? 0);
        $techId = $cp->maintenance_technician_id ?: null;
        $slot = in_array($cp->preferred_time_slot, self::TIME_SLOTS, true) ? $cp->preferred_time_slot : null;

        DB::beginTransaction();
        try {
            $requestId = DB::table('service_requests')->insertGetId([
                'ticket_number' => 'SR-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                'customer_id' => $cp->customer_id,
                'service_type' => 'Maintenance',
                'source' => 'scheduled',
                'customer_product_id' => $cp->id,
                'problem_description' => 'บำรุงรักษาตามรอบ ครั้งที่ ' . $visitNo . ($total > 0 ? '/' . $total : '')
                    . ' — ' . $cp->product_name . ($cp->maintenance_note ? "\n" . $cp->maintenance_note : ''),
                'address_id' => $addressId,
                'custom_address_text' => $addressId ? null : 'ยังไม่ระบุที่อยู่ — กรุณาติดต่อลูกค้า',
                'preferred_date' => $serviceDate->toDateString(),
                'time_slot' => $slot,
                'technician_id' => $techId,
                'status' => $techId ? 'assigned' : 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('service_request_tracking')->insert([
                'service_request_id' => $requestId,
                'status' => 'สร้างงานบำรุงรักษาตามรอบ',
                'description' => ($adminId ? 'แอดมินเปิดใบงานตามแผนบำรุงรักษา' : 'ระบบเปิดใบงานตามแผนบำรุงรักษาอัตโนมัติ')
                    . ($techId ? ' และมอบหมายช่างประจำแล้ว' : ' — รอแอดมินมอบหมายช่าง'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // เลื่อนวันครบรอบถัดไป (นับจากวันครบรอบเดิม ไม่ใช่วันที่เปิดใบงาน → รอบไม่เลื่อนไปเรื่อยๆ)
            DB::table('customer_products')->where('id', $cp->id)->update([
                'next_service_date' => self::nextDate($cp->next_service_date, (int) $cp->visit_interval_months, $cp->preferred_day ? (int) $cp->preferred_day : null)->toDateString(),
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[MaintenancePlan] create ticket failed cp=' . $cp->id . ': ' . $e->getMessage());
            return ['ok' => false, 'message' => 'เปิดใบงานไม่สำเร็จ: ' . $e->getMessage(), 'request_id' => null];
        }

        $ticket = DB::table('service_requests')->where('id', $requestId)->value('ticket_number');
        try {
            StaffNotificationService::notifyRole(
                'security_admin',
                $techId ? 'งานบำรุงรักษาตามรอบ (มอบหมายช่างประจำแล้ว)' : 'งานบำรุงรักษาตามรอบ รอมอบหมายช่าง',
                "{$ticket} — {$cp->product_name} นัด " . $serviceDate->format('d/m/Y'),
                '/admin/service-requests/' . $requestId,
                'service_request'
            );
            CustomerNotificationService::notify(
                (int) $cp->customer_id,
                'นัดหมายบำรุงรักษาตามรอบ',
                'ช่างจะเข้าดูแล ' . $cp->product_name . ' วันที่ ' . $serviceDate->format('d/m/Y') . ($slot ? ' ช่วง ' . $slot : '') . ' หากต้องการเลื่อนนัด กรุณาติดต่อเจ้าหน้าที่',
                'service',
                'service_request',
                $requestId
            );
        } catch (\Throwable $e) {
            Log::warning('[MaintenancePlan] notify failed: ' . $e->getMessage());
        }

        return ['ok' => true, 'message' => "เปิดใบงาน {$ticket} นัดวันที่ " . $serviceDate->format('d/m/Y') . ' แล้ว', 'request_id' => $requestId];
    }

    /** cron รายวัน: เปิดใบงานให้ทุกแผนที่ถึงช่วงเปิดล่วงหน้า */
    public static function generateDue(): array
    {
        $created = 0; $skipped = 0; $errors = [];
        DB::table('customer_products')
            ->where('auto_schedule', true)
            ->where('status', 'active')
            ->whereNotNull('visit_interval_months')
            ->whereNotNull('next_service_date')
            ->whereRaw('next_service_date <= DATE_ADD(CURDATE(), INTERVAL COALESCE(ticket_lead_days, 10) DAY)')
            ->chunkById(200, function ($rows) use (&$created, &$skipped, &$errors) {
                foreach ($rows as $cp) {
                    $r = self::createTicket((int) $cp->id);
                    if ($r['ok']) $created++;
                    else { $skipped++; if (str_starts_with($r['message'], 'เปิดใบงานไม่สำเร็จ')) $errors[] = "#{$cp->id}: {$r['message']}"; }
                }
            });

        return ['created' => $created, 'skipped' => $skipped, 'errors' => $errors];
    }
}
