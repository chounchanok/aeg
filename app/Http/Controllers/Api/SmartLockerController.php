<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;

class SmartLockerController extends Controller
{
    use ApiResponseTrait;

    // 1. ดึงรายการตู้เซฟ (แยกระหว่าง PRIME และ PRIVILEGE ได้)
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'th');
        $query = DB::table('smart_lockers')->where('is_active', true);

        // รองรับการ Filter ตามประเภทตู้ (PRIME / PRIVILEGE)
        if ($request->has('type')) {
            $query->where('type', strtoupper($request->type));
        }

        $lockers = $query->orderBy('type')->orderBy('locker_number')->get()->map(function ($locker) use ($lang) {
            return [
                'id' => $locker->id,
                'locker_number' => $locker->locker_number,
                'type' => $locker->type,
                'title' => ($lang == 'en' && !empty($locker->title_en)) ? $locker->title_en : $locker->title_th,
                'description' => ($lang == 'en' && !empty($locker->description_en)) ? $locker->description_en : $locker->description_th,
                'price' => $locker->price,
                'image_url' => $locker->image_url,
                'status' => $locker->status, // available, rented, maintenance
                'is_available' => $locker->status === 'available' // boolean สำหรับให้แอปโชว์ปุ่มสีเขียวง่ายๆ
            ];
        });

        return $this->successResponse($lockers, 'Smart Lockers retrieved successfully');
    }

    public function getSmartLockers(Request $request)
    {
        $lang = $request->header('Accept-Language', 'th');
        
        // ดึงล็อกเกอร์ พร้อมข้อมูลหมวดหมู่
        $lockers = \App\Models\SmartLockerCategory::all();

        $data = $lockers->map(function ($locker) use ($lang) {
            return [
                'id' => $locker->id ?? null,
                'slug' => $locker->slug ?? null,
                'name' => ($lang == 'en' && !empty($locker->title_en)) 
                            ? $locker->title_en 
                            : ($locker->title_th ?? 'Uncategorized'),
                'image_url' => $locker->image_url ?? null,
            ];
        });

        return $this->successResponse($data, 'ดึงข้อมูลสำเร็จ');
    }

    // 2. ดึงรายละเอียดตู้เซฟแบบเจาะจง
    public function show($id)
    {
        $lang = request()->header('Accept-Language', 'th');
        $locker = DB::table('smart_lockers')->where('id', $id)->where('is_active', true)->first();

        if (!$locker) return $this->errorResponse('ไม่พบข้อมูลตู้เซฟนี้', 404);

        $data = [
            'id' => $locker->id,
            'locker_number' => $locker->locker_number,
            'type' => $locker->type,
            'title' => ($lang == 'en' && !empty($locker->title_en)) ? $locker->title_en : $locker->title_th,
            'description' => ($lang == 'en' && !empty($locker->description_en)) ? $locker->description_en : $locker->description_th,
            'price' => $locker->price,
            'image_url' => $locker->image_url,
            'status' => $locker->status,
            'is_available' => $locker->status === 'available'
        ];

        return $this->successResponse($data, 'Locker detail retrieved');
    }

    // ==========================================
    // 1. API คำนวณราคา (ก่อนกดจอง)
    // ==========================================
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'smart_locker_id' => 'required|integer',
            'duration_months' => 'required|integer|min:1'
        ]);

        $locker = DB::table('smart_lockers')->where('id', $request->smart_locker_id)->first();
        
        if (!$locker) {
            return $this->errorResponse('ไม่พบข้อมูลตู้เซฟ', 404);
        }

        // คำนวณราคา
        $serviceFee = $locker->price * $request->duration_months;
        $deposit = 0; // ถ้ามีค่ามัดจำสามารถดึงจาก $locker->deposit_amount ได้
        
        $subtotal = $serviceFee + $deposit;
        $vatAmount = $serviceFee * 0.07;
        $grandTotal = $subtotal + $vatAmount;

        return $this->successResponse([
            'smart_locker_id' => $locker->id,
            'duration_months' => $request->duration_months,
            'summary' => [
                'service_fee' => (float) $serviceFee,
                'deposit' => (float) $deposit,
                'subtotal' => (float) $subtotal,
                'vat_amount' => (float) $vatAmount,
                'grand_total' => (float) $grandTotal
            ]
        ], 'คำนวณราคาสำเร็จ');
    }

    // ==========================================
    // 2. API จองตู้เซฟ (เปลี่ยนสถานะตู้เป็น Pending)
    // ==========================================
    public function book(Request $request)
    {
        $request->validate([
            'smart_locker_id' => 'required|integer',
            'payment_gateway' => 'required|string',
            'duration_months' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'address_id' => 'required|integer',
            'custom_address_text' => 'nullable|string|required_without:address_id'
        ]);

        $user = $request->user();
        
        $locker = DB::table('smart_lockers')->where('id', $request->smart_locker_id)->first();
        
        // 🌟 ดักสถานะตู้ ต้องเป็น available เท่านั้นถึงจะจองได้
        if (!$locker || $locker->status !== 'available') {
            return $this->errorResponse('ขออภัย ตู้เซฟนี้ถูกจองหรือยังไม่พร้อมให้บริการ', 400);
        }

        $serviceFee = $locker->price * $request->duration_months;
        $deposit = 0; 
        $subtotal = $serviceFee + $deposit;
        $vatAmount = $serviceFee * 0.07;
        $grandTotal = $subtotal + $vatAmount;

        DB::beginTransaction();
        try {
            $bookingId = DB::table('locker_bookings')->insertGetId([
                'booking_number' => 'LCK-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(5)),
                'user_id' => $user->id,
                'smart_locker_id' => $locker->id,
                'total_amount' => $grandTotal,
                'payment_gateway' => $request->payment_gateway,
                'start_date' => $request->start_date ?? now(),
                'end_date' => $request->start_date ? now()->addMonths($request->duration_months) : now()->addMonths($request->duration_months),
                'address_id' => $request->address_id,
                'custom_address_text' => $request->custom_address_text ?? null,
                'status' => 'pending_payment',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 🌟 ล็อกสถานะตู้ไว้ชั่วคราวเป็น pending (รอชำระเงิน)
            DB::table('smart_lockers')->where('id', $locker->id)->update([
                'status' => 'pending_payment', 
                'updated_at' => now()
            ]);

            DB::commit();

            $booking = DB::table('locker_bookings')->where('id', $bookingId)->first();
            $paymentUrl = null;

            if ($request->payment_gateway === 'bbl') {
                // 🌟 คืนค่าเป็นลิงก์ WebView ของระบบเรา ให้น้องโอมเอาไปเปิด
                $paymentUrl = url('/payment/bbl/redirect/' . $booking->booking_number);
            } else {
                $paymentUrl = "https://placeholder-gateway.com/pay/" . $booking->booking_number;
            }

            return $this->successResponse([
                'booking_id' => $bookingId,
                'booking_number' => $booking->booking_number,
                'payment_url' => $paymentUrl
            ], 'สร้างรายการจองสำเร็จ กรุณาชำระเงิน');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาดในการจอง: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 3. API ยกเลิกการจอง (คืนตู้ให้กลับเป็น Available)
    // ==========================================
    public function cancelBooking(Request $request, $id)
    {
        $user = $request->user();
        
        $booking = DB::table('locker_bookings')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return $this->errorResponse('ไม่พบข้อมูลการจองนี้', 404);
        }

        // ยกเลิกได้เฉพาะรายการที่ยังไม่ได้จ่ายเงิน
        if ($booking->status !== 'pending_payment') {
            return $this->errorResponse('ไม่สามารถยกเลิกรายการนี้ได้ เนื่องจากชำระเงินแล้วหรือถูกยกเลิกไปแล้ว', 400);
        }

        DB::beginTransaction();
        try {
            // 1. เปลี่ยนสถานะบิลเป็น ยกเลิก (cancelled)
            DB::table('locker_bookings')->where('id', $id)->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);

            // 2. 🌟 ปลดล็อกตู้ ให้กลับมาว่างเหมือนเดิม
            DB::table('smart_lockers')->where('id', $booking->smart_locker_id)->update([
                'status' => 'available',
                'updated_at' => now()
            ]);

            DB::commit();

            return $this->successResponse(null, 'ยกเลิกการจองและคืนสถานะตู้เซฟเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาดในการยกเลิก: ' . $e->getMessage(), 500);
        }
    }
}