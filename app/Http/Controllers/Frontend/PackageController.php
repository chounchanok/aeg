<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderItem;

class PackageController extends Controller
{

    public function index($type)
    {
        $lang = $request->header('Accept-Language', 'th');
        
        $query = DB::table('products')->where('is_active', true);

        // รองรับการกรองตามหมวดหมู่
        if ($type) {
            $query->where('type', $type);
        }

        $products = $query->orderBy('created_at', 'desc')->get()->map(function ($p) use ($lang) {
            return [
                'id' => $p->id,
                'name' => ($lang == 'en' && !empty($p->name_en)) ? $p->name_en : $p->name_th,
                'description' => ($lang == 'en' && !empty($p->description_en)) ? $p->description_en : $p->description_th,
                'type' => $p->type,
                'price' => $p->price,
                'compare_at_price' => $p->compare_at_price,
                'image_url' => $p->image_url,
                'point_earn' => $p->point_earn,
            ];
        });
        return view('frontend.packages', compact('products'));
    }

    // แสดงหน้า "แพ็กเกจของฉัน" (แยก Active และ History)
    public function myPackages()
    {
        $userId = Auth::id();

        // ดึงรายการสินค้าที่สถานะกำลังใช้งาน (เช่น paid, processing)
        $activeItems = OrderItem::whereHas('order', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->whereIn('status', ['paid', 'processing']);
        })->with('product')->get();

        // ดึงรายการสินค้าที่สถานะหมดอายุ/เสร็จสิ้นแล้ว (เช่น completed, cancelled)
        $historyItems = OrderItem::whereHas('order', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->whereIn('status', ['completed', 'cancelled']);
        })->with('product')->get();

        return view('frontend.packages', compact('activeItems', 'historyItems'));
    }

    // แสดงหน้า "เขียนรีวิว"
    public function feedback($id)
    {
        // ดึงข้อมูล Item ที่ต้องการรีวิว
        $item = OrderItem::with('product')->findOrFail($id);
        return view('frontend.package-feedback', compact('item'));
    }
}