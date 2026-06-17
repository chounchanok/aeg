<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceRequestAdminController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลใบแจ้งซ่อม พร้อมข้อมูลลูกค้าและแพ็กเกจ
        $requests = DB::table('service_requests')
            ->join('users', 'service_requests.customer_id', '=', 'users.id')
            ->join('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->select(
                'service_requests.*',
                'users.username',
                'users.phone',
                'customer_products.product_name'
            )
            ->orderBy('service_requests.created_at', 'desc')
            ->get();

        return view('admin.service-requests.index', [
            'requests' => $requests,
            // ส่งค่าเพื่อให้เมนูด้านซ้ายสว่าง (Active) ตรงกับหน้าแจ้งซ่อม
            'first_level_active_index' => 'service-requests',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function show($id)
    {
        // 1. ดึงข้อมูลใบแจ้งซ่อม
        $request = DB::table('service_requests')
            ->join('users', 'service_requests.customer_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->join('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->leftJoin('customer_addresses', 'service_requests.address_id', '=', 'customer_addresses.id')
            ->select(
                'service_requests.*',
                'users.username', 'users.phone', 'customer_profiles.first_name',
                'customer_products.product_name', 'customer_products.serial_number',
                'customer_addresses.address_line', 'customer_addresses.province'
            )
            ->where('service_requests.id', $id)
            ->first();

        if (!$request) abort(404);

        // จัดการเรื่องที่อยู่แสดงผล (ถ้าพิมพ์เอง หรือเลือกจากที่มี)
        $display_address = $request->address_id
            ? "{$request->address_line} จ.{$request->province}"
            : $request->custom_address_text;

        // 2. ดึงรูปภาพประกอบ
        $images = DB::table('service_request_images')->where('service_request_id', $id)->get();

        // 3. ดึงประวัติการแชท (ถ้ามี)
        $chats = DB::table('service_request_chats')
            ->leftJoin('users', 'service_request_chats.user_id', '=', 'users.id')
            ->where('service_request_id', $id)
            ->select('service_request_chats.*', 'users.username')
            ->orderBy('created_at', 'asc')->get();

        // 4. ดึงประวัติสถานะ (Tracking)
        $tracking = DB::table('service_request_tracking')->where('service_request_id', $id)->orderBy('created_at', 'desc')->get();

        // 🌟 ดึงรายชื่อพนักงานที่มี Role เป็น technician
        $technicians = DB::table('users')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->where('users.role', 'technician')
            ->select('users.id', 'customer_profiles.first_name as name')
            ->get();

        return view('admin.service-requests.show', [
            'request' => $request,
            'images' => $images,
            'chats' => $chats,
            'tracking' => $tracking,
            'technicians' => $technicians, // 🌟 ส่งตัวแปรนี้ไปที่ View
            'display_address' => $display_address,
            'first_level_active_index' => 'service-requests',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // ==========================================
    // บันทึกอัปเดตสถานะงานซ่อม และเก็บ Tracking Log
    // ==========================================
    public function updateStatus(Request $request, $id)
    {
        // 1. เพิ่มการ Validate รับค่า วันที่ และ เวลา
        $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
            'technician_id' => 'nullable|integer',
            'preferred_date' => 'nullable|date',   // 🌟 รับค่าวันที่
            'time_slot' => 'nullable|string'       // 🌟 รับค่าเวลา
        ]);

        DB::beginTransaction();
        try {
            // 2. อัปเดตข้อมูลลงฐานข้อมูล (เพิ่ม preferred_date และ time_slot)
            DB::table('service_requests')->where('id', $id)->update([
                'status' => $request->status,
                'technician_id' => $request->technician_id,
                'preferred_date' => $request->preferred_date, // 🌟 บันทึกวันที่
                'time_slot' => $request->time_slot,           // 🌟 บันทึกเวลา
                'updated_at' => now()
            ]);

            $statusLabels = [
                'pending' => 'รอดำเนินการ',
                'assigned' => 'จ่ายงานให้ช่างเรียบร้อยแล้ว',
                'in_progress' => 'ช่างกำลังดำเนินการซ่อม',
                'completed' => 'ซ่อมเสร็จสิ้น',
                'cancelled' => 'ยกเลิกรายการ'
            ];

            // ดึงชื่อช่าง (ถ้ามี)
            $techName = '';
            if ($request->technician_id) {
                $techProfile = DB::table('customer_profiles')->where('user_id', $request->technician_id)->first();
                if ($techProfile) $techName = " (ช่างผู้รับผิดชอบ: " . $techProfile->first_name . ")";
            }

            // จัดฟอร์แมตข้อความแจ้งเตือนวันเวลาที่นัดหมาย
            $dateTimeInfo = '';
            if ($request->preferred_date) {
                $formattedDate = \Carbon\Carbon::parse($request->preferred_date)->format('d/m/Y');
                $dateTimeInfo = " | นัดหมาย: {$formattedDate} {$request->time_slot}";
            }

            // 3. บันทึกประวัติ (Tracking)
            DB::table('service_request_tracking')->insert([
                'service_request_id' => $id,
                'status' => $statusLabels[$request->status],
                // 🌟 แนบข้อมูลวันเวลานัดหมายลงไปใน Log ด้วย ลูกค้าจะได้เห็นผ่านแอป
                'description' => 'แอดมินอัปเดตสถานะงานเป็น: ' . $statusLabels[$request->status] . $techName . $dateTimeInfo,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'อัปเดตข้อมูลและวันเวลานัดหมายสำเร็จ']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // แอดมินส่งข้อความแชทหาลูกค้า
    // ==========================================
    public function sendChat(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);

        try {
            // บันทึกข้อความลงตาราง
            $chatId = DB::table('service_request_chats')->insertGetId([
                'service_request_id' => $id,
                'user_id' => auth()->id(), // ดึง ID แอดมินที่ล็อกอินอยู่
                'sender_type' => 'admin',
                'message' => $request->message,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ดึงข้อมูลที่เพิ่งสร้างเพื่อส่งกลับไปวาดบนหน้าจอ
            $newChat = DB::table('service_request_chats')->where('id', $chatId)->first();

            return response()->json([
                'success' => true,
                'chat' => [
                    'message' => $newChat->message,
                    'time' => \Carbon\Carbon::parse($newChat->created_at)->format('H:i')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'ไม่สามารถส่งข้อความได้'], 500);
        }
    }
}
