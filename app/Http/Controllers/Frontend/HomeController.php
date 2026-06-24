<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Banner;
use App\Models\ServiceCategory;
use Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // 1. ดึงข้อมูล Banners (กรองตามที่ API เดิมเขียนไว้ คือ location = main และ is_active = true)
        $banners = Banner::where('location', 'main')
                         ->where('is_active', true)
                         ->orderBy('sort_order', 'asc')
                         ->get();

        // 2. ดึงข้อมูล หมวดหมู่บริการ
        $categories = ServiceCategory::all();

        // 🌟 2. ดึงข้อมูล "บริการที่ใกล้หมดอายุ" เฉพาะกรณีที่มีการ Login เท่านั้น
        $expiringServices = collect(); // ตั้งค่าเริ่มต้นเป็น Array ว่างเผื่อไม่ได้ล็อกอิน

        if (Auth::check()) {
            $userId = Auth::id();

            // ดึงบริการของลูกค้าคนนี้ที่กำลังจะหมดอายุในอีกไม่เกิน 90 วัน (และยังไม่หมดอายุ)
            $expiringServices = DB::table('customer_products')
                ->where('customer_id', $userId)
                ->where('status', 'active')
                ->whereNotNull('warranty_expire_date')
                ->where('warranty_expire_date', '>=', Carbon::now()) // ยังไม่หมดอายุ
                // ->where('warranty_expire_date', '<=', Carbon::now()->addDays(90)) // (เปิดคอมเมนต์บรรทัดนี้ได้ ถ้าอยากให้โชว์เฉพาะตัวที่จะหมดใน 90 วัน)
                ->orderBy('warranty_expire_date', 'asc') // เอาตัวที่ใกล้หมดอายุสุดขึ้นก่อน
                ->limit(4) // ดึงมาโชว์แค่ 4 ตัว
                ->get();
        }

        // 3. ดึงข้อมูล สิทธิพิเศษแนะนำ (Rewards) สุ่มมา 3 รายการเพื่อให้พอดีกับ Layout หน้าเว็บ
        $recommendedPrivileges = DB::table('rewards')
                                   ->where('is_active', true) // สมมติว่ามีฟิลด์นี้ ถ้าไม่มีให้ลบออกได้ครับ
                                   ->inRandomOrder()
                                   ->limit(3)
                                   ->get();

        // 4. ดึงข้อมูล บริการแนะนำ / สินค้าแนะนำ
        // หมายเหตุ: ใน API เดิมไปดึงจากตาราง rewards ซึ่งอาจจะผิด ผมเลยปรับให้ดึงจาก products แทนครับ
        $recommendedServices = DB::table('products')
                                 ->inRandomOrder()
                                 ->limit(4)
                                 ->get();

        return view('frontend.index', compact(
            'banners',
            'expiringServices',
            'categories',
            'recommendedPrivileges',
            'recommendedServices'
        ));
    }
}
