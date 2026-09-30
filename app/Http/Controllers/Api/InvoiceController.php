<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\StaffNotificationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * 🌟 API ใบแจ้งหนี้รายเดือนฝั่งลูกค้า (มือถือ) — ดูรายการ/รายละเอียด, ขอลิงก์จ่ายเงิน (gateway),
 * และแนบสลิปโอนเงิน (bank_transfer) ให้แอดมินตรวจสอบ
 */
class InvoiceController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $invoices = Invoice::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($inv) => $this->formatInvoice($inv));

        return $this->successResponse($invoices, 'Invoices retrieved');
    }

    public function show(Request $request, $id)
    {
        $invoice = Invoice::where('user_id', $request->user()->id)->where('id', $id)->first();
        if (!$invoice) {
            return $this->errorResponse('ไม่พบใบแจ้งหนี้นี้', 404);
        }

        $data = $this->formatInvoice($invoice);
        $data['items'] = $invoice->items()->get(['description', 'quantity', 'unit_price', 'amount']);

        return $this->successResponse($data, 'Invoice detail retrieved');
    }

    /**
     * ขอลิงก์ชำระเงินผ่าน Gateway (BBL) — ใช้ได้เฉพาะใบแจ้งหนี้ที่เลือกช่องทาง gateway และยังไม่ชำระ
     * แอปเปิด payment_url นี้ใน WebView (แพทเทิร์นเดียวกับการจ่ายค่าสั่งซื้อ/จองตู้เซฟ)
     */
    public function pay(Request $request, $id)
    {
        $invoice = Invoice::where('user_id', $request->user()->id)->where('id', $id)->first();
        if (!$invoice) {
            return $this->errorResponse('ไม่พบใบแจ้งหนี้นี้', 404);
        }
        if ($invoice->status !== 'pending') {
            return $this->errorResponse('ใบแจ้งหนี้นี้ไม่ได้อยู่ในสถานะรอชำระเงิน', 400);
        }
        if ($invoice->payment_method !== 'gateway') {
            return $this->errorResponse('ใบแจ้งหนี้นี้ตั้งช่องทางชำระเป็นโอนเงิน กรุณาใช้ช่องแนบสลิปแทน', 400);
        }

        // 🌟 ไม่ต่อ /{type} ท้าย URL ให้แอปเป็นคนต่อเองตามช่องทางที่ลูกค้าเลือก (qrcode/creditcard/all)
        // ตามแพทเทิร์นเดียวกับลิงก์จ่ายเงินค่าสั่งซื้อใน EcommerceController::checkout()
        $paymentUrl = url('/payment/bbl/redirect/' . $invoice->invoice_number);

        return $this->successResponse(['payment_url' => $paymentUrl], 'Payment link generated');
    }

    /**
     * แนบสลิปโอนเงิน — ใช้ได้เฉพาะใบแจ้งหนี้ที่เลือกช่องทางโอนเงินและยังไม่ชำระ
     * แอดมิน (แผนกบัญชี) ต้องกดยืนยันการชำระด้วยมือที่หลังบ้านหลังตรวจสลิปแล้ว (ดู InvoiceAdminController::markPaid)
     */
    public function uploadSlip(Request $request, $id)
    {
        $invoice = Invoice::where('user_id', $request->user()->id)->where('id', $id)->first();
        if (!$invoice) {
            return $this->errorResponse('ไม่พบใบแจ้งหนี้นี้', 404);
        }
        if ($invoice->status !== 'pending') {
            return $this->errorResponse('ใบแจ้งหนี้นี้ไม่ได้อยู่ในสถานะรอชำระเงิน', 400);
        }

        $request->validate([
            'slip' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // สูงสุด 10MB
        ]);

        $path = $request->file('slip')->store('invoice-slips', 'public');
        $invoice->update(['payment_slip_url' => url('storage/' . $path)]);

        StaffNotificationService::notifyRole(
            'accounting',
            'มีสลิปโอนเงินรอตรวจสอบ',
            "ใบแจ้งหนี้ #{$invoice->invoice_number} ยอด " . number_format((float) $invoice->total_amount, 2) . ' บาท มีลูกค้าแนบสลิปโอนเงินมาแล้ว รอตรวจสอบและยืนยันการชำระ',
            url('/admin/invoices/' . $invoice->id),
            'invoice_slip'
        );

        return $this->successResponse(['payment_slip_url' => $invoice->payment_slip_url], 'อัปโหลดสลิปเรียบร้อยแล้ว รอแอดมินตรวจสอบและยืนยันการชำระเงิน');
    }

    private function formatInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'type' => $invoice->type,
            'billing_month' => $invoice->billing_month->format('Y-m'),
            'issue_date' => $invoice->issue_date->format('Y-m-d'),
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'subtotal' => (float) $invoice->subtotal,
            'vat_amount' => (float) $invoice->vat_amount,
            'total_amount' => (float) $invoice->total_amount,
            'payment_method' => $invoice->payment_method,
            'status' => $invoice->status,
            'is_overdue' => $invoice->status === 'pending' && $invoice->due_date->isPast(),
            'paid_at' => $invoice->paid_at?->format('Y-m-d H:i:s'),
            'payment_slip_url' => $invoice->payment_slip_url,
        ];
    }
}
