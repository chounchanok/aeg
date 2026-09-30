<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('invoices')
            ->join('users', 'invoices.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->leftJoin('service_contracts', 'invoices.service_contract_id', '=', 'service_contracts.id')
            ->select(
                'invoices.*',
                'users.username',
                'customer_profiles.first_name',
                'customer_profiles.last_name',
                'service_contracts.contract_number'
            );

        // 🌟 ตัวกรอง — ประเภท / สถานะ / เดือน (ทั้งหมดไม่บังคับ)
        if ($request->filled('type')) {
            $query->where('invoices.type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('invoices.status', $request->status);
        }
        if ($request->filled('month')) {
            $query->whereRaw("DATE_FORMAT(invoices.billing_month, '%Y-%m') = ?", [$request->month]);
        }

        // 🌟 ไม่ทำ pagination ให้สอดคล้องกับหน้าอื่นๆ ในระบบ (orders/products) — จำกัดแค่ 200 รายการล่าสุด
        // กันหน้าโหลดหนักเกินไปถ้าใช้งานไปนานๆ ยังไม่ตัด (ตัวกรองด้านบนช่วยแคบขอบเขตได้อยู่แล้ว)
        $invoices = $query->orderBy('invoices.created_at', 'desc')->limit(200)->get();

        $summary = [
            'pending' => DB::table('invoices')->where('status', 'pending')->count(),
            'paid_this_month' => DB::table('invoices')->where('status', 'paid')->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->count(),
            'overdue' => DB::table('invoices')->where('status', 'pending')->where('due_date', '<', now()->toDateString())->count(),
        ];

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'summary' => $summary,
            'filters' => $request->only(['type', 'status', 'month']),
            'first_level_active_index' => 'invoices',
            'second_level_active_index' => 'invoices',
            'third_level_active_index' => ''
        ]);
    }

    public function show($id)
    {
        $invoice = DB::table('invoices')
            ->join('users', 'invoices.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->leftJoin('service_contracts', 'invoices.service_contract_id', '=', 'service_contracts.id')
            ->select(
                'invoices.*',
                'users.username',
                'users.email',
                'customer_profiles.first_name',
                'customer_profiles.last_name',
                'customer_profiles.phone',
                'service_contracts.contract_number',
                'service_contracts.title as contract_title'
            )
            ->where('invoices.id', $id)
            ->first();

        if (!$invoice) abort(404);

        $items = DB::table('invoice_items')->where('invoice_id', $id)->get();

        return view('admin.invoices.show', [
            'invoice' => $invoice,
            'items' => $items,
            'first_level_active_index' => 'invoices',
            'second_level_active_index' => 'invoices',
            'third_level_active_index' => ''
        ]);
    }

    /**
     * แอดมินกดยืนยันว่าได้รับเงินแล้ว — ใช้กับ bank_transfer (ตรวจสลิปที่ลูกค้าแนบเอง แล้วกดยืนยันด้วยมือ)
     * หรือกรณี gateway ที่ webhook ไม่เข้า (เช็คสถานะแล้วพบว่าจ่ายจริง)
     */
    public function markPaid(Request $request, $id, InvoiceGenerationService $service)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'ไม่พบใบแจ้งหนี้นี้ในระบบ');
        }

        if ($invoice->status === 'paid') {
            return redirect()->back()->with('error', 'ใบแจ้งหนี้นี้ถูกยืนยันว่าชำระแล้วอยู่ก่อนหน้านี้แล้ว');
        }

        if ($request->filled('admin_note')) {
            $invoice->update(['admin_note' => $request->admin_note]);
        }

        $service->markPaid($invoice, $request->input('transaction_ref'));

        return redirect()->back()->with('success', "ยืนยันการชำระใบแจ้งหนี้ {$invoice->invoice_number} เรียบร้อยแล้ว");
    }

    public function cancel(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'ไม่พบใบแจ้งหนี้นี้ในระบบ');
        }
        if ($invoice->status === 'paid') {
            return redirect()->back()->with('error', 'ยกเลิกไม่ได้ เพราะใบแจ้งหนี้นี้ชำระเงินแล้ว');
        }

        $invoice->update([
            'status' => 'cancelled',
            'admin_note' => $request->input('admin_note', $invoice->admin_note),
        ]);

        return redirect()->back()->with('success', "ยกเลิกใบแจ้งหนี้ {$invoice->invoice_number} เรียบร้อยแล้ว");
    }

    /**
     * 🌟 ปุ่ม "สร้างใบแจ้งหนี้ตอนนี้" — ใช้กรณี cron ยังไม่ตั้งบนเครื่อง production ให้ก่อน หรือต้องการ backfill ย้อนหลัง
     * ระบุเดือนได้ (ไม่ระบุ = ให้ระบบตัดสินใจเองแบบเดียวกับที่ cron รายวันทำ)
     */
    public function generateNow(Request $request, InvoiceGenerationService $service)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $result = $service->generateForMonth($request->input('month') ?: null, 'manual', true);

        $message = "สร้างใบแจ้งหนี้สัญญารายเดือน {$result['contract_generated']} ใบ, ใบสรุปยอดลูกค้าวางบิล {$result['statement_generated']} ใบ (ข้าม {$result['skipped']} รายการที่ออกไปแล้ว)";

        if (!empty($result['errors'])) {
            return redirect()->back()->with('error', $message . ' — พบข้อผิดพลาด: ' . implode('; ', $result['errors']));
        }

        return redirect()->back()->with('success', $message);
    }
}
