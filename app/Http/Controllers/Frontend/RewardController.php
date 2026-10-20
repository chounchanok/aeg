<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Banner; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    public function index()
    {
        // 🌟 1. ดึง Tier ของ User ที่ล็อกอินอยู่
        $eligibleTierNames = [];
        $hasUserTier = false;

        if (Auth::check()) {
            $userTier = DB::table('customer_wallets')
                ->join('loyalty_tiers', 'customer_wallets.current_tier_id', '=', 'loyalty_tiers.id')
                ->where('customer_wallets.user_id', Auth::id())
                ->select('loyalty_tiers.*')
                ->first();

            if ($userTier) {
                $hasUserTier = true;

                // เช็คคอลัมน์ชื่อ Tier ใน loyalty_tiers (ปกติคือ 'name' หรือ 'tier_name')
                $nameColumn = isset($userTier->name) ? 'name' : (isset($userTier->tier_name) ? 'tier_name' : 'name');

                // เช็คคอลัมน์ลำดับขั้น เช่น 'level', 'sort_order' หรือใช้ 'id' ตามลำดับ
                $tierColumn = isset($userTier->level) ? 'level' : (isset($userTier->sort_order) ? 'sort_order' : 'id');
                $userTierValue = $userTier->{$tierColumn};

                // ดึง "รายชื่อ Tier" ทั้งหมดที่มีระดับน้อยกว่าหรือเท่ากับ Tier ปัจจุบันของ User
                // เช่น ถ้า User เป็น Platinum จะได้ ['Advance', 'Platinum']
                $eligibleTierNames = DB::table('loyalty_tiers')
                    ->where($tierColumn, '<=', $userTierValue)
                    ->pluck($nameColumn)
                    ->toArray();
            }
        }

        // 🌟 2. Base Query กรองของรางวัลตามชื่อ Tier
        $rewardQuery = Reward::where('is_active', true);

        if ($hasUserTier) {
            // สมาชิกที่ล็อกอิน: แสดงรางวัลทั่วไป (NULL หรือ ค่าว่าง) หรือรางวัลที่ Tier สิทธิ์ถึง
            $rewardQuery->where(function ($query) use ($eligibleTierNames) {
                $query->whereNull('minimum_tier_required')
                      ->orWhere('minimum_tier_required', '')
                      ->orWhereIn('minimum_tier_required', $eligibleTierNames);
            });
        } else {
            // ยังไม่ได้ล็อกอิน: แสดงเฉพาะรางวัลทั่วไปที่ไม่ได้กำหนด Tier
            $rewardQuery->where(function ($query) {
                $query->whereNull('minimum_tier_required')
                      ->orWhere('minimum_tier_required', '');
            });
        }

        // 🌟 3. ดึงรายการของรางวัลหลัก และ 6 รายการสุ่ม
        $rewards = (clone $rewardQuery)->orderBy('points_required', 'ASC')->get();
        $randomRewards = (clone $rewardQuery)->inRandomOrder()->take(6)->get();

        $categories = DB::table('reward_categories')->get();
        $rewardsByCategory = $rewards->groupBy('category_id');
        $banners = Banner::where('location', 'ease_club')
                         ->where('is_active', true)
                         ->orderBy('sort_order', 'asc')
                         ->get();

        return view('frontend.reward-all', compact('rewards', 'categories', 'rewardsByCategory', 'banners', 'randomRewards'));
    }

    public function show($id)
    {
        $rewards = Reward::findOrFail($id);
        return view('frontend.reward', compact('rewards'));
    }
}