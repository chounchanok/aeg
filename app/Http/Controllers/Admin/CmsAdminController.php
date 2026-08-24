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

    // 2. ฟังก์ชันอัปเดตตอนสร้างใหม่ (เพิ่มรูป Mobile)
    public function storeBanner(Request $request)
    {
        $request->validate([
            'title_th' => 'required|string',
            'location' => 'required|in:main,ease_club,service',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'image_m' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240' // 🌟 รับรูป Mobile (ไม่บังคับ)
        ]);

        $imageUrl = '';
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $imageUrl = url('storage/' . $path);
        }

        // 🌟 จัดการอัปโหลดรูป Mobile
        $imageUrlM = null;
        if ($request->hasFile('image_m')) {
            $pathM = $request->file('image_m')->store('banners/mobile', 'public');
            $imageUrlM = url('storage/' . $pathM);
        }

        DB::table('banners')->insert([
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'image_url' => $imageUrl,
            'image_url_m' => $imageUrlM, // 🌟 บันทึกรูป Mobile ลง Database
            'location' => $request->location,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'อัปโหลดแบนเนอร์เรียบร้อยแล้ว');
    }

    // 3. ฟังก์ชันสำหรับแก้ไขข้อมูล (เพิ่มรูป Mobile)
    public function updateBanner(Request $request, $id)
    {
        $request->validate([
            'title_th' => 'required|string',
            'location' => 'required|in:main,ease_club,service',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'image_m' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240' // 🌟 รับรูป Mobile
        ]);

        $banner = DB::table('banners')->where('id', $id)->first();
        $imageUrl = $banner->image_url;
        $imageUrlM = $banner->image_url_m; // 🌟 เก็บค่ารูป Mobile เดิมไว้ก่อน

        // ถ้าอัปโหลดรูป Desktop ใหม่
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $imageUrl = url('storage/' . $path);
        }

        // 🌟 ถ้าอัปโหลดรูป Mobile ใหม่
        if ($request->hasFile('image_m')) {
            $pathM = $request->file('image_m')->store('banners/mobile', 'public');
            $imageUrlM = url('storage/' . $pathM);
        }

        DB::table('banners')->where('id', $id)->update([
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'image_url' => $imageUrl,
            'image_url_m' => $imageUrlM, // 🌟 อัปเดตรูป Mobile
            'location' => $request->location,
            'sort_order' => $request->sort_order ?? 0,
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
        // 🌟 เปลี่ยนมาใช้ชุดข้อมูลเดียวกับที่ Chat Bot ใช้ตอบ (chatbot_service_faqs)
        // แทนตาราง faqs เดิม เพื่อให้แก้ไขจากที่นี่ที่เดียว มีผลทั้งหน้าเว็บ FAQ และคำตอบของบอท
        $faqs = DB::table('chatbot_service_faqs as f')
            ->join('chatbot_services as s', 'f.service_id', '=', 's.id')
            ->join('chatbot_topics as t', 's.topic_id', '=', 't.id')
            ->orderBy('t.sort_order', 'asc')
            ->orderBy('s.sort_order', 'asc')
            ->orderBy('f.sort_order', 'asc')
            ->select(
                'f.id', 'f.question_th', 'f.answer_th', 'f.sort_order', 'f.is_active',
                's.id as service_id', 's.name_th as service_name',
                't.id as topic_id', 't.name_th as topic_name'
            )
            ->get();

        // สำหรับ dropdown หมวด > บริการ ในฟอร์มเพิ่ม/แก้ไข
        $topics = DB::table('chatbot_topics')->where('is_active', true)->orderBy('sort_order')->get();
        $services = DB::table('chatbot_services')->where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.cms.faqs', [
            'faqs' => $faqs,
            'topics' => $topics,
            'services' => $services,
            'first_level_active_index' => 'cms',
            'second_level_active_index' => 'faqs',
            'third_level_active_index' => ''
        ]);
    }

    public function storeFaq(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:chatbot_services,id',
            'question_th' => 'required|string',
            'answer_th' => 'required|string',
        ]);

        DB::table('chatbot_service_faqs')->insert([
            'service_id' => $request->service_id,
            'question_th' => $request->question_th,
            'answer_th' => $request->answer_th,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มคำถาม FAQ เรียบร้อยแล้ว');
    }

    public function updateFaq(Request $request, $id)
    {
        $request->validate([
            'service_id' => 'required|exists:chatbot_services,id',
            'question_th' => 'required|string',
            'answer_th' => 'required|string',
        ]);

        DB::table('chatbot_service_faqs')->where('id', $id)->update([
            'service_id' => $request->service_id,
            'question_th' => $request->question_th,
            'answer_th' => $request->answer_th,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'แก้ไขคำถาม FAQ เรียบร้อยแล้ว');
    }

    public function deleteFaq($id)
    {
        DB::table('chatbot_service_faqs')->where('id', $id)->delete();
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
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