<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageReview;
use Illuminate\Http\Request;

class ReviewAdminController extends Controller
{
    public function index()
    {
        // ดึงรีวิวเรียงตามใหม่สุด พร้อมข้อมูลคนรีวิวและชื่อสินค้า
        $reviews = PackageReview::with(['user', 'orderItem'])->orderBy('created_at', 'desc')->get();

        // ส่งตัวแปรหลอกให้เมนูด้านข้างทำงานได้ (เหมือนไฟล์ประกัน)
        $first_level_active_index = 'reviews';
        $second_level_active_index = '';
        $third_level_active_index = '';

        return view('admin.reviews.index', compact('reviews', 'first_level_active_index', 'second_level_active_index', 'third_level_active_index'));
    }
}
