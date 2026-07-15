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

    // 3. API จองตู้เซฟ
    public function book(Request $request)
    {
        $request->validate([
            'smart_locker_id' => 'required|integer',
            'payment_gateway' => 'required|string',
            'duration_months' => 'required|integer|min:1' // ระยะเวลาเช่า (เดือน)
        ]);

        $user = $request->user();
        
        // เช็คว่าตู้ยังว่างอยู่ไหม
        $locker = DB::table('smart_lockers')->where('id', $request->smart_locker_id)->first();
        if (!$locker || $locker->status !== 'available') {
            return $this->errorResponse('ขออภัย ตู้เซฟนี้ไม่พร้อมให้บริการ หรือถูกจองไปแล้ว', 400);
        }

        $totalAmount = $locker->price * $request->duration_months;

        DB::beginTransaction();
        try {
            // สร้าง Booking
            $bookingId = DB::table('locker_bookings')->insertGetId([
                'booking_number' => 'LCK-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                'user_id' => $user->id,
                'smart_locker_id' => $locker->id,
                'total_amount' => $totalAmount,
                'payment_gateway' => $request->payment_gateway,
                'status' => 'pending_payment',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ล็อกสถานะตู้ไว้ชั่วคราว (กันคนอื่นมากดจองซ้ำระหว่างรอจ่ายเงิน)
            DB::table('smart_lockers')->where('id', $locker->id)->update([
                'status' => 'rented',
                'updated_at' => now()
            ]);

            DB::commit();

            $booking = DB::table('locker_bookings')->where('id', $bookingId)->first();
            $paymentUrl = "https://placeholder-gateway.com/pay/" . $booking->booking_number;

            return $this->successResponse([
                'booking_id' => $bookingId,
                'booking_number' => $booking->booking_number,
                'total_amount' => $totalAmount,
                'payment_url' => $paymentUrl
            ], 'จองตู้เซฟสำเร็จ กรุณาชำระเงิน');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาดในการจอง: ' . $e->getMessage(), 500);
        }
    }
}