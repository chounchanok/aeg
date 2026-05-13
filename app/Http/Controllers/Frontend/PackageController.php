<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // จำเป็นต้องมี
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // จำเป็นต้องมีเพื่อใช้ DB::table
use App\Models\OrderItem;

class PackageController extends Controller
{
    // เพิ่ม Request $request เข้ามาในพารามิเตอร์
    public function index(Request $request, $type)
    {
        dd($type); // ใช้ dd() เพื่อตรวจสอบข้อมูลที่ดึงมาได้ก่อนส่งไปยัง view
        // 1. ตรวจสอบก่อนว่าตาราง products ของคุณมีคอลัมน์ is_active หรือไม่
        // ถ้าไม่มีให้เอา ->where('is_active', true) ออก
        $query = DB::table('products')->where('is_active', true);

        // 2. รองรับการกรองตามหมวดหมู่
        // *หมายเหตุ: ถ้าในตาราง products ไม่มีคอลัมน์ 'type' ให้คอมเมนต์ 3 บรรทัดนี้ไว้ก่อน ไม่งั้นจะ Error*
        // if ($type) {
        //     $query->where('type', $type);
        // }

        $products = $query->orderBy('created_at', 'desc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                // ปรับให้ดึงจากคอลัมน์ที่มีอยู่จริงในฐานข้อมูล
                'name' => $p->name ?? '',
                'description' => $p->description ?? '',
                'price' => $p->price ?? 0,
                'image_url' => $p->image_url ?? null,
            ];
        });


        return view('frontend.packages', compact('products'));
    }

    // แสดงหน้า "แพ็กเกจของฉัน"
    public function myPackages()
    {
        $userId = Auth::id();

        $activeItems = OrderItem::whereHas('order', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->whereIn('status', ['paid', 'processing']);
        })->with('product')->get();

        $historyItems = OrderItem::whereHas('order', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->whereIn('status', ['completed', 'cancelled']);
        })->with('product')->get();

        return view('frontend.packages', compact('activeItems', 'historyItems'));
    }

    // แสดงหน้า "เขียนรีวิว"
    public function feedback($id)
    {
        $item = OrderItem::with('product')->findOrFail($id);
        return view('frontend.package-feedback', compact('item'));
    }
}
