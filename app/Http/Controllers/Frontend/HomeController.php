<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Banner;
use App\Models\ServiceCategory;

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
            'categories',
            'recommendedPrivileges',
            'recommendedServices'
        ));
    }
}
