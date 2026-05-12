<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // แสดงรายการสินค้าทั้งหมด
    public function index()
    {
        // ดึงสินค้าที่ is_active = true พร้อมทำ Pagination หน้าละ 12 ชิ้น
        $products = DB::table('products')->where('is_active', true)->paginate(12);
        
        return view('frontend.product-catagry', compact('products'));
    }

    // แสดงรายละเอียดสินค้า 1 ชิ้น
    public function show($id)
    {
        $product = DB::table('products')->where('id', $id)->where('is_active', true)->first();
        
        if (!$product) {
            abort(404, 'ไม่พบสินค้านี้');
        }

        return view('frontend.product-detail', compact('product'));
    }
}