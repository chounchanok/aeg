<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // แสดงรายการสินค้าทั้งหมด
    public function index($type = null)
    {
        // 1. ดึงข้อมูลพร้อมแบ่งหน้า
        $products = DB::table('products')->where('is_active', true)
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // 2. แปลงข้อมูลใน Paginator ให้แปลง name_th เป็น name
        $products->getCollection()->transform(function ($p) {
            $p->name = $p->name_th ?? $p->name_en ?? 'ไม่มีชื่อสินค้า';
            $p->description = $p->description_th ?? $p->description_en ?? '';
            return $p;
        });

        return view('frontend.product-catagry', compact('products'));
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
}
