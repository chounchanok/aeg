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

    /**
     * 🌟 ค้นหาตู้ว่างตามช่วงเวลา (ลูกค้าเลือกช่วงวันก่อน แล้วระบบหาตู้ให้)
     * GET /smart-lockers/search-available?from=2026-11-01&duration_months=3[&type=PRIME][&category_id=1]
     *   หรือ ?from=2026-11-01&to=2026-12-31 (to = วันสุดท้ายที่ใช้ตู้ นับรวม)
     *
     * - มีตู้ว่างทั้งช่วง → status = "success" + smart_locker_id (ตู้ที่แนะนำ) + available_lockers ทั้งหมด → ใช้ POST /smart-lockers/book ต่อ
     * - ไม่มีตู้ว่าง     → status = "unavailable" + ข้อความให้เลือกใหม่ + ช่วงที่ว่าง (free_ranges) และวันเริ่มที่ใกล้ที่สุดที่ว่างครบช่วง (suggestions)
     *
     * ช่วงวันใช้หลักเดียวกับการจอง: start_date นับรวม, end_date = วันถัดจากวันสุดท้าย (half-open)
     */
    public function searchAvailable(Request $request)
    {
        $request->validate([
            'from' => 'required|date|after_or_equal:today',
            'to' => 'nullable|date|after_or_equal:from|required_without:duration_months',
            'duration_months' => 'nullable|integer|min:1|max:60|required_without:to',
            'type' => 'nullable|string',
            'category_id' => 'nullable|integer',
        ]);

        $lang = $request->header('Accept-Language', 'th');
        $from = Carbon::parse($request->query('from'))->startOfDay();
        $durationMonths = $request->filled('duration_months') ? (int) $request->query('duration_months') : null;
        $endExclusive = $durationMonths
            ? $from->copy()->addMonths($durationMonths)
            : Carbon::parse($request->query('to'))->startOfDay()->addDay();
        $durationDays = (int) $from->diffInDays($endExclusive);
        if ($durationDays > 1830) {
            return $this->errorResponse('เลือกช่วงเวลาได้ไม่เกิน 60 เดือน', 422);
        }

        $lockers = DB::table('smart_lockers')
            ->where('is_active', true)
            ->where('status', '!=', 'maintenance')
            ->when($request->filled('type'), fn ($q) => $q->where('type', strtoupper($request->query('type'))))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->query('category_id')))
            ->orderBy('type')->orderBy('locker_number')
            ->get();

        // ช่วงที่ต้องโหลดข้อมูลการจอง = ช่วงที่เลือก + 180 วันถัดไป (ใช้หาวันเริ่มใหม่ที่ใกล้ที่สุด)
        $searchDays = 180;
        $totalDays = $searchDays + $durationDays;
        $windowEnd = $from->copy()->addDays($totalDays);
        $lockerIds = $lockers->pluck('id');

        $bookings = DB::table('locker_bookings')
            ->whereIn('smart_locker_id', $lockerIds)
            ->whereIn('status', ['pending_payment', 'paid', 'active', 'completed'])
            ->where('start_date', '<', $windowEnd->toDateString())
            ->where('end_date', '>', $from->toDateString())
            ->get(['smart_locker_id', 'start_date', 'end_date'])
            ->groupBy('smart_locker_id');
        $blocks = DB::table('smart_locker_unavailable_dates')
            ->whereIn('smart_locker_id', $lockerIds)
            ->where('date', '>=', $from->toDateString())
            ->where('date', '<', $windowEnd->toDateString())
            ->get(['smart_locker_id', 'date'])
            ->groupBy('smart_locker_id');

        $present = function ($locker) use ($lang) {
            return [
                'id' => $locker->id,
                'smart_locker_id' => $locker->id,
                'locker_number' => $locker->locker_number,
                'type' => $locker->type,
                'category_id' => $locker->category_id ?? null,
                'title' => ($lang == 'en' && !empty($locker->title_en)) ? $locker->title_en : $locker->title_th,
                'price' => (float) $locker->price,
                'image_url' => $locker->image_url,
            ];
        };

        $available = [];
        $freeRanges = [];
        $suggestions = [];
        foreach ($lockers as $locker) {
            // busy[i] = วันที่ from+i ไม่ว่าง
            $busy = array_fill(0, $totalDays, 0);
            foreach ($bookings->get($locker->id, collect()) as $b) {
                $startIdx = max(0, (int) $from->diffInDays(Carbon::parse($b->start_date)->startOfDay(), false));
                $endIdx = min($totalDays, (int) $from->diffInDays(Carbon::parse($b->end_date)->startOfDay(), false));
                for ($i = $startIdx; $i < $endIdx; $i++) $busy[$i] = 1;
            }
            foreach ($blocks->get($locker->id, collect()) as $blk) {
                $i = (int) $from->diffInDays(Carbon::parse($blk->date)->startOfDay(), false);
                if ($i >= 0 && $i < $totalDays) $busy[$i] = 1;
            }

            // prefix sum เพื่อเช็คว่าช่วง [s, s+durationDays) ว่างทั้งหมดไหมแบบ O(1)
            $prefix = [0];
            foreach ($busy as $i => $v) $prefix[$i + 1] = $prefix[$i] + $v;

            if ($prefix[$durationDays] === 0) {
                $available[] = $present($locker);
                continue;
            }

            // ช่วงที่ว่างภายในช่วงที่ลูกค้าเลือก (แสดงให้ลูกค้าเห็นว่าว่างวันไหนบ้าง)
            $ranges = [];
            $runStart = null;
            for ($i = 0; $i <= $durationDays; $i++) {
                $isFree = $i < $durationDays && $busy[$i] === 0;
                if ($isFree && $runStart === null) $runStart = $i;
                if (!$isFree && $runStart !== null) {
                    $ranges[] = [
                        'from' => $from->copy()->addDays($runStart)->toDateString(),
                        'to' => $from->copy()->addDays($i - 1)->toDateString(),
                        'days' => $i - $runStart,
                    ];
                    $runStart = null;
                }
            }
            if (!empty($ranges)) {
                $freeRanges[] = $present($locker) + ['free_ranges' => $ranges];
            }

            // วันเริ่มที่ใกล้ที่สุด (ภายใน 180 วัน) ที่ตู้นี้ว่างครบตามระยะเวลาเดิม
            for ($sIdx = 1; $sIdx <= $searchDays; $sIdx++) {
                if ($prefix[$sIdx + $durationDays] - $prefix[$sIdx] === 0) {
                    $start = $from->copy()->addDays($sIdx);
                    $end = $durationMonths ? $start->copy()->addMonths($durationMonths) : $start->copy()->addDays($durationDays);
                    $suggestions[] = $present($locker) + [
                        'start_date' => $start->toDateString(),
                        'end_date' => $end->copy()->subDay()->toDateString(),
                    ];
                    break;
                }
            }
        }

        $requested = [
            'from' => $from->toDateString(),
            'to' => $endExclusive->copy()->subDay()->toDateString(),
            'duration_months' => $durationMonths,
            'duration_days' => $durationDays,
        ];

        if (!empty($available)) {
            return $this->successResponse([
                'is_available' => true,
                'smart_locker_id' => $available[0]['id'], // ตู้ที่แนะนำ → ส่งต่อให้ POST /smart-lockers/book
                'requested' => $requested,
                'available_lockers' => $available,
            ], 'มีตู้เซฟว่างในช่วงเวลาที่เลือก');
        }

        usort($suggestions, fn ($a, $b) => strcmp($a['start_date'], $b['start_date']) ?: ($a['id'] <=> $b['id']));

        return response()->json([
            'status' => 'unavailable',
            'message' => 'ช่วงเวลาที่คุณเลือกไม่มี locker ว่าง โปรดเลือกใหม่อีกครั้ง',
            'data' => [
                'is_available' => false,
                'smart_locker_id' => null,
                'requested' => $requested,
                'free_ranges' => $freeRanges, // ตู้ที่ว่างบางช่วงภายในช่วงที่เลือก
                'suggestions' => array_slice($suggestions, 0, 5), // วันเริ่มที่ใกล้ที่สุดที่ว่างครบตามระยะเวลาเดิม
            ],
        ], 200);
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
