<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class EaseClubController extends Controller
{
    use ApiResponseTrait;

    public function getBanners()
    {
        $banners = DB::table('banners')->where('location', 'ease_club')->where('is_active', true)->get();
        return $this->successResponse($banners, 'Ease Club banners retrieved');
    }

    public function getBannersCategory()
    {
        $banners = DB::table('banners')->where('location', 'category')->where('is_active', true)->get();
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

    public function getOverview()
    {
        $categories = DB::table('reward_categories')->get();
        $advanceRewards = DB::table('rewards')->where('minimum_tier_required', 'Advance')->limit(4)->get();

        return $this->successResponse([
            'categories' => $categories,
            'advance_exclusive' => $advanceRewards
        ], 'Overview retrieved');
    }

    public function getRewardsByCategory(Request $request, $categoryId)
    {
        // 1. ดึง Rewards ทั้งหมดใน Category นี้ออกมา
        $rewards = DB::table('rewards')->where('category_id', $categoryId)->get();

        // 2. ตรวจสอบว่ามีการ Login อยู่หรือไม่
        $userId = $request->user() ? $request->user()->id : null;

        if ($userId) {
            // ดึง ID ของสินค้าที่ User คนนี้เคยกด Favorite ไว้ทั้งหมดมาเป็น Array
            // (เพื่อป้องกันปัญหา N+1 Query ที่ต้องยิง DB ซ้ำๆ ในลูป)
            $favoritedIds = DB::table('favorites')
                ->where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();

            // เอา Array ของ IDs มาเช็คและแนบค่า is_favorited กลับไป
            $rewards->map(function ($reward) use ($favoritedIds) {
                $reward->is_favorited = in_array($reward->id, $favoritedIds);
                return $reward;
            });
        } else {
            // ถ้าไม่ได้ Login ให้ is_favorited เป็น false ทั้งหมด
            $rewards->map(function ($reward) {
                $reward->is_favorited = false;
                return $reward;
            });
        }

        return $this->successResponse($rewards, 'Rewards retrieved successfully');
    }

    public function getRewardDetail(Request $request, $rewardId)
    {
        $reward = DB::table('rewards')->where('id', $rewardId)->first();

        if (!$reward) {
            return $this->errorResponse('Reward not found', 404);
        }

        // ตรวจสอบสถานะ Favorite สำหรับหน้า Detail
        $userId = $request->user() ? $request->user()->id : null;

        if ($userId) {
            $isFavorited = DB::table('favorites')
                ->where('user_id', $userId)
                ->where('product_id', $reward->id)
                ->exists(); // ใช้ exists() จะไวกว่า first() เพราะคืนค่า true/false ทันที

            $reward->is_favorited = $isFavorited;
        } else {
            $reward->is_favorited = false;
        }

        return $this->successResponse($reward, 'Reward detail retrieved');
    }

    public function redeemReward(Request $request, $rewardId)
    {
        $user = $request->user();
        $reward = DB::table('rewards')->where('id', $rewardId)->first();

        if (!$reward) return $this->errorResponse('Reward not found', 404);
        if ($reward->stock_quantity <= 0) return $this->errorResponse('Out of stock', 400);

        $wallet = DB::table('customer_wallets')->where('user_id', $user->id)->first();

        if(empty($wallet)){
            return $this->errorResponse('User wallet not found', 404);
        }

        if ($wallet->current_points < $reward->points_required) {
            return $this->errorResponse('Insufficient points', 400);
        }

        // เริ่ม Transaction ตัดพอยท์และบันทึกประวัติ
        DB::transaction(function () use ($user, $wallet, $reward) {
            // หักพอยท์
            DB::table('customer_wallets')->where('id', $wallet->id)->decrement('current_points', $reward->points_required);

            // ลดสต็อก
            DB::table('rewards')->where('id', $reward->id)->decrement('stock_quantity', 1);

            // บันทึกประวัติการแลก
            DB::table('reward_redemptions')->insert([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_used' => $reward->points_required,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // บันทึก Point Transaction
            DB::table('point_transactions')->insert([
                'user_id' => $user->id,
                'amount' => -$reward->points_required,
                'type' => 'redeem',
                'description' => 'Redeemed reward: ' . $reward->title_th,
                'created_at' => now()
            ]);
        });

        return $this->successResponse(null, 'Reward redeemed successfully');
    }
}
