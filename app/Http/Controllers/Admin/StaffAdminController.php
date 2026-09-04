<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
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

        // 🌟 ดึง RBAC role (แผนก) ของพนักงานทุกคนมาแนบไว้ในคราวเดียว (กัน N+1 query)
        $roleNamesByUser = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->whereIn('user_roles.user_id', $staffs->pluck('id'))
            ->select('user_roles.user_id', 'roles.name')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('name')->implode(', '));

        $staffs->each(function ($staff) use ($roleNamesByUser) {
            $staff->department_names = $roleNamesByUser[$staff->id] ?? '-';
        });

        // 🌟 รายการแผนก (RBAC roles) ทั้งหมด สำหรับ dropdown เลือกตอนเพิ่ม/แก้ไขพนักงาน
        $roles = Role::orderBy('name')->get();

        return view('admin.staff.index', [
            'staffs' => $staffs,
            'roles' => $roles,
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
            'role' => 'required|in:admin,technician',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
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

            // 3. 🌟 ผูกแผนก/สิทธิ์ (RBAC role) ให้พนักงานคนนี้ — เลือกได้หลายแผนกพร้อมกัน
            // (ช่างซ่อม role=technician มักไม่ต้องมี RBAC role เพราะใช้งานผ่าน mobile app แยกอยู่แล้ว
            // จึงปล่อยว่างได้ ไม่บังคับ required)
            if (!empty($request->role_ids)) {
                $user->roles()->sync($request->role_ids);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'เพิ่มพนักงานเรียบร้อยแล้ว']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ดึงข้อมูลพนักงานคนหนึ่งมาแก้ไข (ใช้เปิด modal แก้ไขฝั่ง frontend ผ่าน AJAX)
     */
    public function edit($id)
    {
        $staff = User::leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->where('users.id', $id)
            ->select('users.*', 'customer_profiles.first_name as name')
            ->firstOrFail();

        $currentRoleIds = DB::table('user_roles')->where('user_id', $id)->pluck('role_id');

        return response()->json([
            'success' => true,
            'staff' => $staff,
            'role_ids' => $currentRoleIds,
        ]);
    }

    /**
     * แก้ไขข้อมูลพนักงาน — ชื่อ, เบอร์โทร, ตำแหน่งเดิม (admin/technician), แผนก (RBAC roles),
     * สถานะเปิด/ปิดใช้งาน และรหัสผ่านใหม่ (ถ้ากรอกมา)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'role' => 'required|in:admin,technician,super_admin',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'phone' => $request->phone,
                'role' => $request->role,
                'is_active' => $request->has('is_active') ? (bool) $request->is_active : $user->is_active,
            ];

            if (!empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            DB::table('customer_profiles')->updateOrInsert(
                ['user_id' => $user->id],
                ['first_name' => $request->name, 'phone' => $request->phone, 'updated_at' => now()]
            );

            // 🌟 sync() จะแทนที่ role เดิมทั้งหมดด้วยชุดใหม่ที่เลือกมา (ถอดออกจากแผนกเดิมที่ไม่ได้เลือกแล้วโดยอัตโนมัติ)
            $user->roles()->sync($request->role_ids ?? []);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'แก้ไขข้อมูลพนักงานเรียบร้อยแล้ว']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }
}
