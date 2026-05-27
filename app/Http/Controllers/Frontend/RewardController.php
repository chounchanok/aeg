<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward; // ต้องสร้าง Model Reward ด้วย
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index()
    {
        // ดึงรายการของรางวัลที่เปิดใช้งานอยู่
        $rewards = Reward::where('is_active', true)->get();
        $categories = DB::table('reward_categories')->get();
        $rewardsByCategory = $rewards->groupBy('category_id'); // สมมติว่ามีฟิลด์ category_id ในตาราง rewards

        return view('frontend.reward-all', compact('rewards', 'categories', 'rewardsByCategory'));
    }

    public function show($id)
    {
        // ดึงข้อมูลของรางวัลตาม ID
        $rewards = Reward::findOrFail($id);
        return view('frontend.reward', compact('rewards'));
    }
}
