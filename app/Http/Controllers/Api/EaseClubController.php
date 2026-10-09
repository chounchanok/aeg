<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use App\Services\RewardService;

class EaseClubController extends Controller
{
    use ApiResponseTrait;

    public function getBanners(Request $request)
    {
        $banners = DB::table('banners')->where('location', 'ease_club')->where('is_active', true)->get()
            ->map(fn ($b) => \App\Support\LocalizedImage::banner($b, $request->header('Accept-Language', 'th')));
        return $this->successResponse($banners, 'Ease Club banners retrieved');
    }

    public function getBannersCategory(Request $request)
    {
        $banners = DB::table('banners')->where('location', 'category')->where('is_active', true)->get()
            ->map(fn ($b) => \App\Support\LocalizedImage::banner($b, $request->header('Accept-Language', 'th')));
        return $this->successResponse($banners, 'Category Ease Club banners retrieved');
    }

    public function getUserInfo(Request $request)
    {
        $user = $request->user();

        // จอยข้อมูล Profile กับ Wallet เพื่อดึง ชื่อ, Tier, Points, Member ID และ วันหมดอายุ
        $userInfo = DB::table('users')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->leftJoin('customer_wallets', 'users.id', '=', 'customer_wallets.user_id')
            ->leftJoin('loyalty_tiers', 'customer_wallets.current_tier_id', '=', 'loyalty_tiers.id')
            ->where('users.id', $user->id)
            ->select(
                'users.username',
                'customer_profiles.first_name',
                'customer_profiles.last_name',
                'customer_profiles.profile_image_url',
                'customer_wallets.current_points',
                'customer_wallets.member_id',
                'customer_wallets.points_expiry_date',
                'loyalty_tiers.name as tier_name'
            )
            ->first();

        if (!$userInfo) {
            return $this->errorResponse('User info not found', 404);
        }

        // 1. จัดการชื่อ (ถ้าไม่มีชื่อ-นามสกุล ให้ใช้ username แทน)
        $name = trim(($userInfo->first_name ?? '') . ' ' . ($userInfo->last_name ?? ''));
        if (empty($name)) {
            $name = $userInfo->username;
        }

        // 2. จัดการ Member ID (ถ้าใน DB ยังเป็น null ให้สร้างจำลองรูปแบบ AEG-00000X)
        $memberId = $userInfo->member_id ?? 'AEG-' . str_pad($user->id, 6, '0', STR_PAD_LEFT);

        // 3. จัดการวันหมดอายุคะแนน (ถ้าใน DB ยังเป็น null ให้ตั้งเป็นวันสิ้นปีนี้ของปีปัจจุบัน)
        $expiryDate = $userInfo->points_expiry_date
            ? \Carbon\Carbon::parse($userInfo->points_expiry_date)->format('Y-m-d')
            : \Carbon\Carbon::now()->endOfYear()->format('Y-m-d');

        if($userInfo->tier_name == 'Advance'){
            $profile_image_card = asset('assets/image/card-advance.webp');
        } elseif($userInfo->tier_name == 'Platinum'){
            $profile_image_card = asset('assets/image/card-platinum.webp');
        } elseif($userInfo->tier_name == 'Beyond'){
            $profile_image_card = asset('assets/image/card-beyond.webp');
        } else {
            $profile_image_card = null;
        }

        // 4. จัดเรียงข้อมูล Response ให้ Mobile App นำไปใช้ง่ายๆ
        $data = [
            'member_id' => $memberId,
            'name' => $name,
            'tier' => $userInfo->tier_name ?? 'Advance',
            'current_points' => $userInfo->current_points ?? 0,
            'points_expiry_date' => $expiryDate,
            'profile_image_url' => $userInfo->profile_image_url ?? null,
            'profile_image_card' => $profile_image_card
        ];

        return $this->successResponse($data, 'User info retrieved successfully');
    }

    public function getOverview(Request $request)
    {
        $user = $request->user('sanctum');
        $tier = RewardService::userTier($user?->id);

        $categories = DB::table('reward_categories')->get();

        // 🌟 สิทธิพิเศษ: แสดงเฉพาะรางวัลที่กำหนด Tier ไว้ และ Tier ของลูกค้า (customer_wallets) ถึงแล้ว
        // (เดิม hardcode เป็น 'Advance' — ตอนนี้ผูกกับ Tier จริงของลูกค้า; guest เห็นเฉพาะ Tier ต่ำสุด)
        $exclusiveQuery = DB::table('rewards')
            ->where('is_active', true)
            ->whereNotNull('minimum_tier_required')
            ->where('minimum_tier_required', '!=', '');
        RewardService::applyTierFilter($exclusiveQuery, $user?->id);
        $exclusive = $exclusiveQuery->orderByDesc('id')->limit(4)->get();

        return $this->successResponse([
            'categories' => $categories,
            'tier' => $tier?->name,
            'advance_exclusive' => $exclusive, // คงชื่อ key เดิมไว้ให้แอปเวอร์ชันเก่า
            'tier_exclusive' => $exclusive,
        ], 'Overview retrieved');
    }

    public function getRewardsByCategory(Request $request, $categoryId)
    {
        // 🌟 1. ดึงข้อมูล User ก่อน (รองรับทั้งตอนล็อกอินและเป็น Guest)
        $user = $request->user('sanctum');

        $query = DB::table('rewards')->where('category_id', $categoryId)->where('is_active', true);

        // 🌟 ซ่อนรางวัลที่ Tier ของลูกค้ายังไม่ถึง (Tier อ่านจาก customer_wallets.current_tier_id)
        RewardService::applyTierFilter($query, $user?->id);

        // 🌟 2. แทรกเงื่อนไขการเรียงลำดับ (Order By)
        if ($user) {
            // เช็คว่าของรางวัลนี้อยู่ในตาราง favorites ไหม ถ้ามีให้นับเป็น 1 แล้วดันขึ้นบนสุด
            // (ใช้คอลัมน์ product_id เพราะตาราง favorites เก็บ ID รางวัลไว้ในคอลัมน์นี้เหมือนกัน)
            $query->orderByRaw("(SELECT COUNT(*) FROM favorites WHERE favorites.product_id = rewards.id AND favorites.user_id = ?) DESC", [$user->id]);
        }
        
        // ให้เรียงตามวันที่สร้างเป็นลำดับที่ 2
        $query->orderBy('points_required', 'ASC');

        // 3. ดึง Rewards ออกมาจากฐานข้อมูล
        $rawRewards = $query->get();

        // 4. ดึง ID ของรางวัลที่ User คนนี้เคยกด Favorite ไว้ทั้งหมดมาเป็น Array
        $favoriteRewardIds = [];
        if ($user) {
            $favoriteRewardIds = DB::table('favorites')
                ->where('user_id', $user->id)
                ->whereIn('product_id', $rawRewards->pluck('id'))
                ->pluck('product_id')
                ->toArray();
        }

        // 5. เอา Array ของ IDs มาเช็คและแนบค่า is_favorite กลับไป
        $rewards = $rawRewards->map(function ($reward) use ($favoriteRewardIds) {
            $reward->is_favorite = in_array($reward->id, $favoriteRewardIds);
            return $reward;
        });

        return $this->successResponse($rewards, 'Rewards retrieved successfully');
    }

    public function getRewardDetail(Request $request, $rewardId)
    {
        $reward = DB::table('rewards')->where('id', $rewardId)->first();
        $viewer = $request->user('sanctum');

        // 🌟 รางวัลที่ Tier ไม่ถึงถูกซ่อน → ตอบเหมือนไม่พบ
        if (!$reward || !RewardService::canAccessReward($reward, $viewer?->id)) {
            return $this->errorResponse('Reward not found', 404);
        }

        $reward->reward_type = RewardService::rewardType($reward);

        // 🌟 ข้อมูลเงื่อนไข/การจัดส่งของรางวัล (แอดมินกรอกจากหลังบ้าน /admin/cms/ease-club)
        // set ค่าให้ชัดเจนเสมอ เพื่อให้ mobile ได้ key ครบทุกครั้งแม้แอดมินยังไม่ได้กรอก
        $reward->return_policy = $reward->return_policy ?? null;          // เงื่อนไขการยกเลิกหรือคืนคะแนน
        $reward->shipping_fee = (float) ($reward->shipping_fee ?? 0);      // ค่าจัดส่ง (บาท) 0 = ส่งฟรี
        $reward->delivery_estimate = $reward->delivery_estimate ?? null;  // ระยะเวลาจัดส่ง เช่น "3-5 วันทำการ"

        // ตรวจสอบสถานะ Favorite สำหรับหน้า Detail
        $userId = $viewer ? $viewer->id : null;

        if ($userId) {
            $isFavorited = DB::table('favorites')
                ->where('user_id', $userId)
                ->where('product_id', $reward->id)
                ->exists(); // ใช้ exists() จะไวกว่า first() เพราะคืนค่า true/false ทันที

            $reward->is_favorited = $isFavorited;

            // 🌟 แนบข้อมูลคะแนนของผู้ใช้คนนี้ไปด้วย ตามที่ QA ขอ (ข้อ 2 ของเมล) — คะแนนคงเหลือ /
            // คะแนนที่ยังขาด / สถานะแลกได้หรือไม่ — เพื่อให้ mobile แสดงผลได้ถูกต้องโดยไม่ต้องยิง
            // ไปดึงจาก endpoint อื่นแยกกัน (getUserInfo) ซึ่งเสี่ยงข้อมูลไม่ sync กัน
            $wallet = DB::table('customer_wallets')->where('user_id', $userId)->first();
            $currentPoints = $wallet->current_points ?? 0;

            $reward->current_points = $currentPoints;
            $reward->points_missing = max(0, $reward->points_required - $currentPoints);
            $reward->can_redeem = $currentPoints >= $reward->points_required;
            $reward->user_id = $userId;
        } else {
            $reward->is_favorited = false;
            $reward->current_points = null;
            $reward->points_missing = $reward->points_required;
            $reward->can_redeem = false;
            $reward->user_id = null;
        }

        return $this->successResponse($reward, 'Reward detail retrieved');
    }

    public function redeemReward(Request $request, $rewardId)
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'address_id' => 'nullable|integer',
            'address_text' => 'nullable|string',
        ]);

        // 🌟 logic การแลกรวมไว้ที่ RewardService (ใช้ร่วมกับ POST /rewards/redeem) — เช็ค Tier + ล็อก wallet กันกดซ้ำ
        // สินค้า: ที่อยู่ส่งมาตอนนี้หรือตอนกด "ใช้คูปอง" (POST /rewards/my-codes/{id}/use) ก็ได้
        $result = RewardService::redeem($request->user()->id, (int) $rewardId, $data);

        return $result['ok']
            ? $this->successResponse($result['data'], $result['message'])
            : $this->errorResponse($result['message'], $result['status']);
    }
}
