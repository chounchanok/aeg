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
        $profile = DB::table('customer_profiles')->where('user_id', $user->id)->first() ?? $user; // ดึงโปรไฟล์ (ปรับตามตารางจริงของพี่)

        // ดึงรายการที่อยู่
        $addresses = DB::table('customer_addresses')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.my-account', compact('user', 'profile', 'addresses'));
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

    // 2. ฟังก์ชันเพิ่มที่อยู่ใหม่
    public function storeAddress(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'contact_name' => 'required|string|max:100',
            'contact_phone' => 'required|string|max:20',
            'address_line' => 'required|string',
            'province' => 'required|string',
            'district' => 'required|string',
            'subdistrict' => 'required|string',
            'zipcode' => 'required|string'
        ]);

        DB::table('customer_addresses')->insert([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'address_line' => $request->address_line,
            'province' => $request->province,
            'district' => $request->district,
            'subdistrict' => $request->subdistrict,
            'zipcode' => $request->zipcode,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'เพิ่มที่อยู่ใหม่เรียบร้อยแล้ว');
    }

    // 3. ฟังก์ชันอัปเดตที่อยู่
    public function updateAddress(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'contact_name' => 'required|string|max:100',
            'contact_phone' => 'required|string|max:20',
            'address_line' => 'required|string',
            'province' => 'required|string',
            'district' => 'required|string',
            'subdistrict' => 'required|string',
            'zipcode' => 'required|string'
        ]);

        // เช็คว่าเป็นเจ้าของที่อยู่จริงไหม
        $address = DB::table('customer_addresses')->where('id', $id)->where('user_id', Auth::id())->first();
        if (!$address) return back()->withErrors(['error' => 'ไม่พบข้อมูลที่อยู่นี้']);

        DB::table('customer_addresses')->where('id', $id)->update([
            'title' => $request->title,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'address_line' => $request->address_line,
            'province' => $request->province,
            'district' => $request->district,
            'subdistrict' => $request->subdistrict,
            'zipcode' => $request->zipcode,
            'updated_at' => now()
        ]);

        return back()->with('success', 'อัปเดตข้อมูลที่อยู่เรียบร้อยแล้ว');
    }

    // 4. ฟังก์ชันลบที่อยู่
    public function deleteAddress($id)
    {
        DB::table('customer_addresses')->where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'ลบที่อยู่เรียบร้อยแล้ว');
    }

    // ==========================================
    // บันทึกข้อมูลเพิ่มเติมอัตโนมัติ (AJAX Auto-Save)
    // ==========================================
    public function updateAdditionalInfo(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'company_type' => 'nullable|string',
            'service_interesting' => 'nullable|array'
        ]);

        $profileData = [
            'company_type' => $request->company_type,
            'updated_at' => now()
        ];

        // จัดการ Array เหมือนตอน Update ปกติ
        if ($request->has('service_interesting') && is_array($request->service_interesting)) {
            $profileData['service_interesting'] = json_encode($request->service_interesting, JSON_UNESCAPED_UNICODE);
        } else {
            $profileData['service_interesting'] = json_encode([], JSON_UNESCAPED_UNICODE); 
        }

        DB::table('customer_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            $profileData
        );

        return response()->json(['status' => 'success', 'message' => 'บันทึกข้อมูลอัตโนมัติเรียบร้อยแล้ว']);
    }
}
