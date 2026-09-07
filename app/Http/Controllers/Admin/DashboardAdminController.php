<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class DashboardAdminController extends Controller
{
    public function index()
    {
        // 🌟 RBAC: ตัวเลขยอดขาย/กราฟ แสดงเฉพาะแผนกที่ดูคำสั่งซื้อได้ (orders.manage — Security Admin, บัญชี)
        // และรายการแจ้งซ่อม แสดงเฉพาะแผนกที่จัดการแจ้งซ่อมได้ (service_requests.manage — Security Admin)
        // แผนกอื่น (เช่น Marketing, Insurance) จะเห็นเฉพาะจำนวนลูกค้า/สินค้า — กันข้อมูลรั่วข้ามแผนก
        $canViewOrders = Gate::allows('orders.manage');
        $canViewServiceRequests = Gate::allows('service_requests.manage');

        // 1. สรุปตัวเลขสี่ช่องบน (Widget)
        $totalSales = $canViewOrders ? DB::table('orders')->where('status', 'completed')->sum('total_amount') : null;
        $pendingService = $canViewServiceRequests ? DB::table('service_requests')->where('status', 'pending')->count() : null;
        $totalCustomers = DB::table('users')->where('role', 'customer')->count();
        $activeProducts = DB::table('products')->where('is_active', true)->count();

        // 2. ข้อมูลกราฟยอดขายย้อนหลัง 7 วัน
        $salesData = ['labels' => [], 'values' => []];
        for ($i = 6; $i >= 0 && $canViewOrders; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $sum = DB::table('orders')
                ->whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $salesData['labels'][] = Carbon::now()->subDays($i)->format('d M');
            $salesData['values'][] = $sum;
        }

        // 3. รายการแจ้งซ่อมล่าสุด 5 รายการ
        $recentServices = $canViewServiceRequests
            ? DB::table('service_requests')
                ->join('users', 'service_requests.customer_id', '=', 'users.id')
                ->select('service_requests.*', 'users.username')
                ->orderBy('service_requests.created_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        return view('admin.dashboard.index', [
            'totalSales' => $totalSales,
            'pendingService' => $pendingService,
            'totalCustomers' => $totalCustomers,
            'activeProducts' => $activeProducts,
            'salesData' => $salesData,
            'recentServices' => $recentServices,
            'canViewOrders' => $canViewOrders,
            'canViewServiceRequests' => $canViewServiceRequests,
            'first_level_active_index' => 'dashboard',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }
}