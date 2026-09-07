<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use App\Services\StaffNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceController extends Controller
{
    // หน้ารวมรายการประกันภัย
    public function index()
    {
        // ดึงข้อมูลประกันภัยที่เปิดใช้งาน เรียงตาม sort_order
        $insurances = Insurance::where('is_active', true)
                        ->orderBy('sort_order', 'asc')
                        ->get();

        return view('frontend.insurance', compact('insurances'));
    }

    // หน้ารายละเอียดประกันภัย (ของเดิม)
    public function show($id)
    {
        $insurance = Insurance::where('id', $id)->where('is_active', true)->firstOrFail();
        return view('frontend.insurance-detail', compact('insurance'));
    }

    // 🌟 เพิ่มฟังก์ชันใหม่: หน้าฟอร์มติดต่อผู้เชี่ยวชาญ
    public function contact($id)
    {
        // ดึงข้อมูลประกันเพื่อเอาไปโชว์หัวข้อในหน้าติดต่อ
        $insurance = Insurance::where('id', $id)->where('is_active', true)->firstOrFail();
        
        return view('frontend.insurance-contact', compact('insurance'));
    }

    /**
     * 🌟 บันทึกฟอร์มติดต่อผู้เชี่ยวชาญประกันภัย (route insurance-contact.submit)
     * — เดิม route ชี้มาที่ method นี้แต่ยังไม่เคยมีในไฟล์ ทำให้ฟอร์มหน้าเว็บส่งไม่ได้ (500)
     * บันทึกลง insurance_contacts แล้วแจ้งเตือนแผนก Insurance ให้ไปดำเนินการต่อที่ /admin/contacts/insurance
     */
    public function submitContact(Request $request)
    {
        $request->validate([
            'insurance_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_time' => 'required|string',
            'message' => 'nullable|string'
        ]);

        try {
            $id = DB::table('insurance_contacts')->insertGetId([
                'insurance_id' => $request->insurance_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'contact_time' => $request->contact_time,
                'message' => $request->message,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $insuranceTitle = DB::table('insurances')->where('id', $request->insurance_id)->value('title_th');
            StaffNotificationService::notifyRole(
                'insurance_admin',
                'มีลูกค้าติดต่อเรื่องประกันภัยใหม่',
                "{$request->name} ({$request->phone}) สนใจ: " . ($insuranceTitle ?: '-') . " · สะดวกช่วง {$request->contact_time}",
                '/admin/contacts/insurance/' . $id,
                'contact_insurance'
            );

            return response()->json([
                'success' => true,
                'message' => 'ส่งข้อมูลติดต่อสำเร็จ'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}