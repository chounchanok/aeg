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

    public function getBanners()
    {
        $banners = DB::table('banners')
            ->where('location', 'main')
            ->where('is_active', true)
            ->get();
            
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
            ->get(['id', 'title', 'image_url', 'link_url', 'sort_order']);

        $nextAd = $activeAds->first(fn ($ad) => !in_array($ad->id, $shownIds, true));

        return $this->successResponse($nextAd, 'Popup ad retrieved successfully');
    }

    public function getExpiringServices(Request $request)
    {
        // ดึงบริการที่ใกล้หมดอายุโดยตรง (ไม่ Join ตาราง products)
        $expiring = DB::table('customer_products')
            ->where('customer_id', $request->user()->id)
            ->where('status', 'active')
            ->whereBetween('warranty_expire_date', [now(), now()->addDays(30)])
            ->get()
            ->map(function ($item) {
                // จัด Format ป้องกันตัวแปรหาย
                $item->image_url = $item->image_url ?? null; 
                return $item;
            });

        return $this->successResponse($expiring, 'Expiring services retrieved successfully');
    }

    public function getRecommendedPrivileges()
    {
        // ดึงของรางวัลที่ใช้คะแนนน้อย หรือเป็นที่นิยม
        $privileges = DB::table('rewards')->inRandomOrder()->limit(5)->get();
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