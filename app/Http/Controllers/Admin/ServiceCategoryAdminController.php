<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceCategoryAdminController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลหมวดหมู่ เรียงตามลำดับ sort_order
        $categories = DB::table('service_categories')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.service-categories.index', [
            'categories' => $categories,
            'first_level_active_index' => 'service-categories',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_th' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048'
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('service-categories', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('service_categories')->insert([
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'image_url' => $imageUrl,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มหมวดหมู่บริการเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title_th' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048'
        ]);

        $category = DB::table('service_categories')->where('id', $id)->first();
        $imageUrl = $category->image_url;

        // ถ้ามีการอัปโหลดรูปใหม่
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('service-categories', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('service_categories')->where('id', $id)->update([
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'image_url' => $imageUrl,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'แก้ไขหมวดหมู่บริการเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        DB::table('service_categories')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'ลบหมวดหมู่บริการเรียบร้อยแล้ว');
    }
}