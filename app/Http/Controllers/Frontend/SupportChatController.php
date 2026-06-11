<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\SupportChatSent;

class SupportChatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $topic = $request->query('topic', 'general'); // ค่าเริ่มต้นเป็น general

        // ดึงข้อความแชทเดิม
        $messages = DB::table('support_chats')
            ->where('user_id', $user->id)
            ->where('topic', $topic)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('frontend.support-chat', compact('messages', 'topic', 'user'));
    }

    public function getHistory(Request $request)
    {
        $topic = $request->query('topic', 'general');
        
        $messages = DB::table('support_chats')
            ->where('user_id', Auth::id())
            ->where('topic', $topic)
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json(['status' => 'success', 'data' => $messages]);
    }
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'message' => 'required|string'
        ]);

        $user = Auth::user();

        // 1. บันทึกลง DB
        $messageId = DB::table('support_chats')->insertGetId([
            'user_id' => $user->id,
            'topic' => $request->topic,
            'message' => $request->message,
            'sender_type' => 'customer',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. ดึงข้อมูลที่เพิ่งสร้างเพื่อเอาไป Broadcast
        $newMessage = DB::table('support_chats')->where('id', $messageId)->first();

        // 3. ส่ง Event ไปที่ Pusher
        broadcast(new SupportChatSent($newMessage));

        return response()->json(['status' => 'success', 'data' => $newMessage]);
    }
}