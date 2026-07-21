<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ServiceCategory;

class ProductController extends Controller
{
    // แสดงรายการสินค้าทั้งหมด
    public function index($group = 'package', $categoryId = null)
    {
        // 1. ดึงหมวดหมู่ผ่าน Model ServiceCategory
        $categories = ServiceCategory::where('is_active', true)
            ->where('group', $group)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 2. ดึงสินค้าผ่าน Model Product และใช้ whereHas เพื่อกรองข้อมูลตัวแม่
        $query = Product::with('category') // ดึงข้อมูล category มาเผื่อใช้ใน Blade
            ->where('is_active', true)
            ->whereHas('category', function ($q) use ($group) {
                // เงื่อนไขนี้จะไปเช็คว่าหมวดหมู่ของสินค้านี้ มี group ตรงกับที่เลือกไหม
                $q->where('group', $group);
            });

        // 3. ถ้ามีการเลือกหมวดหมู่ย่อย
        if ($categoryId) {
            $query->where('type', $categoryId); 
        }

        // เรียงลำดับและแบ่งหน้า
        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        // 4. แปลงภาษา
        $products->getCollection()->transform(function ($p) {
            $p->name = $p->name_th ?? $p->name_en ?? 'ไม่มีชื่อสินค้า';
            $p->description = $p->description_th ?? $p->description_en ?? '';
            return $p;
        });

        $currentGroup = $group;
        $currentCategoryId = $categoryId;

        return view('frontend.product-catagry', compact('products', 'categories', 'currentGroup', 'currentCategoryId'));
    }

    // แสดงรายละเอียดสินค้า 1 ชิ้น
    public function show($id)
    {
        $product = DB::table('products')->where('id', $id)->where('is_active', true)->first();

        if (!$product) {
            abort(404, 'ไม่พบสินค้านี้');
        }

        // จัดการเรื่องชื่อคอลัมน์ภาษา ให้ตรงกับหน้า Blade
        $product->name = $product->name_th ?? $product->name_en ?? 'ไม่มีชื่อสินค้า';
        $product->description = $product->description_th ?? $product->description_en ?? 'ยังไม่มีรายละเอียดสินค้า';

        return view('frontend.product-detail', compact('product'));
    }

    public function contact($id)
    {
        // ดึงข้อมูลประกันเพื่อเอาไปโชว์หัวข้อในหน้าติดต่อ
        $product = DB::table('products')->where('id', $id)->where('is_active', true)->firstOrFail();
        
        return view('frontend.product-contact', compact('product'));
    }

    // ==========================================
    // บันทึกข้อมูลฟอร์มติดต่อฝ่ายขาย
    // ==========================================
    public function submitContact(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_time' => 'required|string',
            'message' => 'nullable|string'
        ]);

        try {
            // บันทึกลง Database
            DB::table('product_contacts')->insert([
                'product_id' => $request->product_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'contact_time' => $request->contact_time,
                'message' => $request->message,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ส่ง JSON กลับไปให้ Frontend (เพื่อเรียกโชว์ Modal)
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

    // 🌟 ดึงข้อมูลตู้เซฟไปโชว์หน้าฟอร์ม
    public function safeContact($id)
    {
        $locker = DB::table('smart_lockers')->where('id', $id)->where('is_active', true)->first();
        if (!$locker) abort(404, 'ไม่พบข้อมูลตู้เซฟนี้');
        
        return view('frontend.safe-contact', compact('locker'));
    }

    // 🌟 บันทึกข้อมูลฟอร์มติดต่อตู้เซฟ
    public function submitSafeContact(Request $request)
    {
        $request->validate([
            'smart_locker_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_time' => 'required|string',
            'message' => 'nullable|string'
        ]);

        try {
            DB::table('safe_contacts')->insert([
                'smart_locker_id' => $request->smart_locker_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'contact_time' => $request->contact_time,
                'message' => $request->message,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
