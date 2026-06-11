<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\SupportChatSent; // 🌟 ต้องเพิ่มบรรทัดนี้

class SupportChatAdminController extends Controller
{
    // 1. หน้า List แสดงรายการแชท (จัดกลุ่มตาม User และหัวข้อ)
    public function index()
    {
        $chats = DB::table('support_chats')
            ->join('users', 'support_chats.user_id', '=', 'users.id')
            ->select(
                'support_chats.user_id',
                'support_chats.topic',
                'users.username',
                'users.phone',
                DB::raw('MAX(support_chats.created_at) as last_contact')
            )
            ->groupBy('support_chats.user_id', 'support_chats.topic', 'users.username', 'users.phone')
            ->orderBy('last_contact', 'desc')
            ->get();

        return view('admin.support-chats.index', [
            'chats' => $chats,
            'first_level_active_index' => 'support-chats',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 2. หน้าต่างแชท (ดูข้อความของ User คนนี้ ในหัวข้อนี้)
    public function show(Request $request, $user_id)
    {
        $topic = $request->query('topic', 'general'); // รับค่าหัวข้อ
        
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
            'message' => 'required|string'
        ]);

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
}