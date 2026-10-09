<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Banner;
use App\Models\ServiceCategory;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. ดึงข้อมูล Banners (กรองตามที่ API เดิมเขียนไว้ คือ location = main และ is_active = true)
        $banners = Banner::where('location', 'main')
                         ->where('is_active', true)
                         ->orderBy('sort_order', 'asc')
                         ->get();

        // 2. ดึงข้อมูล หมวดหมู่บริการ
        $categories = ServiceCategory::all();

        // 🌟 2. ดึงข้อมูล "บริการที่ใกล้หมดอายุ" เฉพาะกรณีที่มีการ Login เท่านั้น
        $expiringServices = collect(); // ตั้งค่าเริ่มต้นเป็น Array ว่างเผื่อไม่ได้ล็อกอิน

        if (Auth::check()) {
            $userId = Auth::id();

            // ดึงบริการของลูกค้าคนนี้ที่กำลังจะหมดอายุในอีกไม่เกิน 90 วัน (และยังไม่หมดอายุ)
            $expiringServices = DB::table('customer_products')
                ->where('customer_id', $userId)
                ->where('status', 'active')
                ->whereNotNull('warranty_expire_date')
                ->where('warranty_expire_date', '>=', Carbon::now()) // ยังไม่หมดอายุ
                // ->where('warranty_expire_date', '<=', Carbon::now()->addDays(90)) // (เปิดคอมเมนต์บรรทัดนี้ได้ ถ้าอยากให้โชว์เฉพาะตัวที่จะหมดใน 90 วัน)
                ->orderBy('warranty_expire_date', 'asc') // เอาตัวที่ใกล้หมดอายุสุดขึ้นก่อน
                ->get();

            $expiringLockers = DB::table('locker_bookings as b')
                ->join('smart_lockers as l', 'b.smart_locker_id', '=', 'l.id')
                ->where('b.user_id', $userId)
                ->whereNull('b.renewal_of_booking_id')
                ->whereIn('b.status', ['active', 'paid', 'completed'])
                ->whereBetween('b.end_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(90)->toDateString()])
                ->orderBy('b.end_date')
                ->get([
                    'b.id', 'b.smart_locker_id', 'b.start_date', 'b.end_date', 'b.status',
                    'l.locker_number', 'l.title_th', 'l.image_url',
                ])
                ->map(function ($booking) {
                    return (object) [
                        'id' => $booking->id,
                        'product_name' => 'ตู้เซฟนิรภัย ' . $booking->locker_number . ' · ' . $booking->title_th,
                        'serial_number' => $booking->locker_number,
                        'created_at' => $booking->start_date,
                        'warranty_expire_date' => $booking->end_date,
                        'image_url' => $booking->image_url,
                        'reference_type' => 'locker',
                        'total_service_count' => 0,
                        'used_service_count' => 0,
                        'locker_booking_id' => $booking->id,
                        'smart_locker_id' => $booking->smart_locker_id,
                    ];
                });

            $expiringServices = $expiringServices->concat($expiringLockers)
                ->sortBy('warranty_expire_date')->take(4)->values();
        }

        // 3. ดึงข้อมูล สิทธิพิเศษแนะนำ (Rewards) สุ่มมา 3 รายการเพื่อให้พอดีกับ Layout หน้าเว็บ
        $recommendedPrivileges = DB::table('rewards')
                                   ->where('is_active', true) // สมมติว่ามีฟิลด์นี้ ถ้าไม่มีให้ลบออกได้ครับ
                                   ->inRandomOrder()
                                   ->limit(3)
                                   ->get();

        // 4. ดึงข้อมูล บริการแนะนำ / สินค้าแนะนำ
        // หมายเหตุ: ใน API เดิมไปดึงจากตาราง rewards ซึ่งอาจจะผิด ผมเลยปรับให้ดึงจาก products แทนครับ
        $recommendedServices = DB::table('products')
                                 ->inRandomOrder()
                                 ->limit(4)
                                 ->get();

        // 5. ดึงข้อมูล Popup Ad ที่จะแสดง — สลับไปทีละรูปตามลำดับ sort_order ทุกครั้งที่รีเฟรชหน้า
        // และจำกัดไม่ให้รูปเดิมขึ้นซ้ำเกิน 1 ครั้งต่อวันต่อผู้ชม (เก็บสถานะไว้ใน Cookie ของเบราว์เซอร์)
        $popupAd = null;
        $today = Carbon::now()->format('Y-m-d');
        $shownIds = [];

        if ($request->cookie('popup_ad_shown_date') === $today) {
            $shownIds = array_filter(
                array_map('intval', explode(',', (string) $request->cookie('popup_ad_shown_ids')))
            );
        }

        $activeAds = DB::table('popup_ads')
                        ->where('is_active', true)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

        foreach ($activeAds as $ad) {
            if (!in_array($ad->id, $shownIds, true)) {
                $popupAd = $ad;
                break;
            }
        }

        // ถ้ามีรูปที่จะแสดง ให้บันทึกลง Cookie ว่าแสดงไปแล้ว (เก็บไว้ 3 วัน กันข้ามเที่ยงคืนพอดี)
        if ($popupAd) {
            $shownIds[] = $popupAd->id;
            Cookie::queue('popup_ad_shown_ids', implode(',', $shownIds), 60 * 12 * 3);
            Cookie::queue('popup_ad_shown_date', $today, 60 * 12 * 3);
        }

        return view('frontend.index', compact(
            'banners',
            'expiringServices',
            'categories',
            'recommendedPrivileges',
            'recommendedServices',
            'popupAd'
        ));
    }
}
