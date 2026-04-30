<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardAdminController extends Controller
{
    public function index()
    {
        // 1. สรุปตัวเลขสี่ช่องบน (Widget)
        $totalSales = DB::table('orders')->where('status', 'completed')->sum('total_amount');
        $pendingService = DB::table('service_requests')->where('status', 'pending')->count();
        $totalCustomers = DB::table('users')->where('role', 'customer')->count();
        $activeProducts = DB::table('products')->where('is_active', true)->count();

        // 2. ข้อมูลกราฟยอดขายย้อนหลัง 7 วัน
        $salesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $sum = DB::table('orders')
                ->whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $salesData['labels'][] = Carbon::now()->subDays($i)->format('d M');
            $salesData['values'][] = $sum;
        }

        // 3. รายการแจ้งซ่อมล่าสุด 5 รายการ
        $recentServices = DB::table('service_requests')
            ->join('users', 'service_requests.customer_id', '=', 'users.id')
            ->select('service_requests.*', 'users.username')
            ->orderBy('service_requests.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', [
            'totalSales' => $totalSales,
            'pendingService' => $pendingService,
            'totalCustomers' => $totalCustomers,
            'activeProducts' => $activeProducts,
            'salesData' => $salesData,
            'recentServices' => $recentServices,
            'first_level_active_index' => 'dashboard',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }
}