<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\StaffNotificationService;
use App\Services\SupportChatReferenceService;
use App\Services\SupportChatTopicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\SupportChatSent;

/**
 * แชทติดต่อสอบถาม (ลูกค้า ↔ เจ้าหน้าที่) — ใช้ร่วมกันทั้งหน้าเว็บ (/support-chat) และแอป (/api/support-chats)
 *
 * 🌟 ทุกแชทต้องมี "หัวข้อ" (topic) จากชุดใน config/support_chat.php เพื่อให้ข้อความไปถึงแผนกที่ถูกต้อง
 *    — หลังบ้านจะกรองให้แต่ละแผนกเห็นเฉพาะหัวข้อของตัวเอง
 */
class SupportChatController extends Controller
{
    /**
     * รายการหัวข้อให้ลูกค้าเลือกก่อนเริ่มแชท (public — เว็บและแอปใช้ร่วมกัน)
     */
    public function getTopics()
    {
        return response()->json([
            'status' => 'success',
            'data' => SupportChatTopicService::forCustomer(),
            'default_topic' => SupportChatTopicService::defaultTopic(),
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // ถ้าส่ง ?topic= มาและถูกต้อง จะเปิดแชทหัวข้อนั้นทันที ถ้าไม่ส่ง/ไม่ถูกต้อง หน้าเว็บจะให้ลูกค้าเลือกหัวข้อก่อน
        $topic = $request->query('topic');
        $topic = SupportChatTopicService::isValid($topic) ? $topic : null;

        $messages = $topic
            ? DB::table('support_chats')
                ->where('user_id', $user->id)
                ->where('topic', $topic)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn ($m) => SupportChatReferenceService::present($m))
            : collect();

        return view('frontend.support-chat', compact('messages', 'topic', 'user'));
    }

    public function getHistory(Request $request)
    {
        $topic = $request->query('topic', SupportChatTopicService::defaultTopic());

        if (!SupportChatTopicService::isValid($topic)) {
            return response()->json([
                'status' => 'error',
                'message' => __('หัวข้อแชทไม่ถูกต้อง กรุณาเลือกหัวข้อจากรายการ (GET /support-chats/topics)'),
            ], 422);
        }

        $messages = DB::table('support_chats')
            ->where('user_id', Auth::id())
            ->where('topic', $topic)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($m) => SupportChatReferenceService::present($m)); // 🌟 แนบการ์ด ref (สินค้า/บริการ/ของรางวัล) ถ้ามี

        return response()->json(['status' => 'success', 'topic' => $topic, 'data' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        // 🌟 แนบรายการไปกับข้อความได้ด้วย ref_type + ref_id (ดู SupportChatReferenceService::TYPES)
        //    - ส่งแค่การ์ดอย่างเดียว (ไม่มี message) ได้
        //    - ไม่ส่ง topic มาได้ถ้ามี ref_type — ระบบเลือกหัวข้อให้ตามประเภทรายการ
        $request->validate([
            'topic' => 'nullable|required_without:ref_type|string|in:' . implode(',', array_keys(SupportChatTopicService::topics())),
            'message' => 'nullable|required_without:ref_type|string|max:2000',
            'ref_type' => 'nullable|string|in:' . implode(',', SupportChatReferenceService::types()),
            'ref_id' => 'nullable|required_with:ref_type|integer|min:1',
        ], [
            'topic.required_without' => __('กรุณาเลือกหัวข้อที่ต้องการสอบถาม'),
            'topic.in' => __('หัวข้อแชทไม่ถูกต้อง กรุณาเลือกหัวข้อจากรายการ'),
            'message.required_without' => __('กรุณาพิมพ์ข้อความ'),
            'ref_type.in' => __('ประเภทรายการที่แนบไม่ถูกต้อง'),
            'ref_id.required_with' => __('กรุณาระบุรายการที่ต้องการแนบ'),
        ]);

        $user = Auth::user();

        $refSnapshot = null;
        if ($request->filled('ref_type')) {
            $refSnapshot = SupportChatReferenceService::resolve($request->ref_type, (int) $request->ref_id, (int) $user->id);
            if (!$refSnapshot) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('ไม่พบรายการที่แนบ หรือรายการนี้ไม่ใช่ของคุณ'),
                ], 422);
            }
        }

        $topic = $request->filled('topic')
            ? $request->topic
            : SupportChatReferenceService::topicFor($request->ref_type, $refSnapshot);
        $text = trim((string) $request->message);

        // ดูข้อความล่าสุดของเธรดนี้ก่อนบันทึก — ใช้ตัดสินว่าต้องแจ้งเตือนแผนกไหม (ดูด้านล่าง)
        $lastMessage = DB::table('support_chats')
            ->where('user_id', $user->id)
            ->where('topic', $topic)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        // 1. บันทึกลง DB — แชททั่วไป (ไม่มี ref) ไม่แตะคอลัมน์ ref_* เลย
        //    จึงส่งข้อความธรรมดาได้แม้เซิร์ฟเวอร์ยังไม่ได้รัน migration 2026_10_12_110000
        $row = [
            'user_id' => $user->id,
            'topic' => $topic,
            'message' => $text,
            'sender_type' => 'customer',
            'created_at' => now(),
            'updated_at' => now()
        ];
        if ($refSnapshot) {
            $row['ref_type'] = $request->ref_type;
            $row['ref_id'] = (int) $request->ref_id;
            $row['ref_snapshot'] = json_encode($refSnapshot, JSON_UNESCAPED_UNICODE);
        }
        $messageId = DB::table('support_chats')->insertGetId($row);

        // 2. ดึงข้อมูลที่เพิ่งสร้างเพื่อเอาไป Broadcast
        $newMessage = SupportChatReferenceService::present(DB::table('support_chats')->where('id', $messageId)->first());

        // 3. ส่ง Event ไปที่ Pusher (ครอบ try-catch กันแชทพังถ้า Pusher ล่ม เหมือนฝั่งแอดมิน)
        try {
            broadcast(new SupportChatSent($newMessage));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Pusher Error (Customer): ' . $e->getMessage());
        }

        // 4. 🌟 แจ้งเตือนแผนกเจ้าของหัวข้อ เฉพาะตอน "เริ่มรอบใหม่" (ข้อความแรกของเธรด หรือลูกค้าพิมพ์มาหลังจาก
        //    เจ้าหน้าที่ตอบไปแล้ว) — ไม่แจ้งทุกข้อความ กันแจ้งเตือนถล่มตอนลูกค้าพิมพ์รัวหลายบรรทัด
        if (!$lastMessage || $lastMessage->sender_type === 'admin') {
            $profile = DB::table('customer_profiles')->where('user_id', $user->id)->select('first_name', 'last_name')->first();
            $displayName = trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: ($user->username ?? $user->phone ?? 'ลูกค้า');
            StaffNotificationService::notifyRoles(
                SupportChatTopicService::roleKeysForTopic($topic),
                'มีแชทสอบถามใหม่: ' . SupportChatTopicService::label($topic),
                "{$displayName}: " . \Illuminate\Support\Str::limit($text !== '' ? $text : ('📎 ' . ($refSnapshot['title'] ?? '')), 80),
                '/admin/support-chats/' . $user->id . '?topic=' . $topic,
                'support_chat'
            );
        }

        return response()->json(['status' => 'success', 'data' => $newMessage]);
    }
}
