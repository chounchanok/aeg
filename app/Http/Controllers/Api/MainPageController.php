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

    public function getExpiringServices(Request $request)
    {
        // ดึงบริการที่ใกล้หมดอายุภายใน 30 วันของ User นั้น
        $expiring = DB::table('customer_products')
            ->where('customer_id', $request->user()->id)
            ->where('status', 'active')
            ->whereBetween('warranty_expire_date', [Carbon::now()->subDays(30), Carbon::now()])
            ->get();

        return $this->successResponse($expiring, 'Expiring services retrieved');
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
}