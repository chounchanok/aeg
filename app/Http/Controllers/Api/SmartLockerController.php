<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
            $isAvailable = $this->isDateAvailable($locker, Carbon::today());
            return [
                'id' => $locker->id,
                'locker_number' => $locker->locker_number,
                'type' => $locker->type,
                'title' => ($lang == 'en' && !empty($locker->title_en)) ? $locker->title_en : $locker->title_th,
                'description' => ($lang == 'en' && !empty($locker->description_en)) ? $locker->description_en : $locker->description_th,
                'price' => $locker->price,
                'image_url' => $locker->image_url,
                'status' => $locker->status === 'maintenance' ? 'maintenance' : ($isAvailable ? 'available' : 'rented'),
                'is_available' => $isAvailable
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

        $isAvailable = $this->isDateAvailable($locker, Carbon::today());
        $data = [
            'id' => $locker->id,
            'locker_number' => $locker->locker_number,
            'type' => $locker->type,
            'title' => ($lang == 'en' && !empty($locker->title_en)) ? $locker->title_en : $locker->title_th,
            'description' => ($lang == 'en' && !empty($locker->description_en)) ? $locker->description_en : $locker->description_th,
            'price' => $locker->price,
            'image_url' => $locker->image_url,
            'status' => $locker->status === 'maintenance' ? 'maintenance' : ($isAvailable ? 'available' : 'rented'),
            'is_available' => $isAvailable
        ];

        return $this->successResponse($data, 'Locker detail retrieved');
    }

    /** Public API for mobile date pickers: GET /smart-lockers/{id}/availability?from=YYYY-MM-DD&to=YYYY-MM-DD */
    public function availability(Request $request, $id)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $locker = DB::table('smart_lockers')->where('id', $id)->where('is_active', true)->first();
        if (!$locker) return $this->errorResponse('ไม่พบข้อมูลตู้เซฟนี้', 404);

        $from = Carbon::parse($request->query('from', today()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', $from->copy()->addDays(89)->toDateString()))->startOfDay();
        if ($from->diffInDays($to) > 365) {
            return $this->errorResponse('เลือกช่วงวันที่ได้ไม่เกิน 366 วัน', 422);
        }

        $bookings = DB::table('locker_bookings')
            ->where('smart_locker_id', $id)
            ->whereIn('status', ['pending_payment', 'paid', 'active', 'completed'])
            ->where('start_date', '<', $to->copy()->addDay()->toDateString())
            ->where('end_date', '>', $from->toDateString())
            ->get(['start_date', 'end_date']);
        $manualBlocks = DB::table('smart_locker_unavailable_dates')
            ->where('smart_locker_id', $id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()->keyBy('date');

        $dates = collect();
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $date = $day->toDateString();
            $reserved = $bookings->contains(fn ($booking) => $booking->start_date <= $date && $booking->end_date > $date);
            $block = $manualBlocks->get($date);
            $unavailable = $locker->status === 'maintenance' || $reserved || $block;
            $dates->push([
                'date' => $date,
                'is_available' => !$unavailable,
                'reason' => $locker->status === 'maintenance' ? 'maintenance' : ($reserved ? 'booked' : ($block ? 'admin_block' : null)),
            ]);
        }

        return $this->successResponse([
            'smart_locker_id' => (int) $locker->id,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'dates' => $dates,
        ], 'Locker availability retrieved');
    }

    private function isDateAvailable(object $locker, Carbon $date): bool
    {
        if ($locker->status === 'maintenance') return false;

        $dateString = $date->toDateString();
        $blocked = DB::table('smart_locker_unavailable_dates')
            ->where('smart_locker_id', $locker->id)->where('date', $dateString)->exists();
        $booked = DB::table('locker_bookings')
            ->where('smart_locker_id', $locker->id)
            ->whereIn('status', ['pending_payment', 'paid', 'active', 'completed'])
            ->where('start_date', '<=', $dateString)->where('end_date', '>', $dateString)->exists();

        return !$blocked && !$booked;
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
            'payment_gateway' => 'required|in:bbl',
            'duration_months' => 'required|integer|min:1|max:60',
            'start_date' => 'required|date|after_or_equal:today',
            'address_id' => 'required|integer',
            'custom_address_text' => 'nullable|string|required_without:address_id'
        ]);

        $user = $request->user();
        
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $durationMonths = (int) $request->duration_months;
        $endDate = $startDate->copy()->addMonths($durationMonths);

        try {
            $bookingId = DB::transaction(function () use ($request, $user, $startDate, $endDate, $durationMonths) {
                $locker = DB::table('smart_lockers')->where('id', $request->smart_locker_id)->lockForUpdate()->first();
                if (!$locker || $locker->status === 'maintenance') {
                    throw new \DomainException('ขออภัย ตู้เซฟนี้ยังไม่พร้อมให้บริการ');
                }

                $start = $startDate->toDateString();
                $end = $endDate->toDateString();
                $hasBooking = DB::table('locker_bookings')
                    ->where('smart_locker_id', $locker->id)
                    ->whereIn('status', ['pending_payment', 'paid', 'active', 'completed'])
                    ->where('start_date', '<', $end)->where('end_date', '>', $start)->exists();
                $hasAdminBlock = DB::table('smart_locker_unavailable_dates')
                    ->where('smart_locker_id', $locker->id)->where('date', '>=', $start)->where('date', '<', $end)->exists();
                if ($hasBooking || $hasAdminBlock) {
                    throw new \DomainException('วันที่เลือกมีการจองหรือถูกปิดไว้แล้ว กรุณาเลือกวันอื่น');
                }

                $serviceFee = $locker->price * $durationMonths;
                $grandTotal = $serviceFee + ($serviceFee * 0.07);

                return DB::table('locker_bookings')->insertGetId([
                    'booking_number' => 'LCK-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                    'user_id' => $user->id,
                    'smart_locker_id' => $locker->id,
                    'total_amount' => $grandTotal,
                    'payment_gateway' => $request->payment_gateway,
                    'start_date' => $start,
                    'end_date' => $end,
                    'address_id' => $request->address_id,
                    'custom_address_text' => $request->custom_address_text ?? null,
                    'status' => 'pending_payment',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $booking = DB::table('locker_bookings')->where('id', $bookingId)->first();
            $paymentUrl = url('/payment/bbl/redirect/' . $booking->booking_number . '/all');

            return $this->successResponse([
                'booking_id' => $bookingId,
                'booking_number' => $booking->booking_number,
                'payment_url' => $paymentUrl
            ], 'สร้างรายการจองสำเร็จ กรุณาชำระเงิน');

        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 409);
        } catch (\Exception $e) {
            return $this->errorResponse('เกิดข้อผิดพลาดในการจองตู้เซฟ', 500);
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

            DB::commit();

            return $this->successResponse(null, 'ยกเลิกการจองตู้เซฟเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาดในการยกเลิก: ' . $e->getMessage(), 500);
        }
    }

    /** Create a payable renewal booking linked to the current locker contract. */
    public function renew(Request $request, $id)
    {
        $request->validate([
            'duration_months' => 'required|integer|min:1|max:60',
            'payment_gateway' => 'required|in:bbl',
        ]);

        $user = $request->user();
        try {
            $renewalId = DB::transaction(function () use ($request, $user, $id) {
                $booking = DB::table('locker_bookings')->where('id', $id)->where('user_id', $user->id)->lockForUpdate()->first();
                if (!$booking) throw new \DomainException('ไม่พบสัญญาเช่าตู้เซฟนี้');
                if (!in_array($booking->status, ['active', 'paid', 'completed'], true) || !$booking->end_date) {
                    throw new \DomainException('สัญญานี้ยังไม่สามารถต่ออายุได้');
                }
                if (DB::table('locker_bookings')->where('renewal_of_booking_id', $booking->id)->where('status', 'pending_payment')->exists()) {
                    throw new \DomainException('มีรายการต่ออายุที่รอชำระเงินอยู่แล้ว');
                }
                $endOfCurrent = Carbon::parse($booking->end_date)->startOfDay();
                if ($endOfCurrent->lt(Carbon::today()) || $endOfCurrent->gt(Carbon::today()->addDays(90))) {
                    throw new \DomainException('ต่ออายุได้เมื่อสัญญาเหลืออายุไม่เกิน 90 วัน');
                }

                $locker = DB::table('smart_lockers')->where('id', $booking->smart_locker_id)->lockForUpdate()->first();
                if (!$locker || $locker->status === 'maintenance') {
                    throw new \DomainException('ตู้เซฟอยู่ระหว่างปิดปรับปรุง ไม่สามารถต่ออายุได้');
                }

                $startDate = $endOfCurrent;
                $endDate = $startDate->copy()->addMonths((int) $request->duration_months);
                $serviceFee = (float) $locker->price * (int) $request->duration_months;
                $totalAmount = $serviceFee + ($serviceFee * 0.07);

                return DB::table('locker_bookings')->insertGetId([
                    'booking_number' => 'LCK-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                    'user_id' => $user->id,
                    'smart_locker_id' => $locker->id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'total_amount' => $totalAmount,
                    'payment_gateway' => $request->payment_gateway,
                    'status' => 'pending_payment',
                    'address_id' => $booking->address_id ?? null,
                    'custom_address_text' => $booking->custom_address_text ?? null,
                    'renewal_of_booking_id' => $booking->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $renewal = DB::table('locker_bookings')->where('id', $renewalId)->first();
            return $this->successResponse([
                'renewal_booking_id' => $renewal->id,
                'booking_number' => $renewal->booking_number,
                'smart_locker_id' => (int) $renewal->smart_locker_id,
                'start_date' => $renewal->start_date,
                'end_date' => $renewal->end_date,
                'duration_months' => (int) $request->duration_months,
                'total_amount' => (float) $renewal->total_amount,
                'payment_url' => url('/payment/bbl/redirect/' . $renewal->booking_number . '/all'),
            ], 'สร้างรายการต่ออายุตู้เซฟสำเร็จ กรุณาชำระเงิน');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('เกิดข้อผิดพลาดในการสร้างรายการต่ออายุตู้เซฟ', 500);
        }
    }
}
