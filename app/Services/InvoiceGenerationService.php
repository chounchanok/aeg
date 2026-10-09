<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ServiceContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 🌟 บริการสร้างใบแจ้งหนี้รายเดือนอัตโนมัติ — เรียกจาก:
 *   - App\Console\Commands\GenerateMonthlyInvoices (cron รายวัน — ดูวันที่เหมาะสมเองว่าสัญญาไหนถึงกำหนดบิล)
 *   - App\Http\Controllers\Admin\InvoiceAdminController::generateNow() (ปุ่ม "สร้างใบแจ้งหนี้ตอนนี้" ในหลังบ้าน)
 *
 * รองรับใบแจ้งหนี้ 2 แบบ:
 *   1. type=contract  → จากสัญญา/บริการรายเดือน (service_contracts) เช่น ค่าบำรุงรักษารายเดือน
 *   2. type=statement → สรุปยอดคำสั่งซื้อที่ยังไม่ชำระของลูกค้ากลุ่ม "วางบิลรายเดือน" (is_invoice_customer)
 *      รวมเป็นใบแจ้งหนี้เดียวต่อเดือน แทนที่จะให้จ่ายทีละออเดอร์
 *
 * กันสร้างซ้ำด้วยการเช็ค exists() ก่อนเสมอ (ไม่ใช้ DB unique constraint เพราะ NULL ใน
 * service_contract_id ทำให้ unique index ของ MySQL ไม่ช่วยกันซ้ำแถว type=statement ได้)
 */
class InvoiceGenerationService
{
    /** อัตรา VAT ของไทย — ใช้ "ถอด" VAT ออกจากราคาที่ลูกค้าเห็น (ตั้งสมมติฐานว่าราคาสินค้า/ค่าบริการที่ตั้งไว้ในระบบ รวม VAT แล้ว) */
    const VAT_RATE = 0.07;

    /** จำนวนวันให้ชำระนับจากวันออกใบแจ้งหนี้ */
    const DUE_DAYS = 7;

    /**
     * จุดเข้าหลักที่ใช้ทั้งจาก cron และปุ่มแอดมิน
     *
     * @param string|null $month รูปแบบ 'Y-m' เช่น '2026-09' — ว่าง = ให้ระบบตัดสินใจเองตามวันที่ปัจจุบัน (โหมด cron ปกติ)
     * @param string $generatedBy 'cron' | 'manual'
     * @param bool $force ข้ามการเช็ควัน billing_day ของสัญญา (ใช้ตอนแอดมินกด "สร้างตอนนี้"/backfill ย้อนหลัง)
     */
    public function generateForMonth(?string $month, string $generatedBy = 'cron', bool $force = false): array
    {
        $result = [
            'contract_generated' => 0,
            'statement_generated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        if ($month) {
            // ระบุเดือนมาชัดเจน (แอดมินกดสร้างเอง/backfill) → ใช้ force เสมอ ไม่ต้องรอ billing_day
            $billingMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $force = true;
        } else {
            // โหมด cron รายวันปกติ — บิลของ "เดือนปัจจุบัน" สำหรับสัญญา, และของ "เดือนก่อนหน้า" สำหรับใบสรุปยอด
            $billingMonth = Carbon::now()->startOfMonth();
        }

        $this->generateContractInvoices($billingMonth, $generatedBy, $force, $result);

        // ใบแจ้งหนี้สรุปยอด (statement) ออกตอนต้นเดือนถัดไป สรุปยอดของ "เดือนที่เพิ่งจบไป" เสมอ
        // ถ้าระบุ --month มาตรงๆ ให้ตีความว่าต้องการสรุปยอดของเดือนนั้นเป๊ะๆ (ไม่ต้อง -1 เดือน)
        $statementMonth = $month ? $billingMonth : Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $this->generateStatementInvoices($statementMonth, $generatedBy, $force, $result);

        return $result;
    }

    /**
     * สร้างใบแจ้งหนี้จากสัญญา/บริการรายเดือนที่ถึงกำหนดบิลของเดือนนี้
     */
    protected function generateContractInvoices(Carbon $billingMonth, string $generatedBy, bool $force, array &$result): void
    {
        $today = Carbon::now();

        $contracts = ServiceContract::where('status', 'active')
            ->where('start_date', '<=', $billingMonth->copy()->endOfMonth())
            ->where(function ($q) use ($billingMonth) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $billingMonth->copy()->startOfMonth());
            })
            ->get();

        foreach ($contracts as $contract) {
            // กันซ้ำ: เดือนนี้ออกบิลให้สัญญานี้ไปแล้วหรือยัง
            $already = Invoice::where('service_contract_id', $contract->id)
                ->whereDate('billing_month', $billingMonth->toDateString())
                ->exists();

            if ($already) {
                $result['skipped']++;
                continue;
            }

            // โหมด cron ปกติ (ไม่ force): ต้องถึงวัน billing_day ของสัญญาแล้วเท่านั้น
            // (next_billing_month <= เดือนนี้ กันกรณี cron ไม่ได้รันตรงวัน ก็ยังไล่ตามทันในวันถัดๆ ไปของเดือนเดียวกัน)
            if (!$force) {
                $dueThisMonth = !$contract->next_billing_month || $contract->next_billing_month->lte($billingMonth);
                $dayReached = $today->day >= $contract->billing_day;
                if (!$dueThisMonth || !$dayReached) {
                    continue;
                }
            }

            try {
                DB::transaction(function () use ($contract, $billingMonth, $generatedBy) {
                    $this->createContractInvoice($contract, $billingMonth, $generatedBy);
                });
                $result['contract_generated']++;
            } catch (\Throwable $e) {
                Log::error('[InvoiceGenerationService] ออกใบแจ้งหนี้สัญญา #' . $contract->id . ' ไม่สำเร็จ: ' . $e->getMessage());
                $result['errors'][] = "สัญญา {$contract->contract_number}: " . $e->getMessage();
            }
        }
    }

    protected function createContractInvoice(ServiceContract $contract, Carbon $billingMonth, string $generatedBy): Invoice
    {
        $profile = DB::table('customer_profiles')->where('user_id', $contract->user_id)->first();
        $user = DB::table('users')->where('id', $contract->user_id)->first();

        [$subtotal, $vat, $total] = $this->splitVat((float) $contract->billing_amount, $profile->tax_id ?? null);

        $issueDate = Carbon::now();

        $invoice = Invoice::create([
            'invoice_number' => $this->nextInvoiceNumber($issueDate),
            'type' => 'contract',
            'user_id' => $contract->user_id,
            'service_contract_id' => $contract->id,
            'billing_month' => $billingMonth->toDateString(),
            'issue_date' => $issueDate->toDateString(),
            'due_date' => $issueDate->copy()->addDays(self::DUE_DAYS)->toDateString(),
            'customer_name_snapshot' => trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: ($user->username ?? ''),
            'customer_tax_id_snapshot' => $profile->tax_id ?? null,
            'customer_branch_snapshot' => $profile->branch ?? null,
            'customer_address_snapshot' => $profile->address ?? null,
            'subtotal' => $subtotal,
            'vat_amount' => $vat,
            'total_amount' => $total,
            'payment_method' => $contract->payment_method,
            'status' => 'pending',
            'generated_by' => $generatedBy,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $contract->title . ' (งวด ' . $this->thaiMonthLabel($billingMonth) . ')',
            'reference_type' => 'contract',
            'reference_id' => $contract->id,
            'quantity' => 1,
            'unit_price' => $contract->billing_amount,
            'amount' => $contract->billing_amount,
        ]);

        $contract->update([
            'last_invoiced_month' => $billingMonth->toDateString(),
            'next_billing_month' => $billingMonth->copy()->addMonthNoOverflow()->startOfMonth()->toDateString(),
        ]);

        $this->notifyCustomer($invoice);

        return $invoice;
    }

    /**
     * สร้างใบแจ้งหนี้สรุปยอด (statement) ให้ลูกค้ากลุ่ม "วางบิลรายเดือน" — รวมคำสั่งซื้อที่ยังไม่ชำระ
     * (pending_payment) และยังไม่เคยถูกรวมในใบแจ้งหนี้ใบไหนมาก่อน ของเดือนที่ระบุ
     */
    protected function generateStatementInvoices(Carbon $billingMonth, string $generatedBy, bool $force, array &$result): void
    {
        $invoiceCustomers = DB::table('customer_profiles')
            ->where('is_invoice_customer', true)
            ->get();

        // 🌟 ดึงครั้งเดียวนอกลูป (กัน N+1) — order id ที่เคยถูกรวมในใบแจ้งหนี้ใบไหนไปแล้วบ้าง (ทุกลูกค้า)
        $alreadyInvoicedOrderIds = DB::table('invoice_items')->where('reference_type', 'order')->pluck('reference_id');

        foreach ($invoiceCustomers as $profile) {
            $already = Invoice::where('user_id', $profile->user_id)
                ->where('type', 'statement')
                ->whereDate('billing_month', $billingMonth->toDateString())
                ->exists();

            if ($already) {
                $result['skipped']++;
                continue;
            }

            // ออเดอร์ที่ยังไม่จ่าย + เกิดขึ้นในเดือนที่จะสรุปยอด + ยังไม่เคยถูกดึงเข้าใบแจ้งหนี้ใบอื่น
            $orders = DB::table('orders')
                ->where('user_id', $profile->user_id)
                ->where('status', 'pending_payment')
                ->whereBetween('created_at', [$billingMonth->copy()->startOfMonth(), $billingMonth->copy()->endOfMonth()])
                ->whereNotIn('id', $alreadyInvoicedOrderIds)
                ->get();

            if ($orders->isEmpty()) {
                continue; // ลูกค้าคนนี้ไม่มีรายการค้างจ่ายในเดือนนี้ — ไม่ต้องออกบิลเปล่า
            }

            try {
                DB::transaction(function () use ($profile, $orders, $billingMonth, $generatedBy) {
                    $this->createStatementInvoice($profile, $orders, $billingMonth, $generatedBy);
                });
                $result['statement_generated']++;
            } catch (\Throwable $e) {
                Log::error('[InvoiceGenerationService] ออกใบแจ้งหนี้สรุปยอด user #' . $profile->user_id . ' ไม่สำเร็จ: ' . $e->getMessage());
                $result['errors'][] = "ลูกค้า user_id={$profile->user_id}: " . $e->getMessage();
            }
        }
    }

    protected function createStatementInvoice($profile, $orders, Carbon $billingMonth, string $generatedBy): Invoice
    {
        $user = DB::table('users')->where('id', $profile->user_id)->first();
        $ordersTotal = (float) $orders->sum('total_amount');

        [$subtotal, $vat, $total] = $this->splitVat($ordersTotal, $profile->tax_id ?? null);

        $issueDate = Carbon::now();

        $invoice = Invoice::create([
            'invoice_number' => $this->nextInvoiceNumber($issueDate),
            'type' => 'statement',
            'user_id' => $profile->user_id,
            'service_contract_id' => null,
            'billing_month' => $billingMonth->toDateString(),
            'issue_date' => $issueDate->toDateString(),
            'due_date' => $issueDate->copy()->addDays(self::DUE_DAYS)->toDateString(),
            'customer_name_snapshot' => trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: ($user->username ?? ''),
            'customer_tax_id_snapshot' => $profile->tax_id ?? null,
            'customer_branch_snapshot' => $profile->branch ?? null,
            'customer_address_snapshot' => $profile->address ?? null,
            'subtotal' => $subtotal,
            'vat_amount' => $vat,
            'total_amount' => $total,
            'payment_method' => $profile->invoice_payment_method ?? 'bank_transfer',
            'status' => 'pending',
            'generated_by' => $generatedBy,
        ]);

        foreach ($orders as $order) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'คำสั่งซื้อ #' . $order->order_number,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'quantity' => 1,
                'unit_price' => $order->total_amount,
                'amount' => $order->total_amount,
            ]);
        }

        $this->notifyCustomer($invoice);

        return $invoice;
    }

    /**
     * เมื่อใบแจ้งหนี้ถูกจ่ายแล้ว (ผ่าน gateway webhook หรือแอดมินกดยืนยันสลิปโอน) — อัปเดตสถานะและ
     * ผูกผลลัพธ์กลับไปยังต้นทาง (ออเดอร์ที่ถูกรวมในใบสรุปยอด ให้เปลี่ยนเป็น 'paid' ตามไปด้วย)
     */
    public function markPaid(Invoice $invoice, ?string $transactionId = null, ?array $gatewayResponse = null): void
    {
        DB::transaction(function () use ($invoice, $transactionId, $gatewayResponse) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'gateway_transaction_id' => $transactionId,
                'gateway_response' => $gatewayResponse,
            ]);

            if ($invoice->type === 'statement') {
                $orderIds = $invoice->items()->where('reference_type', 'order')->pluck('reference_id');
                if ($orderIds->isNotEmpty()) {
                    DB::table('orders')->whereIn('id', $orderIds)->update([
                        'status' => 'paid',
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        try {
            DB::table('notifications')->insert([
                'user_id' => $invoice->user_id,
                'title' => 'ชำระใบแจ้งหนี้เรียบร้อยแล้ว',
                'body' => 'ใบแจ้งหนี้ #' . $invoice->invoice_number . ' ยอด ' . number_format((float) $invoice->total_amount, 2) . ' บาท ชำระเงินเรียบร้อยแล้ว ขอบคุณค่ะ',
                'type' => 'invoice',
                'target_type' => 'invoice', // 🌟 กดแล้วเปิดหน้าใบแจ้งหนี้ในแอป
                'target_id' => $invoice->id,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[InvoiceGenerationService] แจ้งเตือนลูกค้าหลังชำระใบแจ้งหนี้ล้มเหลว: ' . $e->getMessage());
        }
    }

    /**
     * ถอด VAT 7% ออกจากยอดรวม — ใช้เฉพาะลูกค้าที่มีเลขผู้เสียภาษี (ต้องการใบกำกับภาษีเต็มรูป)
     * ⚠️ สมมติฐาน: ราคาสินค้า/ค่าบริการที่ตั้งไว้ในระบบเป็นราคารวม VAT แล้ว (ตามธรรมเนียมราคาขายปลีกไทย)
     * @return array{0:float,1:float,2:float} [subtotal, vat_amount, total]
     */
    protected function splitVat(float $amountInclusive, ?string $taxId): array
    {
        if (empty($taxId)) {
            return [round($amountInclusive, 2), 0.0, round($amountInclusive, 2)];
        }

        $subtotal = round($amountInclusive / (1 + self::VAT_RATE), 2);
        $vat = round($amountInclusive - $subtotal, 2);

        return [$subtotal, $vat, round($amountInclusive, 2)];
    }

    /**
     * 🌟 ออกเลขที่ใบแจ้งหนี้แบบเรียงลำดับไม่ขาดตอนต่อเดือน (ข้อกำหนดใบกำกับภาษีของไทย)
     * รูปแบบ: INV-{YYYYMM}-{เลขรัน 4 หลัก} เช่น INV-202610-0001
     * ใช้ lockForUpdate() ล็อกแถวล่าสุดของเดือนนั้นกันเลขชนกันตอนมีการสร้างพร้อมกัน (cron + แอดมินกดสร้างเอง)
     */
    protected function nextInvoiceNumber(Carbon $issueDate): string
    {
        $prefix = 'INV-' . $issueDate->format('Ym') . '-';

        $lastNumber = DB::table('invoices')
            ->where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $nextSeq = 1;
        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    protected function thaiMonthLabel(Carbon $month): string
    {
        $thaiMonths = [1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        return ($thaiMonths[$month->month] ?? $month->month) . ' ' . ($month->year + 543);
    }

    protected function notifyCustomer(Invoice $invoice): void
    {
        try {
            $title = 'มีใบแจ้งหนี้ใหม่';
            $body = 'ใบแจ้งหนี้ #' . $invoice->invoice_number . ' ยอด ' . number_format((float) $invoice->total_amount, 2)
                . ' บาท ครบกำหนดชำระวันที่ ' . Carbon::parse($invoice->due_date)->format('d/m/Y');

            DB::table('notifications')->insert([
                'user_id' => $invoice->user_id,
                'title' => $title,
                'body' => $body,
                'type' => 'invoice',
                'target_type' => 'invoice', // 🌟 กดแล้วเปิดหน้าใบแจ้งหนี้ในแอป
                'target_id' => $invoice->id,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \App\Services\PushNotificationService::sendToUser($invoice->user_id, $title, $body, [
                'type' => 'invoice',
                'invoice_id' => (string) $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[InvoiceGenerationService] แจ้งเตือนใบแจ้งหนี้ใหม่ล้มเหลว: ' . $e->getMessage());
        }
    }
}
