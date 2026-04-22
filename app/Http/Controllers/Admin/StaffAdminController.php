<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffAdminController extends Controller
{
    public function index()
    {
        // ดึงเฉพาะ User ที่เป็นแอดมิน หรือ ช่างซ่อม
        $staffs = User::leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->whereIn('users.role', ['super_admin', 'admin', 'technician'])
            ->select('users.*', 'customer_profiles.first_name as name')
            ->orderBy('users.created_at', 'desc')
            ->get();

        return view('admin.staff.index', [
            'staffs' => $staffs,
            // ให้เมนูซ้ายมือ Active ตรงกับเมนูตั้งค่า > พนักงาน
            'first_level_active_index' => 'settings',
            'second_level_active_index' => 'staff',
            'third_level_active_index' => ''
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'required|string',
            'role' => 'required|in:admin,technician'
        ]);

        DB::beginTransaction();
        try {
            // 1. สร้างบัญชีเข้าสู่ระบบ
            $user = User::create([
                'username' => $request->username,
                'email' => $request->username . '@aeg.com', // อีเมลจำลองสำหรับพนักงาน
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
                'is_active' => true,
            ]);

            // 2. บันทึกชื่อ-สกุล ในตาราง Profile
            DB::table('customer_profiles')->insert([
                'user_id' => $user->id,
                'first_name' => $request->name,
                'phone' => $request->phone,
                'created_at' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'เพิ่มพนักงานเรียบร้อยแล้ว']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }
}