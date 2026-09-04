<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Services\FaqBotService;
use App\Services\StaffNotificationService;

class SupportController extends Controller
{
    use ApiResponseTrait;

    // ==========================================
    // 1. FAQ (คำถามที่พบบ่อย)
    // ==========================================
    public function getFaqs()
    {
        // 🌟 ดึงข้อมูลชุดเดียวกับที่ใช้บนหน้าเว็บ /faq (chatbot_topics > chatbot_services > chatbot_service_faqs)
        // เพื่อให้แอปกับเว็บแสดง FAQ ตรงกัน แก้ไขจากหลังบ้าน (/admin/cms/faqs) ที่เดียวมีผลทั้งคู่
        $topics = DB::table('chatbot_topics')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        // ⚠️ join กับ chatbot_services เท่านั้น (ไม่ต้อง join chatbot_topics ตรงนี้ เพราะ
        // f.service_id ชี้ไปที่ chatbot_services.id ไม่ใช่ chatbot_topics.id — topic_id ดึงผ่าน s.topic_id แทน)
        $faqs = DB::table('chatbot_service_faqs as f')
            ->join('chatbot_services as s', 'f.service_id', '=', 's.id')
            ->where('f.is_active', true)
            ->where('s.is_active', true)
            ->orderBy('s.sort_order', 'asc')
            ->orderBy('f.sort_order', 'asc')
            ->select('f.id', 'f.question_th', 'f.answer_th', 's.id as service_id', 's.name_th as service_name', 's.topic_id')
            ->get()
            ->groupBy('topic_id');

        // จัดกลุ่ม FAQ ให้อยู่ใต้ topic ของตัวเอง (เฉพาะ topic ที่มีคำถามจริง) เหมือนฝั่งเว็บทุกประการ
        $topicsWithFaqs = $topics->map(function ($topic) use ($faqs) {
            // ->values() reindex ให้เป็น array เรียง 0,1,2,... เสมอ ไม่งั้น json อาจออกมาเป็น object แทน array
            $topic->faqs = $faqs->get($topic->id, collect())->values();
            return $topic;
        })->filter(function ($topic) {
            return $topic->faqs->isNotEmpty();
        })->values();

        return $this->successResponse($topicsWithFaqs, 'FAQs retrieved');
    }

    // ==========================================
    // 1.1 FAQ Bot (ให้ลูกค้าหาคำตอบได้เองก่อนคุยกับเจ้าหน้าที่)
    // ใช้ได้ทั้งหน้าเว็บ (support-chat) และแอปมือถือ ไม่ต้องล็อกอินก็ถามได้
    // ==========================================
    public function askBot(Request $request, FaqBotService $bot)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $result = $bot->findAnswer($request->message);

        // เก็บ log ไว้ดูว่าลูกค้าถามอะไรบ้าง และบอทตอบได้ไหม (ใช้ปรับปรุง FAQ ทีหลังได้)
        $userId = Auth::guard('sanctum')->check()
            ? Auth::guard('sanctum')->id()
            : (Auth::check() ? Auth::id() : null);

        DB::table('faq_bot_logs')->insert([
            'user_id' => $userId,
            'question' => $request->message,
            'faq_id' => $result['faq_id'],
            'is_matched' => $result['matched'],
            'match_score' => $result['score'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(
            $result,
            $result['matched'] ? 'พบคำตอบ' : 'ยังไม่พบคำตอบที่ตรงกับคำถามนี้'
        );
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
    // 3. ระบบติดตามสถานะ (Tracking Log & Request Details)
    // ==========================================
    public function getTrackingLogs(Request $request, $id)
    {
        // 1. ตรวจสอบและดึงข้อมูลใบแจ้งซ่อมหลัก (Header)
        $serviceRequest = DB::table('service_requests')
            ->leftJoin('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->leftJoin('customer_profiles', 'service_requests.technician_id', '=', 'customer_profiles.user_id') // ดึงข้อมูลช่าง
            ->leftJoin('technician_profiles', 'service_requests.technician_id', '=', 'technician_profiles.user_id') // ดึงข้อมูลช่าง
            ->where('service_requests.id', $id)
            ->where('service_requests.customer_id', $request->user()->id)
            ->select(
                'service_requests.*',
                'customer_products.product_name as package_name', // ชื่อแพ็กเกจ
                'customer_products.total_service_count',
                'customer_products.used_service_count',
                'customer_profiles.first_name as technician_name',            // ชื่อช่าง
                'customer_profiles.phone as technician_phone',          // เบอร์ช่าง
                'technician_profiles.current_lat as technician_lat',         // พิกัดช่าง (ถ้ามี)
                'technician_profiles.current_long as technician_lng'
            )->first();
        // $serviceRawSql = $serviceRequest->toRawSql();
        // dd($serviceRawSql);
        // $serviceRequest = $serviceRequest->first();

        if (!$serviceRequest) {
            return $this->errorResponse('ไม่พบข้อมูลการแจ้งซ่อม', 404);
        }

        // 2. ดึงข้อมูล Tracking Logs (ไทม์ไลน์ด้านบน)
        $logs = DB::table('service_request_tracking')
            ->where('service_request_id', $id)
            ->orderBy('created_at', 'asc') // เรียงจากเก่าไปใหม่ เพื่อให้แอปวาดไทม์ไลน์ซ้ายไปขวาได้ง่าย
            ->get();

        // 3. จัดเตรียมข้อมูลสำหรับส่งกลับ
        // คำนวณจำนวนบริการคงเหลือ
        $total = $serviceRequest->total_service_count ?? 0;
        $used = $serviceRequest->used_service_count ?? 0;
        $remaining = max(0, $total - $used);

        // ดึงข้อมูล User ปัจจุบัน (สำหรับแสดงตรง "คุณ ... ฟันแจ้ง")
        $userProfile = DB::table('customer_profiles')->where('user_id', $request->user()->id)->first();
        $userAddress = DB::Table('customer_addresses')->where('id', $serviceRequest->address_id)->first();
        $customerFullName = $userProfile ? ($userProfile->first_name . ' ' . $userProfile->last_name) : $request->user()->name;
        $data = [
            'request_info' => [
                'id' => $serviceRequest->id,
                'package_name' => $serviceRequest->package_name ?? 'บริการทั่วไป',
                'ticket_number' => $serviceRequest->ticket_number,
                'problem_description' => $serviceRequest->problem_description, // รายละเอียดปัญหา
                'customer_name' => $customerFullName,
                'customer_phone' => $request->user()->phone ?? ($userProfile->phone ?? '-'),
                'customer_latitude' => $userAddress->latitude, // ที่อยู่ลูกค้า
                'customer_longitude' => $userAddress->longitude, // ที่อยู่ลูกค้า

                'request_date' => \Carbon\Carbon::parse($serviceRequest->created_at)->format('Y-m-d H:i:s'),
                'preferred_date' => $serviceRequest->preferred_date, // วันที่นัดหมาย

                'status' => $serviceRequest->status, // สถานะปัจจุบันของงาน
                'remaining_services' => $remaining,  // บริการคงเหลือ
                'problem_description' => $serviceRequest->problem_description,
                'solutions' => $serviceRequest->solutions,

                // ข้อมูลช่าง (แสดงในปุ่มโทร)
                'technician' => $serviceRequest->technician_id ? [
                    'name' => $serviceRequest->technician_name,
                    'phone' => $serviceRequest->technician_phone,
                    'lat' => $serviceRequest->technician_lat,
                    'lng' => $serviceRequest->technician_lng,
                ] : null
            ],
            // ไทม์ไลน์สถานะ (วงกลมเขียว/เทา)
            'tracking_logs' => $logs
        ];

        return $this->successResponse($data, 'Tracking details retrieved successfully');
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
    // 4. ติดต่อแอดมิน / ฝ่ายขาย (Contact Admin Form)
    // ปรับปรุงตามเมล QA ข้อ 6: auto-fill จากข้อมูลสมาชิก, แยกที่อยู่เป็นฟิลด์ย่อย,
    // แนบสินค้า/จำนวนที่สนใจ, แนบรูปสถานที่ติดตั้ง, ออกหมายเลขคำขอให้ติดตามสถานะได้
    // ==========================================
    public function submitContactAdmin(Request $request)
    {
        // 🌟 ฟิลด์ที่ auto-fill ได้จากข้อมูลสมาชิกทำให้เป็น nullable ตรงนี้ก่อน แล้วไปบังคับ
        // (ว่าต้องไม่ว่างเปล่าหลัง fallback) อีกทีด้านล่าง — เพื่อลดการกรอกซ้ำตามที่ QA ขอ
        $request->validate([
            'topic' => 'required|string',
            'user_type' => 'required|in:business,personal',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address_full' => 'nullable|string', // เผื่อแอปเวอร์ชันเก่ายังส่งที่อยู่แบบรวมช่องเดียวมา
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'subdistrict' => 'nullable|string|max:100',
            'zipcode' => 'nullable|string|max:10',
            'company_name' => 'nullable|string', // จำเป็นถ้าเป็น business
            'preferred_contact_time' => 'nullable|string',
            'product_id' => 'nullable|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'detail' => 'nullable|string',
            'image' => 'nullable|file|image|max:10240', // สูงสุด 10MB
        ]);

        $user = auth('sanctum')->user();

        // 🌟 Auto-fill จากข้อมูลสมาชิก — ใช้ค่าที่แอปส่งมาก่อนเสมอ ถ้าไม่ส่งมาค่อย fallback ไปดึงจากโปรไฟล์
        $firstName = $request->first_name;
        $lastName = $request->last_name;
        $email = $request->email;
        $phone = $request->phone;
        $province = $request->province;
        $district = $request->district;
        $subdistrict = $request->subdistrict;
        $zipcode = $request->zipcode;

        if ($user) {
            $profile = DB::table('customer_profiles')->where('user_id', $user->id)->first();
            $firstName = $firstName ?: ($profile->first_name ?? null);
            $lastName = $lastName ?: ($profile->last_name ?? null);
            $email = $email ?: $user->email;
            $phone = $phone ?: $user->phone;

            // ถ้ายังไม่ได้ระบุที่อยู่มาเองเลย ลองดึงที่อยู่ default ของสมาชิกมาเติมให้
            if (empty($province) && empty($request->address_full)) {
                $defaultAddress = DB::table('customer_addresses')
                    ->where('user_id', $user->id)
                    ->where('is_default', true)
                    ->first();

                if ($defaultAddress) {
                    $province = $defaultAddress->province;
                    $district = $defaultAddress->district;
                    $subdistrict = $defaultAddress->subdistrict;
                    $zipcode = $defaultAddress->zipcode;
                }
            }
        }

        // หลัง fallback แล้ว ข้อมูลที่จำเป็นต่อการติดต่อกลับต้องไม่ว่างเปล่า
        if (empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
            return $this->errorResponse('กรุณากรอกชื่อ นามสกุล อีเมล และเบอร์โทรศัพท์ให้ครบถ้วน', 422);
        }

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('contact-admin', 'public');
            $imageUrl = '/storage/' . $path;
        }

        // 🌟 ออกหมายเลขคำขอ เช่น CONTACT-20260904-0001 ให้ลูกค้าติดตามสถานะได้ (เมลข้อ 6)
        $todayPrefix = 'CONTACT-' . now()->format('Ymd');
        $countToday = DB::table('contact_admin_requests')->where('request_number', 'like', $todayPrefix . '%')->count();
        $requestNumber = $todayPrefix . '-' . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);

        $id = DB::table('contact_admin_requests')->insertGetId([
            // ถ้า User ล็อกอินอยู่ จะเก็บ ID ให้ด้วย ถ้าไม่ล็อกอินก็เป็น null
            'user_id' => $user?->id,
            'request_number' => $requestNumber,
            'topic' => $request->topic,
            'user_type' => $request->user_type,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address_full' => $request->address_full,
            'province' => $province,
            'district' => $district,
            'subdistrict' => $subdistrict,
            'zipcode' => $zipcode,
            'email' => $email,
            'phone' => $phone,
            'company_name' => $request->user_type === 'business' ? $request->company_name : null,
            'preferred_contact_time' => $request->preferred_contact_time,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'detail' => $request->detail,
            'image_url' => $imageUrl,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 🌟 แจ้งเตือนแผนก Sales Admin โดยอัตโนมัติ (เมล QA ข้อ 5)
        StaffNotificationService::notifyRole(
            'sales_admin',
            'มีคำขอติดต่อฝ่ายขายใหม่',
            "เลขที่ {$requestNumber} จาก {$firstName} {$lastName} (หัวข้อ: {$request->topic})",
            '/admin/contact-requests/' . $id,
            'contact_admin'
        );

        return $this->successResponse([
            'id' => $id,
            'request_number' => $requestNumber,
            'status' => 'pending',
        ], 'ส่งข้อมูลติดต่อฝ่ายขายเรียบร้อยแล้ว ทีมงานจะติดต่อกลับโดยเร็วที่สุด');
    }

    /**
     * ประวัติคำขอติดต่อฝ่ายขายของสมาชิกคนนี้ ใช้สำหรับติดตามสถานะ (เมลข้อ 6)
     */
    public function getMyContactRequests(Request $request)
    {
        $userId = $request->user()->id;

        $requests = DB::table('contact_admin_requests')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($requests, 'Contact requests retrieved successfully');
    }
}
