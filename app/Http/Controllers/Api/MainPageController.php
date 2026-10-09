<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;

class MainPageController extends Controller
{
    use ApiResponseTrait;

    public function getBanners(Request $request)
    {
        $lang = $request->header('Accept-Language', 'th');
        $banners = DB::table('banners')
            ->where('location', 'main')
            ->where('is_active', true)
            ->get()
            ->map(fn ($b) => \App\Support\LocalizedImage::banner($b, $lang)); // 🌟 รูปภาษาอังกฤษ (fallback รูปไทย)
            
        return $this->successResponse($banners, 'Banners retrieved successfully');
    }

    public function getPopupAds(Request $request)
    {
        // 🌟 Popup Ads: หลังบ้านอัปโหลดได้หลายรูป ต้องการให้แอปสลับแสดงทีละรูปทุกครั้งที่เข้าหน้าแรก (ตามลำดับ sort_order)
        // และจำกัดไม่ให้รูปเดิมขึ้นซ้ำเกิน 1 ครั้ง/วัน/ผู้ใช้ — เนื่องจาก endpoint นี้เรียกได้แบบไม่ล็อกอิน (public)
        // จึงให้ฝั่งแอปเป็นคนเก็บสถานะ "แสดงไปแล้วกี่ id ในวันนี้" เอง (เช่น local storage) แล้วส่งมาที่ query param `shown_ids`
        // แอปต้องเทียบวันที่เองด้วย — ถ้าเปลี่ยนวันแล้วให้เคลียร์ shown_ids ที่เก็บไว้ก่อนเรียก (เริ่มรอบใหม่)
        //
        // Request:  GET /main/popup-ads?shown_ids=1,4,7   (shown_ids ไม่บังคับ — ไม่ส่งมา = ยังไม่เคยเห็นรูปไหนเลยวันนี้)
        // Response: data = popup ad object ตัวถัดไปที่ควรแสดง หรือ null ถ้าแสดงครบทุกรูปที่ Active แล้วในวันนี้
        $shownIds = array_filter(array_map('intval', explode(',', (string) $request->query('shown_ids', ''))));

        $activeAds = DB::table('popup_ads')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'title', 'image_url', 'image_url_en', 'link_url', 'sort_order'])
            ->map(fn ($ad) => \App\Support\LocalizedImage::popup($ad, $request->header('Accept-Language', 'th'))); // 🌟 รูปภาษาอังกฤษ

        $nextAd = $activeAds;

        return $this->successResponse($nextAd, 'Popup ad retrieved successfully');
    }

    public function getExpiringServices(Request $request)
    {
        $products = DB::table('customer_products')
            ->where('customer_id', $request->user()->id)
            ->where('status', 'active')
            ->whereBetween('warranty_expire_date', [now(), now()->addDays(30)])
            ->get()
            ->map(function ($item) {
                $item->image_url = $item->image_url ?? null;
                $item->service_type = 'product';
                $item->can_renew = false;
                return $item;
            });

        $lockers = DB::table('locker_bookings as b')
            ->join('smart_lockers as l', 'b.smart_locker_id', '=', 'l.id')
            ->where('b.user_id', $request->user()->id)
            ->whereNull('b.renewal_of_booking_id')
            ->whereIn('b.status', ['active', 'paid', 'completed'])
            ->whereBetween('b.end_date', [today()->toDateString(), today()->addDays(30)->toDateString()])
            ->orderBy('b.end_date')
            ->get([
                'b.id', 'b.booking_number', 'b.smart_locker_id', 'b.start_date', 'b.end_date', 'b.status',
                'l.locker_number', 'l.title_th', 'l.title_en', 'l.image_url', 'l.price as monthly_price',
            ])
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service_type' => 'smart_locker',
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'smart_locker_id' => $booking->smart_locker_id,
                    'locker_number' => $booking->locker_number,
                    'product_name' => 'ตู้เซฟนิรภัย ' . $booking->locker_number . ' · ' . $booking->title_th,
                    'serial_number' => $booking->locker_number,
                    'image_url' => $booking->image_url,
                    'start_date' => $booking->start_date,
                    'end_date' => $booking->end_date,
                    'warranty_expire_date' => $booking->end_date,
                    'status' => $booking->status,
                    'monthly_price' => (float) $booking->monthly_price,
                    'can_renew' => true,
                    'renewal' => [
                        'method' => 'POST',
                        'endpoint' => url('/api/smart-lockers/bookings/' . $booking->id . '/renew'),
                        'required_fields' => ['duration_months', 'payment_gateway'],
                        'payment_gateway_options' => ['bbl'],
                    ],
                ];
            });

        $expiring = $products->concat($lockers)->sortBy(function ($item) {
            return data_get($item, 'warranty_expire_date');
        })->values();

        return $this->successResponse($expiring, 'Expiring services retrieved successfully');
    }

    public function getRecommendedPrivileges(Request $request)
    {
        // ดึงของรางวัลที่ใช้คะแนนน้อย หรือเป็นที่นิยม
        // 🌟 ผูกกับ Tier ของลูกค้า (customer_wallets.current_tier_id) — ไม่แสดงรางวัลที่ Tier ยังไม่ถึง
        $query = DB::table('rewards')->where('is_active', true);
        \App\Services\RewardService::applyTierFilter($query, $request->user()->id);
        $privileges = $query->inRandomOrder()->limit(5)->get();
        return $this->successResponse($privileges, 'Recommended privileges retrieved');
    }

    public function getRecommendedServices()
    {
        // ดึงข้อมูล Content ข่าวสารหรือบริการจากหมวดหมู่ promotion
        $services = DB::table('rewards')
            // ->where('category', 'promotion')
            // ->where('status', 'published')
            ->limit(4)->get();
            
        return $this->successResponse($services, 'Recommended services retrieved');
    }

    public function getRecommendedProducts()
    {
        // ดึงข้อมูลสินค้าแนะนำจากหมวดหมู่ที่กำหนด (เช่น recommended)
        $products = DB::table('products')
                    ->inRandomOrder()
                    ->limit(4)
                    ->get();
        
            return $this->successResponse($products, 'Recommended products retrieved');
    }

    public function getServiceCategories(Request $request)
    {
        $lang = $request->header('Accept-Language', 'th');
        
        $query = DB::table('service_categories')->where('is_active', true);

        // 🌟 เพิ่มเติม: ถ้าน้องโอมส่ง ?group=equipment มา ให้กรองเอาเฉพาะกลุ่มนั้นๆ
        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        $categories = $query->orderBy('sort_order', 'asc')->get()->map(function ($cat) use ($lang) {
            return [
                'id' => $cat->id,
                'name' => ($lang == 'en' && !empty($cat->name_en)) ? $cat->name_en : $cat->name_th,
                'group' => $cat->group, // 🌟 ส่งค่ากลุ่ม (equipment/package/service) กลับไปด้วย
                'icon_url' => $cat->icon_url
            ];
        });

        return $this->successResponse($categories, 'Service categories retrieved successfully');
    }
}
