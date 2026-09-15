<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAdminController extends Controller
{
    public function index()
    {
        // ดึงรายการคำสั่งซื้อ พร้อมชื่อลูกค้า
        $orders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->select('orders.*', 'users.username', 'customer_profiles.first_name', 'customer_profiles.last_name')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'first_level_active_index' => 'orders',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function show($id)
    {
        // 1. ดึงข้อมูลออเดอร์และลูกค้า
        $order = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->select('orders.*', 'users.username', 'users.email', 'users.phone', 'customer_profiles.first_name', 'customer_profiles.last_name')
            ->where('orders.id', $id)
            ->first();

        if (!$order) abort(404);

        // 2. ดึงรายการสินค้าในออเดอร์นี้ (Order Items)
        $orderItems = DB::table('order_items')->where('order_id', $id)->get();

        // 3. ดึงข้อมูลที่อยู่จัดส่ง (ถ้ามี)
        $address = null;
        if ($order->address_id) {
            $address = DB::table('customer_addresses')->where('id', $order->address_id)->first();
        }

        return view('admin.orders.show', [
            'order' => $order,
            'orderItems' => $orderItems,
            'address' => $address,
            'first_level_active_index' => 'orders',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_payment,paid,processing,completed,cancelled'
        ]);

        $statusLabels = [
            'pending_payment' => 'รอชำระเงิน',
            'paid' => 'ชำระเงินเรียบร้อยแล้ว',
            'processing' => 'กำลังดำเนินการจัดส่ง',
            'completed' => 'จัดส่งสำเร็จ',
            'cancelled' => 'ยกเลิกคำสั่งซื้อ',
        ];

        // 🌟 ดึง user_id (เจ้าของออเดอร์) ไว้ก่อน เพื่อใช้แจ้งเตือนหลังอัปเดตสถานะสำเร็จ
        $order = DB::table('orders')->where('id', $id)->first();

        if (!$order) {
            return redirect()->back()->with('error', 'ไม่พบคำสั่งซื้อนี้ในระบบ');
        }

        DB::beginTransaction();
        try {
            DB::table('orders')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            // 🌟 บันทึกการแจ้งเตือนในระบบ (แสดงในกระดิ่งแจ้งเตือนหน้าเว็บ) ให้ลูกค้าเจ้าของออเดอร์นี้
            $notifyTitle = 'สถานะคำสั่งซื้อมีการอัปเดต';
            $notifyBody = 'คำสั่งซื้อ #' . $order->order_number . ' ของคุณเปลี่ยนสถานะเป็น: '
                . ($statusLabels[$request->status] ?? $request->status);

            DB::table('notifications')->insert([
                'user_id' => $order->user_id,
                'title' => $notifyTitle,
                'body' => $notifyBody,
                'type' => 'order',
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        // 🌟 ยิง Push Notification (FCM) ไปยังมือถือของลูกค้า — ทำหลัง commit เสร็จแล้ว และห้ามทำให้ request ล้มเหลว
        // แม้ว่าการส่ง push จะผิดพลาด (เช่น ยังไม่ได้ตั้งค่า Firebase credentials) เพราะสถานะออเดอร์อัปเดตสำเร็จไปแล้ว
        try {
            PushNotificationService::sendToUser($order->user_id, $notifyTitle, $notifyBody, [
                'type' => 'order_status',
                'order_id' => (string) $id,
                'order_number' => $order->order_number,
                'status' => $request->status,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[OrderAdminController] ส่ง push แจ้งเตือนล้มเหลว: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'อัปเดตสถานะคำสั่งซื้อเรียบร้อยแล้ว');
    }

    // ==========================================
    // 🌟 อัปโหลดใบเสนอราคา / ใบเสร็จรับเงิน ให้คำสั่งซื้อนี้ (แอดมินสร้าง/แนบไฟล์จากหลังบ้าน)
    // ลูกค้าจะเห็นลิงก์และดาวน์โหลดได้จากแอป ผ่าน GET /ecommerce/orders/{id} และ GET /ecommerce/orders
    // ==========================================
    public function uploadDocuments(Request $request, $id)
    {
        $request->validate([
            'quotation' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480', // สูงสุด 20MB
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        if (!$request->hasFile('quotation') && !$request->hasFile('receipt')) {
            return redirect()->back()->with('error', 'กรุณาเลือกไฟล์ใบเสนอราคาหรือใบเสร็จอย่างน้อย 1 ไฟล์');
        }

        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            return redirect()->back()->with('error', 'ไม่พบคำสั่งซื้อนี้ในระบบ');
        }

        $updateData = ['updated_at' => now()];
        $notifyMessages = [];

        if ($request->hasFile('quotation')) {
            $path = $request->file('quotation')->store('order-documents/quotations', 'public');
            $updateData['quotation_url'] = url('storage/' . $path);
            $notifyMessages[] = 'ใบเสนอราคา';
        }

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('order-documents/receipts', 'public');
            $updateData['receipt_url'] = url('storage/' . $path);
            $notifyMessages[] = 'ใบเสร็จรับเงิน';
        }

        DB::table('orders')->where('id', $id)->update($updateData);

        // 🌟 แจ้งเตือนลูกค้าเจ้าของออเดอร์ว่ามีเอกสารใหม่ให้ดาวน์โหลด (แสดงในกระดิ่งแจ้งเตือนหน้าเว็บ/แอป)
        $docLabel = implode(' และ', $notifyMessages);
        $notifyTitle = "$docLabel พร้อมให้ดาวน์โหลดแล้ว";
        $notifyBody = "$docLabel สำหรับคำสั่งซื้อ #{$order->order_number} พร้อมให้ดาวน์โหลดแล้ว กดดูรายละเอียดออเดอร์เพื่อดาวน์โหลด";

        DB::table('notifications')->insert([
            'user_id' => $order->user_id,
            'title' => $notifyTitle,
            'body' => $notifyBody,
            'type' => 'order',
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        try {
            PushNotificationService::sendToUser($order->user_id, $notifyTitle, $notifyBody, [
                'type' => 'order_document',
                'order_id' => (string) $id,
                'order_number' => $order->order_number,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[OrderAdminController] ส่ง push แจ้งเตือนเอกสารล้มเหลว: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', "อัปโหลด{$docLabel}เรียบร้อยแล้ว");
    }
}