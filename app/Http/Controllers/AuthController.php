<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginView()
    {
        return view('frontend.login', [
            'layout' => 'base',
            'dark_mode' => false,
            'color_scheme' => 'default'
        ]);
    }

    public function registerView()
    {
        return view('frontend.registration', [
            'layout' => 'base',
            'dark_mode' => false,
            'color_scheme' => 'default'
        ]);
    }

    // 🌟 เพิ่มฟังก์ชันสำหรับหน้าลืมรหัสผ่าน
    public function forgotPasswordView()
    {
        return view('frontend.forgot-password', [
            'layout' => 'base',
            'dark_mode' => false,
            'color_scheme' => 'default'
        ]);
    }

    public function login(Request $request)
    {
        // =========================================
        // 🌟 ส่วนที่ 1: ดักจับและจัดฟอร์แมตเบอร์มือถือ (Format Phone Number)
        // =========================================
        $phone = $request->input('phone');

        if ($phone) {
            // ลบอักขระพิเศษ (ช่องว่าง, ขีด, วงเล็บ) ให้เหลือแต่ตัวเลขและเครื่องหมาย +
            $phone = preg_replace('/[^0-9+]/', '', $phone);

            // ถ้าขึ้นต้นด้วย +66 ให้ตัดออกแล้วแทนด้วย 0
            if (str_starts_with($phone, '+66')) {
                $phone = '0' . substr($phone, 3);
            } 
            // ถ้าขึ้นต้นด้วย 66 และมีความยาว 11 ตัว ให้แทนด้วย 0 (เผื่อ User พิมพ์ 66812345678)
            elseif (str_starts_with($phone, '66') && strlen($phone) == 11) {
                $phone = '0' . substr($phone, 2);
            }

            // ถ้าความยาวเหลือ 9 ตัว (แปลว่าลืมพิมพ์ 0 นำหน้า) ให้เติม 0 เข้าไป
            if (strlen($phone) == 9 && in_array(substr($phone, 0, 1), ['6', '8', '9'])) {
                $phone = '0' . $phone;
            }

            // นำค่าที่แปลงจนสมบูรณ์แล้ว กลับไปอัปเดตใน Request
            $request->merge(['phone' => $phone]);
        }

        // =========================================
        // 🌟 ส่วนที่ 2: Validate ข้อมูล (หลังจากฟอร์แมตแล้ว)
        // =========================================
        $credentials = $request->validate([
            'phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/'], // บังคับว่าต้องขึ้นต้นด้วย 0 และมี 9-10 หลัก
            'password' => ['required'],
        ], [
            'phone.regex' => 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง'
        ]);
        
        // =========================================
        // 🌟 ส่วนที่ 3: เช็คการเข้าสู่ระบบ
        // =========================================
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // ตรวจสอบ Role ของผู้ใช้งานเพื่อแยกการ Redirect
            if (Auth::user()->role === 'customer') {
                return redirect()->intended('/'); // ลูกค้าไปหน้า Frontend
            }

            // ถ้าไม่ใช่ customer ไปหน้า Backend
            return redirect()->intended('/admin/dashboard');
        }

        // ถ้าล็อกอินไม่ผ่าน
        return back()->withErrors([
            'phone' => 'เบอร์มือถือหรือรหัสผ่านไม่ถูกต้อง',
        ])->onlyInput('phone');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}