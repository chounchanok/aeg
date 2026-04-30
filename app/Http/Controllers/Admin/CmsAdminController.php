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
    // 1. ดึงแบนเนอร์ (อัปเดตให้เรียงตาม sort_order ก่อน แล้วค่อยเรียงตามวันที่สร้าง)
    public function banners()
    {
        $banners_raw = DB::table('banners');
        if(request()->has('location')) {
            $banners_raw->where('location', request()->location);
        }else{
            $banners_raw->where('location', 'main'); 
        }
        $banners = $banners_raw->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.banners', [
            'banners' => $banners,
            'locations' => request()->location ?? 'main',
            'first_level_active_index' => 'cms',
            'second_level_active_index' => 'banners',
            'third_level_active_index' => ''
        ]);
    }

    // 2. ฟังก์ชันอัปเดตตอนสร้างใหม่ (เพิ่ม sort_order)
    public function storeBanner(Request $request)
    {
        $request->validate([
            'title_th' => 'required|string',
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
            'sort_order' => $request->sort_order ?? 0, // 🌟 เพิ่มบรรทัดนี้
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'อัปโหลดแบนเนอร์เรียบร้อยแล้ว');
    }

    // 3. 🌟 ฟังก์ชันใหม่ สำหรับแก้ไขข้อมูล
    public function updateBanner(Request $request, $id)
    {
        $request->validate([
            'title_th' => 'required|string',
            'location' => 'required|in:main,ease_club,service',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // ไม่บังคับอัปโหลดรูปใหม่
        ]);

        $banner = DB::table('banners')->where('id', $id)->first();
        $imageUrl = $banner->image_url;

        // ถ้ามีการอัปโหลดรูปใหม่ ให้เซฟทับตัวแปรเดิม
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('banners')->where('id', $id)->update([
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'image_url' => $imageUrl,
            'location' => $request->location,
            'sort_order' => $request->sort_order ?? 0, // 🌟 อัปเดตลำดับ
            'is_active' => $request->has('is_active'),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'แก้ไขแบนเนอร์เรียบร้อยแล้ว');
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

    // ==========================================
    // ส่วนที่ 3: จัดการสิทธิประโยชน์ (EASE CLUB / Rewards)
    // ==========================================
    public function easeClub()
    {
        // ดึงข้อมูลของรางวัล พร้อมชื่อหมวดหมู่
        $rewards = DB::table('rewards')
            ->join('reward_categories', 'rewards.category_id', '=', 'reward_categories.id')
            ->select('rewards.*', 'reward_categories.name as category_name')
            ->orderBy('rewards.created_at', 'desc')
            ->get();

        // ดึงหมวดหมู่มาโชว์ใน Dropdown ตอนเพิ่มข้อมูล
        $categories = DB::table('reward_categories')->get();

        return view('admin.cms.ease-club', [
            'rewards' => $rewards,
            'categories' => $categories,
            'first_level_active_index' => 'cms',
            'second_level_active_index' => 'ease-club',
            'third_level_active_index' => ''
        ]);
    }

    public function storeReward(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'title_th' => 'required|string',
            'points_required' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('rewards', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('rewards')->insert([
            'category_id' => $request->category_id,
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'description_th' => $request->description_th,
            'description_en' => $request->description_en,
            'points_required' => $request->points_required,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'minimum_tier_required' => $request->minimum_tier_required, // เช่น Advance, Platinum หรือ null
            'image_url' => $imageUrl,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มของรางวัลเรียบร้อยแล้ว');
    }

    public function updateReward(Request $request, $id)
    {
        $request->validate([
            'title_th' => 'required|string',
            'points_required' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $reward = DB::table('rewards')->where('id', $id)->first();
        $imageUrl = $reward->image_url;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('rewards', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('rewards')->where('id', $id)->update([
            'category_id' => $request->category_id,
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'description_th' => $request->description_th,
            'description_en' => $request->description_en,
            'points_required' => $request->points_required,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'minimum_tier_required' => $request->minimum_tier_required,
            'image_url' => $imageUrl,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'แก้ไขของรางวัลเรียบร้อยแล้ว');
    }

    public function deleteReward($id)
    {
        DB::table('rewards')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'ลบของรางวัลเรียบร้อยแล้ว');
    }
}