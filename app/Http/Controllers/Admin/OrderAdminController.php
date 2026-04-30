<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAdminController extends Controller
{
    public function index()
    {
        // ดึงรายการคำสั่งซื้อ พร้อมชื่อลูกค้า
        $orders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->select('orders.*', 'users.username', 'customer_profiles.first_name', 'customer_profiles.last_name')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'first_level_active_index' => 'orders',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function show($id)
    {
        // 1. ดึงข้อมูลออเดอร์และลูกค้า
        $order = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->select('orders.*', 'users.username', 'users.email', 'users.phone', 'customer_profiles.first_name', 'customer_profiles.last_name')
            ->where('orders.id', $id)
            ->first();

        if (!$order) abort(404);

        // 2. ดึงรายการสินค้าในออเดอร์นี้ (Order Items)
        $orderItems = DB::table('order_items')->where('order_id', $id)->get();

        // 3. ดึงข้อมูลที่อยู่จัดส่ง (ถ้ามี)
        $address = null;
        if ($order->address_id) {
            $address = DB::table('customer_addresses')->where('id', $order->address_id)->first();
        }

        return view('admin.orders.show', [
            'order' => $order,
            'orderItems' => $orderItems,
            'address' => $address,
            'first_level_active_index' => 'orders',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_payment,paid,processing,completed,cancelled'
        ]);

        DB::table('orders')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'อัปเดตสถานะคำสั่งซื้อเรียบร้อยแล้ว');
    }
}