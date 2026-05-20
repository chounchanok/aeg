<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\Request;

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
}