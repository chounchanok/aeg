<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward; // ต้องสร้าง Model Reward ด้วย
use App\Models\Banner; 
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index()
    {
        // ดึงรายการของรางวัลที่เปิดใช้งานอยู่
        $rewards = Reward::where('is_active', true)->orderBy('points_required', 'ASC')->get();
        $categories = DB::table('reward_categories')->get();
        $rewardsByCategory = $rewards->groupBy('category_id'); // สมมติว่ามีฟิลด์ category_id ในตาราง rewards
        $banners = Banner::where('location', 'ease_club')
                         ->where('is_active', true)
                         ->orderBy('sort_order', 'asc')
                         ->get();

        return view('frontend.reward-all', compact('rewards', 'categories', 'rewardsByCategory', 'banners'));
    }

    public function show($id)
    {
        // ดึงข้อมูลของรางวัลตาม ID
        $rewards = Reward::findOrFail($id);
        return view('frontend.reward', compact('rewards'));
    }
}
