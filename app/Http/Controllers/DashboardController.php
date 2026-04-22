<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => DB::table('users')->where('role', 'customer')->count(),
            'pending_requests' => DB::table('service_requests')->where('status', 'pending')->count()
        ];

        return view('dashboard.index', [
            'stats' => $stats,
            // ส่งแค่ตัวแปรบอกว่าตอนนี้เมนูไหนกำลัง Active อยู่ (เพื่อให้เมนูมีสีสว่างขึ้น)
            'first_level_active_index' => 'dashboard', 
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }
}