<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class SupportController extends Controller
{
    use ApiResponseTrait;

    // ==========================================
    // 1. FAQ (คำถามที่พบบ่อย)
    // ==========================================
    public function getFaqs()
    {
        $faqs = DB::table('faqs')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return $this->successResponse($faqs, 'FAQs retrieved');
    }

    // ==========================================
    // 2. ระบบ Chat (ผูกกับใบแจ้งซ่อม)
    // ==========================================
    public function getChats(Request $request, $id)
    {
        // ตรวจสอบก่อนว่าใบแจ้งซ่อมนี้เป็นของลูกค้าคนนี้จริง
        $serviceRequest = DB::table('service_requests')
            ->where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (!$serviceRequest) return $this->errorResponse('ไม่พบข้อมูลการแจ้งซ่อม', 404);

        $chats = DB::table('service_request_chats')
            ->where('service_request_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->successResponse($chats, 'Chat history retrieved');
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);
        $user = $request->user();

        $serviceRequest = DB::table('service_requests')
            ->where('id', $id)
            ->where('customer_id', $user->id)
            ->first();

        if (!$serviceRequest) return $this->errorResponse('ไม่พบข้อมูลการแจ้งซ่อม', 404);

        $chatId = DB::table('service_request_chats')->insertGetId([
            'service_request_id' => $id,
            'user_id' => $user->id,
            'sender_type' => 'customer', // ผ่าน API ฝั่ง Mobile จะเป็น customer เสมอ
            'message' => $request->message,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $newMessage = DB::table('service_request_chats')->where('id', $chatId)->first();

        return $this->successResponse($newMessage, 'Message sent');
    }

    // ==========================================
    // 3. ระบบติดตามสถานะ (Tracking Log)
    // ==========================================
    public function getTrackingLogs(Request $request, $id)
    {
        $serviceRequest = DB::table('service_requests')
            ->where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (!$serviceRequest) return $this->errorResponse('ไม่พบข้อมูลการแจ้งซ่อม', 404);

        $logs = DB::table('service_request_tracking')
            ->where('service_request_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($logs, 'Tracking logs retrieved');
    }

    public function sendContactEmail(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'preferred_time' => 'required|string',
            'message' => 'nullable|string'
        ]);

        // บันทึกลง Database ให้แอดมินดูย้อนหลังได้
        DB::table('contact_inquiries')->insert([
            'topic' => $request->topic,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'preferred_time' => $request->preferred_time,
            'message' => $request->message,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 💡 ถ้าอนาคตจะเชื่อม Email ให้เขียน \Illuminate\Support\Facades\Mail::to(...)->send(...) ตรงนี้ครับ

        return response()->json([
            'status' => 'success',
            'message' => 'ส่งข้อความติดต่อแอดมินสำเร็จ ทีมงานจะติดต่อกลับโดยเร็วที่สุด'
        ]);
    }

    // ==========================================
    // 4. ติดต่อแอดมิน (Contact Admin Form อัปเดตใหม่ตาม UI)
    // ==========================================
    public function submitContactAdmin(Request $request)
    {
        // 1. ตรวจสอบข้อมูลให้ตรงกับ UI ใหม่
        $request->validate([
            'user_type' => 'required|in:business,personal', // เลือก ธุรกิจ หรือ ส่วนตัว
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address_full' => 'required|string', // จังหวัด, เขต, แขวง, รหัสไปรษณีย์
            'email' => 'required|email',
            'phone' => 'required|string',
            'company_name' => 'nullable|string', // จำเป็นถ้าเป็น business
            'preferred_contact_time' => 'nullable|string'
        ]);

        // 2. บันทึกลง Database (ใช้ตารางใหม่ contact_admin_requests)
        DB::table('contact_admin_requests')->insert([
            // ถ้า User ล็อกอินอยู่ จะเก็บ ID ให้ด้วย ถ้าไม่ล็อกอินก็เป็น null
            'user_id' => auth('sanctum')->check() ? auth('sanctum')->id() : null,
            'user_type' => $request->user_type,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address_full' => $request->address_full,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->user_type === 'business' ? $request->company_name : null,
            'preferred_contact_time' => $request->preferred_contact_time,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $this->successResponse(null, 'Contact form submitted successfully');
    }
}
