<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\ServiceCategory;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order', 'asc')->get();
        $categories = ServiceCategory::all();

        // เปลี่ยนเส้นทางไปเรียกใช้ไฟล์ resources/views/frontend/index.blade.php
        return view('frontend.index', compact('banners', 'categories'));
    }
}
