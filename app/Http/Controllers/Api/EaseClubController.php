<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class EaseClubController extends Controller
{
    use ApiResponseTrait;

    public function getUserInfo(Request $request)
    {
        $user = $request->user();
        
        // จอยข้อมูล Profile กับ Wallet เพื่อดึง Tier และ Points
        $userInfo = DB::table('users')
            ->join('customer_wallets', 'users.id', '=', 'customer_wallets.user_id')
            ->join('loyalty_tiers', 'customer_wallets.current_tier_id', '=', 'loyalty_tiers.id')
            ->where('users.id', $user->id)
            ->select('users.id', 'users.username', 'customer_wallets.current_points', 'loyalty_tiers.name as tier_name')
            ->first();

        return $this->successResponse($userInfo, 'User info retrieved');
    }

    public function getBanners()
    {
        $banners = DB::table('banners')->where('location', 'ease_club')->where('is_active', true)->get();
        return $this->successResponse($banners, 'Ease Club banners retrieved');
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

    public function getRewardsByCategory($categoryId)
    {
        $rewards = DB::table('rewards')->where('category_id', $categoryId)->get();
        return $this->successResponse($rewards, 'Rewards retrieved successfully');
    }

    public function getRewardDetail($rewardId)
    {
        $reward = DB::table('rewards')->where('id', $rewardId)->first();
        if (!$reward) return $this->errorResponse('Reward not found', 404);

        return $this->successResponse($reward, 'Reward detail retrieved');
    }

    public function redeemReward(Request $request, $rewardId)
    {
        $user = $request->user();
        $reward = DB::table('rewards')->where('id', $rewardId)->first();

        if (!$reward) return $this->errorResponse('Reward not found', 404);
        if ($reward->stock_quantity <= 0) return $this->errorResponse('Out of stock', 400);

        $wallet = DB::table('customer_wallets')->where('user_id', $user->id)->first();

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
                'description' => 'Redeemed reward: ' . $reward->title,
                'created_at' => now()
            ]);
        });

        return $this->successResponse(null, 'Reward redeemed successfully');
    }
}