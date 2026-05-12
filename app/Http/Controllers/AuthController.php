<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ... ฟังก์ชัน loginView(), registerView(), forgotPasswordView() คงไว้เหมือนเดิม ...
    public function loginView()
    {
        // ส่งตัวแปร layout ไปให้ View
        return view('frontend.login', [
            'layout' => 'base', // ใช้ base layout เพราะหน้า login ไม่ต้องการเมนู sidebar
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

    public function forgotPasswordView()
    {
        return view('frontend.forgot-password', [
            'layout' => 'base',
            'dark_mode' => false,
            'color_scheme' => 'default'
        ]);
    }

    // ==========================================
    // 1. เข้าสู่ระบบ (Login) - คงโค้ดเดิมของคุณไว้ได้เลย 
    // ==========================================
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

    // ==========================================
    // 2. สมัครสมาชิก (Register) - แบบรับแค่เบอร์มือถือ
    // ==========================================
    public function registerSubmit(Request $request)
    {
        // 1. จัดการ Format เบอร์มือถือ (ดึง Logic มาจากหน้า Login ของคุณ)
        $phone = $request->input('phone');
        if ($phone) {
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            if (str_starts_with($phone, '+66')) {
                $phone = '0' . substr($phone, 3);
            } elseif (str_starts_with($phone, '66') && strlen($phone) == 11) {
                $phone = '0' . substr($phone, 2);
            }
            if (strlen($phone) == 9 && in_array(substr($phone, 0, 1), ['6', '8', '9'])) {
                $phone = '0' . $phone;
            }
            $request->merge(['phone' => $phone]);
        }

        // 2. Validate ตรวจสอบว่าเบอร์นี้ต้องไม่เคยซ้ำในระบบ
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/', 'unique:users,phone'],
        ], [
            'phone.regex' => 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง',
            'phone.unique' => 'เบอร์มือถือนี้มีการลงทะเบียนในระบบแล้ว'
        ]);

        DB::beginTransaction();
        try {
            // 3. สร้างค่าจำลองสำหรับฟิลด์ที่บังคับใน Database
            $dummyUsername = 'user_' . $request->phone;
            $dummyName = 'Guest_' . substr($request->phone, -4); // เช่น Guest_4567
            $randomPassword = Str::random(12); // สุ่มรหัสผ่านไปก่อน เพราะเราล็อกอินด้วย OTP

            // สร้าง User
            $user_data = [
                'username' => $dummyUsername,
                'email' => $dummyUsername . '@temp.com',
                'phone' => $request->phone,
                'password' => Hash::make($randomPassword),
                'role' => 'customer',
                'is_active' => false, // รอการยืนยัน OTP
            ];
            $user = User::create($user_data);

            // สร้าง Profile
            DB::table('customer_profiles')->insert([
                'user_id' => $user->id,
                'first_name' => $dummyName,
                'phone' => $request->phone,
                'created_at' => now(),
            ]);

            // สร้าง OTP
            $otpCode = '123456'; // สำหรับใช้เทส (Production ค่อยต่อ SMS Gateway)
            DB::table('otp_codes')->insert([
                'phone' => $request->phone,
                'code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(5),
                'created_at' => now(),
            ]);

            DB::commit();

            // ส่งเบอร์โทรไปเพื่อแสดงในหน้ายืนยัน OTP
            return redirect()->route('verify-otp')->with('register_phone', $request->phone);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['phone' => 'ระบบขัดข้อง ไม่สามารถสมัครสมาชิกได้: ' . $e->getMessage()])->withInput();
        }
    }

    // ==========================================
    // 3. ยืนยัน OTP (Verify OTP) - แปลงจาก API
    // ==========================================
    public function verifyOtpView(Request $request)
    {
        $phone = session('register_phone');
        if (!$phone) {
            return redirect()->route('register');
        }
        return view('frontend.verify-otp', compact('phone'));
    }

    public function verifyOtpSubmit(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        $otp = DB::table('otp_codes')
            ->where('phone', $request->phone)
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'รหัส OTP ไม่ถูกต้องหรือหมดอายุ']);
        }

        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            $user->update([
                'is_active' => true,
                'phone_verified_at' => now()
            ]);
            
            DB::table('otp_codes')->where('phone', $request->phone)->delete();

            // แทนที่จะออก Token ให้สร้าง Session Login ฝั่ง Web
            Auth::login($user);

            return redirect()->route('home')->with('success', 'ยืนยันตัวตนและเข้าสู่ระบบสำเร็จ');
        }

        return back()->withErrors(['error' => 'ไม่พบข้อมูลผู้ใช้งาน']);
    }

    // ==========================================
    // 4. ลืมรหัสผ่าน (Forgot & Reset Password)
    // ==========================================
    public function forgotPasswordSubmit(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'ส่งลิงก์ตั้งรหัสผ่านใหม่ไปยังอีเมลของคุณแล้ว');
        }
        return back()->withErrors(['email' => 'ไม่สามารถส่งอีเมลได้ กรุณาลองใหม่']);
    }

    public function resetPasswordView($token, Request $request)
    {
        return view('frontend.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPasswordSubmit(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'รีเซ็ตรหัสผ่านสำเร็จ กรุณาเข้าสู่ระบบใหม่');
        }

        return back()->withErrors(['email' => trans($response)]);
    }

    
}