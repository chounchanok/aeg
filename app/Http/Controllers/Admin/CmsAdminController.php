<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CmsAdminController extends Controller
{
    // ==========================================
    // ส่วนที่ 1: จัดการแบนเนอร์ (Banners)
    // ==========================================
    public function banners()
    {
        $banners = DB::table('banners')->orderBy('created_at', 'desc')->get();
        return view('admin.cms.banners', [
            'banners' => $banners,
            'first_level_active_index' => 'cms',
            'second_level_active_index' => 'banners',
            'third_level_active_index' => ''
        ]);
    }

    public function storeBanner(Request $request)
    {
        $request->validate([
            'title_th' => 'required|string',
            'title_en' => 'required|string',
            'location' => 'required|in:main,ease_club,service',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $imageUrl = '';
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('banners')->insert([
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'image_url' => $imageUrl,
            'location' => $request->location,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'อัปโหลดแบนเนอร์เรียบร้อยแล้ว');
    }

    public function deleteBanner($id)
    {
        DB::table('banners')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'ลบแบนเนอร์เรียบร้อยแล้ว');
    }

    // ==========================================
    // ส่วนที่ 2: จัดการคำถามที่พบบ่อย (FAQs)
    // ==========================================
    public function faqs()
    {
        $faqs = DB::table('faqs')->orderBy('sort_order', 'asc')->get();
        return view('admin.cms.faqs', [
            'faqs' => $faqs,
            'first_level_active_index' => 'cms',
            'second_level_active_index' => 'faqs',
            'third_level_active_index' => ''
        ]);
    }

    public function storeFaq(Request $request)
    {
        $request->validate([
            'question_th' => 'required|string',
            'question_en' => 'required|string',
            'answer_th' => 'required|string',
            'answer_en' => 'required|string',
            'category' => 'nullable|string'
        ]);

        DB::table('faqs')->insert([
            'category' => $request->category ?? 'ทั่วไป',
            'question_th' => $request->question_th,
            'question_en' => $request->question_en,
            'answer_th' => $request->answer_th,
            'answer_en' => $request->answer_en,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มคำถาม FAQ เรียบร้อยแล้ว');
    }

    public function deleteFaq($id)
    {
        DB::table('faqs')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'ลบคำถาม FAQ เรียบร้อยแล้ว');
    }
}