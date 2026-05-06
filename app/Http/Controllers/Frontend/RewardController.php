<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward; // ต้องสร้าง Model Reward ด้วย

class RewardController extends Controller
{
    public function index()
    {
        // ดึงรายการของรางวัลที่เปิดใช้งานอยู่
        $rewards = Reward::where('is_active', true)->get();
        return view('frontend.reward', compact('rewards'));
    }
}