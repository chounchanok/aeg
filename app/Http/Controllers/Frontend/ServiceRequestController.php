<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem; // อิงตามที่หน้า myPackages ใช้

class ServiceRequestController extends Controller
{
    // 1. ดึงข้อมูลมาโชว์ในหน้าฟอร์มแจ้งซ่อม
    public function requestForm($id)
    {
        $user = Auth::user();
        $item = OrderItem::with('product')->findOrFail($id);

        // ดึงที่อยู่ของลูกค้า (ถ้ามีตารางที่อยู่ก็ดึงมาได้เลยครับ อันนี้ผมจำลองดึงจาก Profile)
        $profile = DB::table('customer_profiles')->where('user_id', $user->id)->first();

        return view('frontend.repair-request', compact('item', 'user', 'profile'));
    }

    // 2. รับข้อมูลจากฟอร์มเพื่อบันทึก
    public function submitRequest(Request $request, $id)
    {
        $request->validate([
            'problem_description' => 'required|string',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|string',
        ]);

        $user = Auth::user();
        $item = OrderItem::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. สร้างเลขที่ใบแจ้งซ่อม (Ticket Number)
            $ticketNumber = 'SR-' . date('ymd') . '-' . rand(1000, 9999);

            // 2. บันทึกข้อมูลลงใบแจ้งซ่อม
            $requestId = DB::table('service_requests')->insertGetId([
                'ticket_number' => $ticketNumber,
                'service_type' => 'repair',
                'customer_id' => $user->id,
                'customer_product_id' => $item->id, // ผูกกับ ID ของแพ็กเกจ
                'problem_description' => $request->problem_description . ' สะดวกรับบริการ: ' . $request->preferred_time,
                'preferred_date' => $request->preferred_date,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 3. 🌟 จำลองการนัดเวลาเป็น "ข้อความแชทแรก" ตามที่ต้องการ!
            $chatMessage = "แจ้งซ่อมอุปกรณ์\nรายละเอียดปัญหา: " . $request->problem_description . "\nวันที่สะดวกให้เข้าซ่อม: " . $request->preferred_date . " (" . $request->preferred_time . ")";

            DB::table('service_request_chats')->insert([
                'service_request_id' => $requestId,
                'user_id' => $user->id,
                'sender_type' => 'customer',
                'message' => $chatMessage,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 4. บันทึกประวัติสถานะ (Tracking)
            DB::table('service_request_tracking')->insert([
                'service_request_id' => $requestId,
                'status' => 'รอดำเนินการ',
                'description' => 'ลูกค้าส่งคำขอแจ้งซ่อมผ่านระบบเรียบร้อยแล้ว',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            // ส่งกลับไปหน้า "สถานะแจ้งซ่อม" พร้อมข้อความสำเร็จ
            return redirect()->route('repair-status', $requestId)->with('success', 'ส่งคำขอแจ้งซ่อมสำเร็จ ทีมงานจะติดต่อกลับโดยเร็วที่สุด');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    // ดูสถานะใบแจ้งซ่อมรายใบ
    public function status($id)
    {
        $user = Auth::user();

        $request = DB::table('service_requests')
            ->join('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->select('service_requests.*', 'customer_products.product_name', 'customer_products.image_url', 'customer_products.total_service_count', 'customer_products.used_service_count')
            ->where('service_requests.id', $id)
            ->where('service_requests.customer_id', $user->id)
            ->first();

        if (!$request) abort(404);

        $profile = DB::table('customer_profiles')->where('user_id', $user->id)->first();

        return view('frontend.repair-status', compact('request', 'user', 'profile'));
    }

    // ดูประวัติใบแจ้งซ่อมทั้งหมดของ User นี้
    public function history()
    {
        $user = Auth::user();

        $requests = DB::table('service_requests')
            ->join('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->select('service_requests.*', 'customer_products.product_name', 'customer_products.image_url')
            ->where('service_requests.customer_id', $user->id)
            ->orderBy('service_requests.created_at', 'desc')
            ->get();

        return view('frontend.repair-history', compact('requests'));
    }
}
