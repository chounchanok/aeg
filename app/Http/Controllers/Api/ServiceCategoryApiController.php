<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceCategoryApiController extends Controller
{
    public function index(Request $request)
    {
        // รับค่าภาษาจาก Mobile App (ถ้าไม่ส่งมาให้ default เป็น 'th')
        $lang = $request->header('Accept-Language', 'th');

        // ดึงข้อมูลหมวดหมู่ที่เปิดใช้งานอยู่ เรียงตามลำดับ
        $categories = DB::table('service_categories')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($cat) use ($lang) {
                // คืนค่า JSON กลับไปให้แอป ตามภาษาที่ร้องขอ
                return [
                    'id' => $cat->id,
                    'title' => ($lang == 'en' && !empty($cat->title_en)) ? $cat->title_en : $cat->title_th,
                    'image_url' => $cat->image_url,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}