<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 🌟 จัดการสัญญา/บริการรายเดือน (Service Contracts) — ต้นทางของใบแจ้งหนี้แบบ type=contract
 * ดูใบแจ้งหนี้ที่ออกจากสัญญาแต่ละอันได้ที่หน้า "ใบแจ้งหนี้" (InvoiceAdminController) กรองตาม service_contract_id
 */
class ServiceContractAdminController extends Controller
{
    public function index()
    {
        $contracts = DB::table('service_contracts')
            ->join('users', 'service_contracts.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->select(
                'service_contracts.*',
                'users.username',
                'customer_profiles.first_name',
                'customer_profiles.last_name'
            )
            ->orderBy('service_contracts.created_at', 'desc')
            ->get();

        // จำนวนใบแจ้งหนี้ที่ออกไปแล้วของแต่ละสัญญา (แสดงในตาราง ไว้กดดูประวัติ)
        $invoiceCounts = DB::table('invoices')
            ->select('service_contract_id', DB::raw('count(*) as total'))
            ->whereNotNull('service_contract_id')
            ->groupBy('service_contract_id')
            ->pluck('total', 'service_contract_id');

        $customers = DB::table('users')
            ->join('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->where('users.role', 'customer')
            ->select('users.id', 'users.username', 'customer_profiles.first_name', 'customer_profiles.last_name', 'customer_profiles.tax_id')
            ->orderBy('customer_profiles.first_name')
            ->get();

        return view('admin.service-contracts.index', [
            'contracts' => $contracts,
            'invoiceCounts' => $invoiceCounts,
            'customers' => $customers,
            'first_level_active_index' => 'invoices',
            'second_level_active_index' => 'service-contracts',
            'third_level_active_index' => ''
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'billing_amount' => 'required|numeric|min:0.01',
            'billing_day' => 'required|integer|min:1|max:28',
            'payment_method' => 'required|in:gateway,bank_transfer',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $contractNumber = $this->nextContractNumber();
        $startDate = \Illuminate\Support\Carbon::parse($request->start_date);

        DB::table('service_contracts')->insert([
            'contract_number' => $contractNumber,
            'user_id' => $request->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'billing_amount' => $request->billing_amount,
            'billing_day' => $request->billing_day,
            'payment_method' => $request->payment_method,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active',
            // 🌟 ถ้าวันเริ่มสัญญาผ่าน billing_day ของเดือนนี้ไปแล้ว ให้เริ่มบิลเดือนถัดไปแทน กันบิลย้อนหลังโดยไม่ตั้งใจ
            'next_billing_month' => $startDate->day > $request->billing_day
                ? $startDate->copy()->addMonthNoOverflow()->startOfMonth()->toDateString()
                : $startDate->copy()->startOfMonth()->toDateString(),
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "สร้างสัญญา {$contractNumber} เรียบร้อยแล้ว");
    }

    public function update(Request $request, $id)
    {
        $contract = DB::table('service_contracts')->where('id', $id)->first();
        if (!$contract) {
            return redirect()->back()->with('error', 'ไม่พบสัญญานี้ในระบบ');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'billing_amount' => 'required|numeric|min:0.01',
            'billing_day' => 'required|integer|min:1|max:28',
            'payment_method' => 'required|in:gateway,bank_transfer',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,paused,cancelled,completed',
        ]);

        DB::table('service_contracts')->where('id', $id)->update([
            'title' => $request->title,
            'description' => $request->description,
            'billing_amount' => $request->billing_amount,
            'billing_day' => $request->billing_day,
            'payment_method' => $request->payment_method,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'บันทึกการแก้ไขสัญญาเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $contract = DB::table('service_contracts')->where('id', $id)->first();
        if (!$contract) {
            return redirect()->back()->with('error', 'ไม่พบสัญญานี้ในระบบ');
        }

        $invoiceCount = DB::table('invoices')->where('service_contract_id', $id)->count();
        if ($invoiceCount > 0) {
            // 🌟 มีใบแจ้งหนี้อ้างอิงอยู่แล้ว ห้ามลบทิ้ง (จะทำให้ประวัติการเงินหาย) ให้ยกเลิกสัญญาแทน
            return redirect()->back()->with('error', "ลบไม่ได้ เพราะมีใบแจ้งหนี้ {$invoiceCount} ใบอ้างอิงสัญญานี้อยู่ — กรุณาเปลี่ยนสถานะเป็น \"ยกเลิก\" แทน");
        }

        DB::table('service_contracts')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'ลบสัญญาเรียบร้อยแล้ว');
    }

    /**
     * ออกเลขที่สัญญาเรียงลำดับต่อปี เช่น CTR-2026-0001 (ไม่ต้องกันชนแบบ invoice เพราะแอดมินสร้างเองทีละรายการ ไม่ใช่ cron)
     */
    private function nextContractNumber(): string
    {
        $prefix = 'CTR-' . date('Y') . '-';
        $lastNumber = DB::table('service_contracts')
            ->where('contract_number', 'like', $prefix . '%')
            ->orderByDesc('contract_number')
            ->value('contract_number');

        $nextSeq = $lastNumber ? ((int) substr($lastNumber, strlen($prefix)) + 1) : 1;

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
