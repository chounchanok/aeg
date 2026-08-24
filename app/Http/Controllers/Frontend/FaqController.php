<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    /**
     * หน้า FAQ (คำถามที่พบบ่อย) ฝั่งลูกค้า
     * ดึงข้อมูลชุดเดียวกับที่แชทบอทใช้ตอบ (chatbot_topics > chatbot_services > chatbot_service_faqs)
     * เพื่อให้เนื้อหาตรงกันทั้งสองที่ แก้ไขจากหลังบ้านที่เดียวก็อัปเดตทั้งคู่
     */
    public function index()
    {
        $topics = DB::table('chatbot_topics')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $faqs = DB::table('chatbot_service_faqs as f')
            ->join('chatbot_services as s', 'f.service_id', '=', 's.id')
            ->where('f.is_active', true)
            ->where('s.is_active', true)
            ->orderBy('s.sort_order', 'asc')
            ->orderBy('f.sort_order', 'asc')
            ->select('f.id', 'f.question_th', 'f.answer_th', 's.id as service_id', 's.name_th as service_name', 's.topic_id')
            ->get()
            ->groupBy('topic_id');

        // จัดกลุ่ม FAQ ให้อยู่ใต้ topic ของตัวเอง (เฉพาะ topic ที่มีคำถามจริง)
        $topicsWithFaqs = $topics->map(function ($topic) use ($faqs) {
            $topic->faqs = $faqs->get($topic->id, collect());
            return $topic;
        })->filter(function ($topic) {
            return $topic->faqs->isNotEmpty();
        })->values();

        return view('frontend.faq', [
            'topicsWithFaqs' => $topicsWithFaqs,
        ]);
    }
}
