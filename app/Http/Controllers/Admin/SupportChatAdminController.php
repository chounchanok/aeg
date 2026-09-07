<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupportChatTopicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\SupportChatSent;

/**
 * แชทติดต่อสอบถาม (หลังบ้าน)
 *
 * 🌟 RBAC ระดับหัวข้อ: แต่ละแผนกเห็น/ตอบได้เฉพาะหัวข้อของตัวเองตาม config/support_chat.php
 *    (เช่น Insurance เห็นเฉพาะ 'insurance', Sec Admin เห็น 'security-system' + 'technician-service')
 *    IT (full access) และ super_admin เดิม เห็นทุกหัวข้อ — permission 'support_chats.reply' เป็นแค่ประตูเข้าเมนู
 */
class SupportChatAdminController extends Controller
{
    // 1. หน้า List แสดงรายการแชท (จัดกลุ่มตาม User และหัวข้อ) — เฉพาะหัวข้อที่แผนกนี้ดูแล
    public function index(Request $request)
    {
        $allowedTopics = SupportChatTopicService::allowedTopicsForUser(Auth::user());
        $seesAllTopics = SupportChatTopicService::hasFullAccess(Auth::user()); // IT เห็นทุกหัวข้อ รวมหัวข้อเก่านอก config
        $topicFilter = $request->query('topic', 'all');
        if ($topicFilter !== 'all' && !in_array($topicFilter, $allowedTopics, true)) {
            $topicFilter = 'all';
        }

        $query = DB::table('support_chats')
            ->join('users', 'support_chats.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->when(!$seesAllTopics, fn ($q) => $q->whereIn('support_chats.topic', $allowedTopics))
            ->select(
                'support_chats.user_id',
                'support_chats.topic',
                'users.username',
                'users.phone',
                'customer_profiles.first_name',
                'customer_profiles.last_name',
                DB::raw('MAX(support_chats.created_at) as last_contact'),
                DB::raw('COUNT(*) as message_count'),
                // ใครเป็นคนพิมพ์ข้อความล่าสุดของเธรดนี้ — ถ้าเป็นลูกค้า = ยังรอเจ้าหน้าที่ตอบ
                DB::raw('(SELECT s2.sender_type FROM support_chats s2
                          WHERE s2.user_id = support_chats.user_id AND s2.topic = support_chats.topic
                          ORDER BY s2.created_at DESC, s2.id DESC LIMIT 1) as last_sender_type')
            )
            ->groupBy('support_chats.user_id', 'support_chats.topic', 'users.username', 'users.phone', 'customer_profiles.first_name', 'customer_profiles.last_name')
            ->orderBy('last_contact', 'desc');

        if ($topicFilter !== 'all') {
            $query->where('support_chats.topic', $topicFilter);
        }

        $chats = $query->get();

        // จำนวนเธรดที่ "รอตอบ" แยกตามหัวข้อ สำหรับแท็บด้านบน (นับเฉพาะหัวข้อที่แผนกนี้เห็น)
        $waitingByTopic = DB::table('support_chats as s')
            ->when(!$seesAllTopics, fn ($q) => $q->whereIn('s.topic', $allowedTopics))
            ->whereRaw('s.id = (SELECT s2.id FROM support_chats s2 WHERE s2.user_id = s.user_id AND s2.topic = s.topic ORDER BY s2.created_at DESC, s2.id DESC LIMIT 1)')
            ->where('s.sender_type', 'customer')
            ->select('s.topic', DB::raw('COUNT(*) as total'))
            ->groupBy('s.topic')
            ->pluck('total', 'topic');

        return view('admin.support-chats.index', [
            'chats' => $chats,
            'allowedTopics' => $allowedTopics,
            'topicFilter' => $topicFilter,
            'topicLabels' => array_map(fn ($t) => $t['label'], SupportChatTopicService::topics()),
            'departmentNames' => SupportChatTopicService::departmentNames(),
            'waitingByTopic' => $waitingByTopic,
            'first_level_active_index' => 'support-chats',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 2. หน้าต่างแชท (ดูข้อความของ User คนนี้ ในหัวข้อนี้)
    public function show(Request $request, $user_id)
    {
        $topic = $request->query('topic', SupportChatTopicService::defaultTopic());
        $this->authorizeTopic($topic);

        $customer = DB::table('users')->where('id', $user_id)->first();
        if (!$customer) abort(404);

        $messages = DB::table('support_chats')
            ->where('user_id', $user_id)
            ->where('topic', $topic)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.support-chats.show', [
            'messages' => $messages,
            'customer' => $customer,
            'topic' => $topic,
            'topicLabel' => SupportChatTopicService::label($topic),
            'departmentName' => SupportChatTopicService::departmentNames()[$topic] ?? '-',
            'first_level_active_index' => 'support-chats',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 3. แอดมินกดส่งข้อความตอบกลับ
    public function reply(Request $request, $user_id)
    {
        $request->validate([
            'topic' => 'required|string',
            'message' => 'required|string|max:2000'
        ]);

        $this->authorizeTopic($request->topic);

        $messageId = DB::table('support_chats')->insertGetId([
            'user_id' => $user_id,
            'topic' => $request->topic,
            'message' => $request->message,
            'sender_type' => 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $newMessage = DB::table('support_chats')->where('id', $messageId)->first();

        // 🌟 ครอบด้วย try-catch
        try {
            broadcast(new SupportChatSent($newMessage));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Pusher Error (Admin): ' . $e->getMessage());
        }

        return response()->json(['status' => 'success', 'data' => $newMessage]);
    }

    /**
     * 403 ถ้าแผนกของผู้ใช้ไม่ได้ดูแลหัวข้อนี้ (หรือหัวข้อไม่อยู่ใน config)
     */
    protected function authorizeTopic(?string $topic): void
    {
        if (!SupportChatTopicService::canAccessTopic(Auth::user(), $topic)) {
            abort(403, 'แผนกของคุณไม่ได้รับผิดชอบแชทหัวข้อนี้ กรุณาติดต่อฝ่าย IT หากต้องการสิทธิ์เพิ่มเติม');
        }
    }
}
