<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ChatbotController
 *
 * รองรับแชทบอทเมนูปุ่มกดหน้า support-chat (เว็บ) และแอปมือถือ (API)
 * - ดูเมนู/ข้อมูลบริการ: เปิดให้ทุกคนดูได้ แม้ยังไม่ล็อกอิน/ยังไม่สมัครสมาชิก
 * - ถามคำถามเพิ่มเติม (keyword search) และสนใจซื้อบริการ/แจ้งเคลม: ต้องล็อกอินก่อน
 *   ถ้ายังไม่ล็อกอินจะได้ require_membership = true กลับไป ให้ฝั่ง client เด้งไปหน้าสมัครสมาชิก/ล็อกอิน
 */
class ChatbotController extends Controller
{
    use ApiResponseTrait;

    public function topics(ChatbotService $bot)
    {
        return $this->successResponse($bot->getTopics(), 'Topics retrieved');
    }

    public function services(Request $request, int $topicId, ChatbotService $bot)
    {
        $topic = $bot->getTopic($topicId);
        if (!$topic) {
            return $this->errorResponse('ไม่พบหมวดที่ต้องการ', 404);
        }

        return $this->successResponse($bot->getServices($topicId), 'Services retrieved');
    }

    public function serviceDetail(int $serviceId, ChatbotService $bot)
    {
        $detail = $bot->getServiceDetail($serviceId);
        if (!$detail) {
            return $this->errorResponse('ไม่พบบริการที่ต้องการ', 404);
        }

        return $this->successResponse($detail, 'Service detail retrieved');
    }

    public function keywordSearch(Request $request, ChatbotService $bot)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'topic_id' => 'nullable|integer',
        ]);

        if (!$this->isMember($request)) {
            return $this->successResponse([
                'require_membership' => true,
                'matched' => false,
            ], 'ต้องสมัครสมาชิกก่อนจึงจะถามคำถามเพิ่มเติมได้');
        }

        $result = $bot->keywordSearch($request->message, $request->topic_id);
        $result['require_membership'] = false;

        if (!$result['matched']) {
            $result['escalation_message'] = $bot->getEscalationMessage();
        }

        return $this->successResponse($result, $result['matched'] ? 'พบคำตอบ' : 'ยังไม่พบคำตอบที่ตรงกับคำถามนี้');
    }

    /**
     * สนใจซื้อบริการ / แจ้งเคลม (lead ที่จับจากแชทบอท)
     */
    public function submitLead(Request $request)
    {
        $request->validate([
            'type' => 'required|in:purchase,claim',
            'topic_key' => 'nullable|string|max:100',
            'service_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email',
            'message' => 'nullable|string|max:2000',
        ]);

        if (!$this->isMember($request)) {
            return $this->successResponse([
                'require_membership' => true,
            ], 'ต้องสมัครสมาชิกก่อนจึงจะดำเนินการต่อได้');
        }

        $userId = $this->currentUserId($request);

        DB::table('chatbot_purchase_leads')->insert([
            'user_id' => $userId,
            'type' => $request->type,
            'topic_key' => $request->topic_key,
            'service_name' => $request->service_name,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message,
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(
            ['require_membership' => false],
            'ขอบคุณครับ ทีมงานจะติดต่อกลับโดยเร็วที่สุด'
        );
    }

    /**
     * ให้คะแนนความพึงพอใจตอนจบการสนทนา (บังคับก่อนปิดแชทตาม spec)
     */
    public function submitRating(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::table('chatbot_ratings')->insert([
            'user_id' => $this->currentUserId($request),
            'rating' => $request->rating,
            'comment' => $request->comment,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(null, 'ขอบคุณสำหรับคะแนนครับ');
    }

    protected function isMember(Request $request): bool
    {
        return Auth::guard('sanctum')->check() || Auth::check();
    }

    protected function currentUserId(Request $request): ?int
    {
        if (Auth::guard('sanctum')->check()) {
            return Auth::guard('sanctum')->id();
        }
        if (Auth::check()) {
            return Auth::id();
        }
        return null;
    }
}
