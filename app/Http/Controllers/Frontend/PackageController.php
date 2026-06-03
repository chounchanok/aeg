<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // จำเป็นต้องมี
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // จำเป็นต้องมีเพื่อใช้ DB::table
use App\Models\OrderItem;
use App\Models\PackageReview;

class PackageController extends Controller
{
    // เพิ่ม Request $request เข้ามาในพารามิเตอร์
    public function index(Request $request, $type)
    {
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

    public function packagesServices()
    {
        $products = DB::table('products')->where('type', 5)->get();
        return view('frontend.service-package', compact('products'));
    }

    // รับค่าจากฟอร์มเพื่อบันทึกลง Database
    public function submitFeedback(Request $request, $id)
    {
        $request->validate([
            'install_rating' => 'required|integer|min:1|max:5',
            'sales_rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string'
        ]);

        $user = Auth::user();

        // เช็คว่าเคยรีวิวรายการนี้ไปแล้วหรือยัง
        $existingReview = PackageReview::where('order_item_id', $id)->where('user_id', $user->id)->first();
        if ($existingReview) {
            return back()->with('error', 'คุณได้ให้คะแนนแพ็กเกจนี้ไปแล้ว');
        }

        DB::beginTransaction();
        try {
            // บันทึกรีวิว
            PackageReview::create([
                'order_item_id' => $id,
                'user_id' => $user->id,
                'install_rating' => $request->install_rating,
                'sales_rating' => $request->sales_rating,
                'review_text' => $request->review_text
            ]);

            // แจก 1 EASE Coin ตามแบนเนอร์ที่คุณทำไว้
            DB::table('customer_wallets')->where('user_id', $user->id)->increment('current_points', 1);

            DB::table('point_transactions')->insert([
                'user_id' => $user->id,
                'amount' => 1,
                'type' => 'earn',
                'description' => 'ได้รับพอยท์จากการรีวิวแพ็กเกจ',
                'created_at' => now()
            ]);

            DB::commit();
            return redirect()->route('packages.mine')->with('success', 'ขอบคุณสำหรับคำติชม คุณได้รับ 1 EASE Coin แล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาดในการส่งข้อมูล: ' . $e->getMessage());
        }
    }
}
