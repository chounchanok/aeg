<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // ดึงข้อมูลผู้ใช้ปัจจุบันไปแสดงที่หน้า My Account
    public function index()
    {
        $user = Auth::user();
        
        // ดึงข้อมูล Profile เพิ่มเติมจากตาราง customer_profiles แบบที่ API ทำ
        $profile = DB::table('customer_profiles')->where('user_id', $user->id)->first();
        return view('frontend.my-account', compact('user', 'profile'));
    }

    // อัปเดตข้อมูลผู้ใช้
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate ข้อมูลให้ตรงกับ Database
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'phone'      => 'required|string|max:20',
            'password'   => 'nullable|string|min:6|confirmed', 
        ]);

        DB::beginTransaction();
        try {
            // 1. อัปเดตข้อมูลในตาราง users (รหัสผ่าน และ เบอร์โทรหลัก)
            $updateUserData = [
                'phone' => $validated['phone'],
                // อัปเดตฟิลด์ name ใน users ให้เป็น ชื่อ-นามสกุล รวมกัน เผื่อนำไปใช้ง่ายๆ
                'name' => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''))
            ];

            if ($request->filled('password')) {
                $updateUserData['password'] = Hash::make($validated['password']);
            }
            
            DB::table('users')->where('id', $user->id)->update($updateUserData);

            // 2. อัปเดตข้อมูลในตาราง customer_profiles (ชื่อ และ นามสกุล แยกกัน)
            DB::table('customer_profiles')
                ->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'first_name' => $validated['first_name'],
                        'last_name'  => $validated['last_name'],
                        'phone'      => $validated['phone'],
                        'updated_at' => now()
                    ]
                );

            DB::commit();
            return back()->with('success', 'อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()]);
        }
    }

    // กดอ่านแจ้งเตือน
    public function readNotification($id)
    {
        // อัปเดตสถานะ is_read เป็น true (1)
        \Illuminate\Support\Facades\DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->update([
                'is_read' => true,
                'updated_at' => now()
            ]);

        // กลับไปที่หน้าเดิม (ระบบจะรีเฟรชและตัวเลขกระดิ่งจะลดลงอัตโนมัติ)
        // 💡 หมายเหตุ: อนาคตถ้าในตารางมีคอลัมน์ link สามารถเปลี่ยนให้ redirect() ไปหน้านั้นๆ แทน back() ได้ครับ
        return back();
    }
}