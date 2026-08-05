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
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;

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
    // 2. สมัครสมาชิก (Register) - เปลี่ยนเป็นระบบ OTP ของ ThaiBulkSMS
    // ==========================================
    public function registerSubmit(Request $request)
    {
        // 1. จัดการ Format เบอร์มือถือ
        $phone = $request->input('phone');
        if ($phone) {
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            if (str_starts_with($phone, '+66')) $phone = '0' . substr($phone, 3);
            elseif (str_starts_with($phone, '66') && strlen($phone) == 11) $phone = '0' . substr($phone, 2);
            if (strlen($phone) == 9 && in_array(substr($phone, 0, 1), ['6', '8', '9'])) $phone = '0' . $phone;
            $request->merge(['phone' => $phone]);
        }

        // 2. Validate ตรวจสอบว่าเบอร์นี้ต้องไม่เคยซ้ำในระบบ
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/', 'unique:users,phone'],
        ], [
            'phone.regex' => 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง',
            'phone.unique' => 'เบอร์มือถือนี้มีการลงทะเบียนในระบบแล้ว'
        ]);

        try {
            // 3. ยิง API ขอ OTP จาก ThaiBulkSMS
            // 💡 นำ App Key และ App Secret จากเว็บ ThaiBulk มาใส่ตรงนี้นะครับ
            $appKey = env('THAIBULK_APP_KEY', '17828749558276');
            $appSecret = env('THAIBULK_APP_SECRET', '5fc3972aecabf0f433a9174bf885a0d5');
            $apiUrl = 'https://otp.thaibulksms.com/v2/otp/request';

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($apiUrl, [
                'key' => $appKey,
                'secret' => $appSecret,
                'msisdn' => $request->phone // เบอร์ที่ลูกค้ากรอก
            ]);

            $responseData = $response->json();

            // 🌟 เก็บ Log ไว้เช็ค
            \Illuminate\Support\Facades\Log::info('ThaiBulkSMS Web Register OTP:', $responseData);

            // 4. เช็คผลลัพธ์ว่า ThaiBulkSMS รับเรื่องสำเร็จหรือไม่
            if ($response->successful() && isset($responseData['token'])) {

                // 🌟 แก้ไขบรรทัดนี้เหมือนฝั่ง API
                $refCode = $responseData['refno'] ?? $responseData['ref_code'] ?? 'N/A';
                $token = $responseData['token'];

                // 5. บันทึก Token ลงตาราง
                DB::table('otp_codes')->updateOrInsert(
                    ['phone' => $request->phone],
                    [
                        'code' => $token, 
                        'expires_at' => Carbon::now()->addMinutes(5),
                        'created_at' => now(),
                    ]
                );

                // 6. บันทึก Session
                session(['verify_phone' => $request->phone, 'ref_code' => $refCode]);

                return redirect()->route('verify-otp')->with('success', 'ส่งรหัส OTP แล้ว (Ref: ' . $refCode . ')');

            } else {
                // กรณี ThaiBulkSMS ปฏิเสธ (เช่น เงินหมด, คีย์ผิด)
                $errorMsg = $responseData['error']['description'] ?? 'ระบบ SMS ขัดข้อง';
                return back()->withErrors(['phone' => 'ไม่สามารถส่ง SMS ได้: ' . $errorMsg])->withInput();
            }

        } catch (\Exception $e) {
            return back()->withErrors(['phone' => 'ระบบขัดข้อง ไม่สามารถเชื่อมต่อ SMS ได้: ' . $e->getMessage()])->withInput();
        }
    }

    // ==========================================
    // 3. หน้ากรอกยืนยัน OTP
    // ==========================================
    public function verifyOtpView(Request $request)
    {
        // เช็ค Session ด้วยชื่อ verify_phone
        $phone = session('verify_phone');
        if (!$phone) {
            return redirect()->route('register')->withErrors(['phone' => 'เซสชันหมดอายุ กรุณากรอกเบอร์โทรศัพท์ใหม่']);
        }

        $refCode = session('ref_code');
        return view('frontend.verify-otp', compact('phone', 'refCode'));
    }

    // ==========================================
    // 4. ยืนยัน OTP และ สร้าง User ผ่านระบบ ThaiBulkSMS
    // ==========================================
    public function verifyOtpSubmit(Request $request)
    {
        $phone = session('verify_phone');

        if (!$phone) {
            return redirect()->route('login')->with('error', 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่อีกครั้ง');
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'กรุณากรอกรหัส OTP',
            'otp.size' => 'รหัส OTP ต้องมี 6 หลักพอดี'
        ]);

        // 1. ดึง Token ของ ThaiBulkSMS จาก DB ขึ้นมา
        $otpRecord = DB::table('otp_codes')
            ->where('phone', $phone)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'รหัสอ้างอิงหมดอายุหรือถูกรีเซ็ต กรุณาขอรหัสใหม่']);
        }

        DB::beginTransaction();
        try {
            // 2. ยิง API ยืนยัน OTP ไปที่ระบบ ThaiBulkSMS
            $appKey = env('THAIBULK_APP_KEY', '17828749558276');
            $appSecret = env('THAIBULK_APP_SECRET', '5fc3972aecabf0f433a9174bf885a0d5');
            $apiUrl = 'https://otp.thaibulksms.com/v2/otp/verify';

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($apiUrl, [
                'key' => $appKey,
                'secret' => $appSecret,
                'token' => $otpRecord->code, // ส่ง Token กลับไป
                'pin' => $request->otp       // รหัส 6 หลักที่ลูกค้าพิมพ์
            ]);

            $responseData = $response->json();

            // 3. ตรวจสอบว่า ThaiBulkSMS คอนเฟิร์มว่าถูกต้องไหม
            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {

                // 🌟 สร้าง User จริงๆ "หลังจากที่ OTP ถูกต้องแล้วเท่านั้น"
                $dummyUsername = 'user_' . $phone;
                $dummyName = 'Guest_' . substr($phone, -4);

                $user = User::create([
                    'username' => $dummyUsername,
                    'email' => $dummyUsername . '@temp.com',
                    'phone' => $phone,
                    'password' => Hash::make('password'), // สุ่มรหัสผ่านไปก่อน
                    'role' => 'customer',
                    'is_active' => true, // เปิดใช้งานทันที
                    'phone_verified_at' => now()
                ]);

                DB::table('customer_profiles')->insert([
                    'user_id' => $user->id,
                    'first_name' => $dummyName,
                    'phone' => $phone,
                    'created_at' => now(),
                ]);

                // สร้างกระเป๋า EASE Coins
                DB::table('customer_wallets')->insert([
                    'user_id' => $user->id,
                    'current_points' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // ลบ OTP และ Session
                DB::table('otp_codes')->where('phone', $phone)->delete();
                session()->forget(['verify_phone', 'ref_code']);

                DB::commit();

                // ล็อกอินและพากลับหน้าแรก
                Auth::login($user);
                return redirect()->route('home')->with('success', 'ยืนยันตัวตนและลงทะเบียนสำเร็จ');

            } else {
                DB::rollBack();
                // ถ้ารหัสผิด
                return back()->withErrors(['otp' => 'รหัส OTP ไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาดในการสร้างบัญชี: ' . $e->getMessage());
        }
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

    // โยนผู้ใช้ไปหน้าขออนุญาตของ Google / LINE / Facebook
    public function redirectToProvider($provider)
    {
        // 🌟 บังคับระบุสิทธิ์ public_profile พ่วงกับ email สำหรับ Facebook Business App
        if ($provider === 'facebook') {
            return Socialite::driver('facebook')->scopes(['public_profile', 'email'])->redirect();
        }
        
        return Socialite::driver($provider)->redirect();
    }

    // รับข้อมูลกลับมาจาก Google / LINE / Facebook / WhatsApp
    public function handleProviderCallback($provider)
    {
        try {
            // รับค่า User จาก Provider ผ่าน Socialite
            $socialUser = Socialite::driver($provider)->user();

            // 🌟 แมตช์ชื่อคอลัมน์
            $column = 'google_id';
            if ($provider === 'line') {
                $column = 'line_id';
            } elseif ($provider === 'facebook') {
                $column = 'facebook_id';
            } elseif ($provider === 'whatsapp') {
                $column = 'whatsapp_id';
            }

            // 1. ตรวจสอบว่าเคยสมัครด้วย Social ไอดีนี้ไหม
            $user = User::where($column, $socialUser->getId())->first();

            // 2. ถ้าไม่เคย ให้เช็คว่าอีเมลนี้ซ้ำกับระบบไหม
            if (!$user && $socialUser->getEmail()) {
                $user = User::where('email', $socialUser->getEmail())->first();
                if ($user) {
                    $user->update([$column => $socialUser->getId()]);
                }
            }

            // 3. ถ้าเป็นไอดีใหม่ ให้สมัครสมาชิกให้เลย
            if (!$user) {
                DB::beginTransaction();
                try {
                    $dummyUsername = $provider . '_' . substr($socialUser->getId(), 0, 10);
                    $dummyPhone = 'SC' . rand(10000000, 99999999); 
                    
                    $user = User::create([
                        'username' => $dummyUsername,
                        'email' => $socialUser->getEmail() ?? ($dummyUsername . '@temp.com'),
                        'phone' => $dummyPhone,
                        'password' => Hash::make(Str::random(16)),
                        $column => $socialUser->getId(),
                        'role' => 'customer',
                        'is_active' => true,
                        'phone_verified_at' => now(), 
                    ]);

                    DB::table('customer_profiles')->insert([
                        'user_id' => $user->id,
                        'first_name' => $socialUser->getName() ?? 'Guest',
                        'phone' => $dummyPhone,
                        'created_at' => now(),
                    ]);

                    DB::table('customer_wallets')->insert([
                        'user_id' => $user->id,
                        'current_points' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->route('login')->with('error', 'ระบบขัดข้อง: ' . $e->getMessage());
                }
            }

            // เข้าสู่ระบบ
            Auth::login($user);
            return redirect()->route('home')->with('success', 'เข้าสู่ระบบด้วย ' . ucfirst($provider) . ' สำเร็จ');

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับ ' . ucfirst($provider));
        }
    }

    // 1. ฟังก์ชันสำหรับรับ Webhook จาก Meta
    public function whatsappWebhook(Request $request)
    {
        // ส่วนที่ 1: ยืนยันตัวตนกับ Meta (ตอนตั้งค่า Webhook ครั้งแรก)
        if ($request->isMethod('get')) {
            $verifyToken = 'ease_club_2026'; // รหัสผ่านที่เราจะเอาไปกรอกในเว็บ Meta
            if ($request->input('hub_verify_token') === $verifyToken) {
                return response($request->input('hub_challenge'), 200);
            }
            return response('Invalid token', 403);
        }

        // ส่วนที่ 2: รับข้อความที่ลูกค้าพิมพ์เข้ามา
        $data = $request->all();
        
        // 🌟 เพิ่ม Log บันทึกทุกครั้งที่มีคนยิง Webhook (POST) เข้ามา
        \Illuminate\Support\Facades\Log::info('WhatsApp Webhook Received: ', $data);
        
        if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
            $phone = $message['from']; // เบอร์ที่ส่งมา เช่น 66812345678
            $text = $message['text']['body'] ?? ''; // ข้อความที่พิมพ์มา

            // ถ้าข้อความขึ้นต้นด้วย Login- ให้เก็บลง Cache (อายุ 5 นาที)
            if (str_starts_with(trim($text), 'Login-')) {
                // แปลงเบอร์ 66 เป็น 0
                if (str_starts_with($phone, '66')) {
                    $phone = '0' . substr($phone, 2);
                }
                
                // บันทึกว่า ข้อความรหัสนี้ ผูกกับเบอร์โทรอะไร
                Cache::put('wa_login_' . trim($text), $phone, now()->addMinutes(5));
            }
        }

        return response('OK', 200);
    }

    // 2. ฟังก์ชันสำหรับให้หน้าเว็บ Polling เช็คสถานะ
    public function checkWhatsappLogin(Request $request)
    {
        $loginText = $request->login_text; // เช่น "Login-123456"
        
        // เช็คว่าใน Cache มีเบอร์โทรที่ผูกกับข้อความนี้หรือยัง
        $phone = Cache::get('wa_login_' . $loginText);

        if ($phone) {
            // ล้าง Cache ทิ้งทันทีเพื่อความปลอดภัย
            Cache::forget('wa_login_' . $loginText);

            // ค้นหาหรือสร้างผู้ใช้งาน
            $user = User::where('phone', $phone)->orWhere('whatsapp_id', $phone)->first();

            if (!$user) {
                DB::beginTransaction();
                try {
                    $dummyUsername = 'wa_' . substr($phone, -6);
                    $user = User::create([
                        'username' => $dummyUsername,
                        'email' => $dummyUsername . '@temp.com',
                        'phone' => $phone,
                        'password' => \Hash::make(\Str::random(16)),
                        'whatsapp_id' => $phone,
                        'role' => 'customer',
                        'is_active' => true,
                        'phone_verified_at' => now(), 
                    ]);

                    DB::table('customer_profiles')->insert([
                        'user_id' => $user->id,
                        'first_name' => 'WhatsApp User',
                        'phone' => $phone,
                        'created_at' => now(),
                    ]);

                    DB::table('customer_wallets')->insert([
                        'user_id' => $user->id,
                        'current_points' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'System error']);
                }
            } elseif (empty($user->whatsapp_id)) {
                $user->update(['whatsapp_id' => $phone]);
            }

            // ล็อกอิน
            Auth::login($user);
            $request->session()->regenerate();

            return response()->json(['status' => 'success']);
        }

        // ถ้ายิงมาเช็คแล้วยังไม่มีการสแกน ให้บอกหน้าเว็บว่ารอไปก่อน
        return response()->json(['status' => 'pending']);
    }


}
