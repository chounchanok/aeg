<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\AssetType;
use App\Http\Resources\UserResource; // เพิ่มการ import UserResource
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;

class AuthController extends Controller
{
    use ApiResponseTrait;

    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'first_name' => 'required|string|max:2550',
    //         'last_name' => 'required|string|max:2550',
    //         'email' => 'required|string|email|max:2550|unique:users',
    //         'phone_number' => 'required|string|max:20|unique:users',
    //         'company' => 'nullable|string',
    //         'password' => 'required|string|min:8|confirmed',
    //         'role_id' => 'nullable|integer|exists:roles,role_id',
    //         'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $data = $validator->validated();
    //     $data['user_id'] = Str::uuid();
    //     $data['password'] = Hash::make($request->password);
    //     $data['notification'] = 'on';

    //     // จัดการการอัปโหลดไฟล์
    //     if ($request->hasFile('profile_image')) {
    //         $path = $request->file('profile_image')->store('profile_images', 'public');
    //         $data['profile_image_path'] = $path;
    //     }

    //     $user = User::create($data);

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'message' => 'User registered successfully',
    //         'access_token' => $token,
    //         'token_type' => 'Bearer',
    //         'user' => new UserResource($user) // ใช้ UserResource ในการตอบกลับ
    //     ], 201);
    // }

    public function roles()
    {
        $roles = Role::where('role_id','>=',3)->get();
        return response()->json($roles);
    }

    public function team_members()
    {
        $team_members = User::whereIn('role_id', [3, 4])->get();
        return response()->json($team_members);
    }

    public function asset_type(){
        $asset_type = AssetType::get();
        return response()->json($asset_type);
    }

    public function customer_contacts()
    {
        $customer_contacts = User::get();
        return response()->json($customer_contacts);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request)
    {
        // 1. จัดฟอร์แมตเบอร์โทร (เหมือนฝั่งเว็บ)
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

        // 2. Validate ข้อมูล
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required'
        ]);

        // 3. ตรวจสอบ User ในฐานข้อมูล
        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'เบอร์โทรศัพท์หรือรหัสผ่านไม่ถูกต้อง'
            ], 401);
        }

        // 4. สร้าง Token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'phone' => $user->phone,
                    'role' => $user->role
                ]
            ]
        ]);
    }

    // =========================================================
    // *** 1. FORGOT PASSWORD (ส่งอีเมลพร้อม URL Reset) ***
    // =========================================================

    /**
     * Send a password reset link to the given user.
     * Endpoint: POST /api/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        // 1. ตรวจสอบว่ามีอีเมลนี้อยู่จริงในระบบหรือไม่
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'exists' => 'The provided email address is not registered.'
        ]);

        // 2. ใช้ Password Broker ในการสร้าง Token และส่งลิงก์
        // Laravel จะจัดการ:
        //   a) สร้าง Token (และบันทึกในตาราง password_resets)
        //   b) ส่ง Notification/Mailable ที่มีลิงก์รีเซ็ตไปให้ผู้ใช้
        $status = Password::sendResetLink($request->only('email'));

        // 3. ตอบกลับ API
        // Password::RESET_LINK_SENT คือค่าคงที่ที่บ่งบอกว่าสำเร็จ
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link successfully sent to your email.'
            ], 200);
        }

        // กรณีเกิดข้อผิดพลาดอื่น ๆ (เช่น ไม่สามารถส่งอีเมลได้)
        return response()->json([
            'message' => 'Could not send password reset link. Please try again later.'
        ], 500);
    }

    // =========================================================
    // *** 2. RESET PASSWORD (บันทึกรหัสผ่านใหม่) ***
    // =========================================================

    /**
     * Reset the given user's password.
     * Endpoint: POST /api/reset-password
     */
    public function resetPassword(Request $request)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูล (token, email, password)
        $validator = Validator::make($request->all(), [
            'token' => 'required', // Token ที่ได้รับจากอีเมล
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8', // ต้องมี password_confirmation ด้วย
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. ใช้ Password Broker ในการรีเซ็ตรหัสผ่าน
        // Password::broker()->reset() จะจัดการ:
        //   a) ตรวจสอบความถูกต้องของ Token และวันหมดอายุ (ในตาราง password_resets)
        //   b) ถ้า Token ถูกต้อง จะเรียกใช้ Closure เพื่ออัปเดตรหัสผ่าน
        //   c) ลบ Token ออกจากตาราง password_resets
        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // อัปเดตรหัสผ่านของผู้ใช้
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        // 3. ตอบกลับ API
        if ($response === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been successfully reset.'], 200);
        }

        // กรณี Token หมดอายุ, ไม่ถูกต้อง, หรืออีเมลไม่ตรงกัน
        // $response จะเป็นสตริงข้อความที่แปลแล้ว (เช่น "passwords.token")
        return response()->json(['message' => trans($response)], 400);
    }

    /**
     * Logout user (Revoke the token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::beginTransaction();
        try {
            // 1. สร้าง User (ให้ Active ทันที เพราะผ่าน OTP มาแล้ว)
            $user = User::create([
                'username' => $request->username,
                'email' => $request->username . '@temp.com',
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'is_active' => true, // 🌟 ปรับเป็น true
                'phone_verified_at' => now(), // 🌟 ถือว่า Verify แล้ว
            ]);

            // 2. สร้าง Profile
            DB::table('customer_profiles')->insert([
                'user_id' => $user->id,
                'first_name' => $request->name,
                'phone' => $request->phone,
                'created_at' => now(),
            ]);

            // 3. สร้างกระเป๋าพอยท์ (ถ้ามีระบบ Ease Club)
            DB::table('customer_wallets')->insert([
                'user_id' => $user->id,
                'current_points' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            // 4. สร้าง Token ให้เข้าสู่ระบบได้เลยหลังสมัครเสร็จ
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'phone' => $user->phone
                ]
            ], 'สมัครสมาชิกและเข้าสู่ระบบสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('ไม่สามารถสมัครสมาชิกได้: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 2. ยืนยัน OTP (Verify OTP)
    // ==========================================
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        // 1. ตรวจสอบ OTP ในฐานข้อมูล
        $otp = DB::table('otp_codes')
            ->where('phone', $request->phone)
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return $this->errorResponse('รหัส OTP ไม่ถูกต้องหรือหมดอายุ', 400);
        }

        // 2. ลบรหัส OTP ที่ใช้แล้วทิ้ง
        DB::table('otp_codes')->where('phone', $request->phone)->delete();

        // 3. แจ้งแอปพลิเคชันว่ายืนยันตัวตนผ่านแล้ว ให้ไปหน้ากรอกข้อมูลต่อได้เลย
        return $this->successResponse([
            'phone' => $request->phone,
            'is_verified' => true
        ], 'ยืนยันรหัส OTP สำเร็จ กรุณากรอกข้อมูลเพื่อลงทะเบียน');
    }

    // ==========================================
    // 3. Social Login (Line / Gmail)
    // ==========================================
    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:line,google',
            'provider_id' => 'required|string',
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $column = $request->provider === 'google' ? 'google_id' : 'line_id';

        // ค้นหา User จาก Social ID
        $user = User::where($column, $request->provider_id)->first();

        if (!$user) {
            // ถ้าไม่พบ ให้สร้าง User ใหม่ทันที (Social Login มักถือว่ายืนยันตัวตนแล้ว)
            DB::transaction(function () use ($request, $column, &$user) {
                $user = User::create([
                    'username' => $request->provider . '_' . $request->provider_id,
                    'email' => $request->email ?? ($request->provider_id . '@' . $request->provider . '.com'),
                    'phone' => $request->phone,
                    'password' => Hash::make(Str::random(16)),
                    $column => $request->provider_id,
                    'role' => 'customer',
                    'is_active' => true,
                    'phone_verified_at' => now(),
                ]);

                DB::table('customer_profiles')->insert([
                    'user_id' => $user->id,
                    'first_name' => $request->name,
                    'phone' => $request->phone,
                    'created_at' => now(),
                ]);
            });
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 'เข้าสู่ระบบสำเร็จ');
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10'
        ]);

        // 1. เช็คว่าเบอร์นี้เคยสมัครหรือยัง
        if (DB::table('users')->where('phone', $request->phone)->exists()) {
            return $this->errorResponse('เบอร์โทรศัพท์นี้ถูกใช้งานในระบบแล้ว กรุณาเข้าสู่ระบบ', 400);
        }

        // 2. สร้างรหัส OTP 6 หลัก และ Ref Code
        $otpCode = sprintf("%06d", mt_rand(1, 999999));
        $refCode = \Illuminate\Support\Str::random(4);

        // 3. บันทึกลงตาราง otp_codes
        DB::table('otp_codes')->updateOrInsert(
            ['phone' => $request->phone],
            [
                'code' => $otpCode,
                'expires_at' => \Carbon\Carbon::now()->addMinutes(5),
                'created_at' => now()
            ]
        );

        // 4. แปลงเบอร์โทรให้ขึ้นต้นด้วย 66 (เช่น 0639149666 -> 66639149666)
        $formattedPhone = preg_replace('/^0/', '66', $request->phone);

        // 5. ข้อความที่จะส่ง
        $smsMessage = "รหัส OTP ของคุณคือ {$otpCode} (Ref: {$refCode})";

        // 6. เตรียม Payload ตามที่คุณฝนส่งมา
        // 💡 หมายเหตุ: แนะนำให้ย้ายค่ารหัสผ่านไปไว้ในไฟล์ .env ตอนขึ้น Server จริงเพื่อความปลอดภัยครับ
        $smsPayload = [
            "user" => "orange",
            "pass" => "C3!xoO71VSPG",
            "from" => "watsarod",
            "to"   => $formattedPhone,
            "servid" => "OGE001",
            "text" => $smsMessage
        ];

        try {
            // 🌟 สำคัญ: ต้องถาม "คุณฝน" ว่า URL Endpoint ที่ใช้ยิง API คือ URL อะไร
            // สมมติ URL เช่น 'https://sms-gateway-api.com/send' ให้เอามาใส่แทนตรงนี้ครับ
            $apiUrl = env('SMS_GATEWAY_URL', 'https://api.sms-provider.com/send');

            $response = \Illuminate\Support\Facades\Http::post($apiUrl, $smsPayload);

            // ถ้ายิงผ่าน
            if ($response->successful()) {
                return $this->successResponse([
                    'ref_code' => $refCode,
                    'phone' => $request->phone
                ], 'ส่งรหัส OTP เรียบร้อยแล้ว');
            } else {
                return $this->errorResponse('ไม่สามารถส่ง SMS ได้: ระบบขัดข้อง', 500);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('เกิดข้อผิดพลาดในการส่ง OTP: ' . $e->getMessage(), 500);
        }
    }
}
