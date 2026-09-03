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
use App\Services\DeviceTokenService;
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

        // 🌟 ถ้า Mobile แนบ FCM token มาด้วยกับการ login ให้บันทึกไว้เลย
        DeviceTokenService::captureFromRequest($request, $user->id);

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'username' => 'required|string|unique:users',
            'phone' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'company' => 'nullable|string',
            'gender' => 'nullable|string',
            'birthday' => 'nullable|date',
            'password' => 'required|string|min:6|confirmed',
            'company_type' => 'nullable|string',
            'service_interesting' => 'nullable|array',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // เพิ่มการตรวจสอบไฟล์รูปภาพ
        ]);

        // จัดการ Array เหมือนตอน Update ปกติ
        if ($request->has('service_interesting') && is_array($request->service_interesting)) {
            $service_interesting = json_encode($request->service_interesting, JSON_UNESCAPED_UNICODE);
        } else {
            $service_interesting = json_encode([], JSON_UNESCAPED_UNICODE); 
        }

        DB::beginTransaction();
        try {
            // 1. สร้าง User (ให้ Active ทันที เพราะผ่าน OTP มาแล้ว)
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'is_active' => true, // 🌟 ปรับเป็น true
                'phone_verified_at' => now(), // 🌟 ถือว่า Verify แล้ว
            ]);

            // 2. สร้าง Profile
            DB::table('customer_profiles')->insert([
                'user_id' => $user->id,
                'first_name' => $request->firstname,
                'last_name' => $request->lastname,
                'phone' => $request->phone,
                'company' => $request->company,
                'gender' => $request->gender,
                'birthday' => $request->birthday,
                'company_type' => $request->company_type,
                'service_interesting' => $service_interesting,
                'update_first' => now(),
                'created_at' => now(),
            ]);

            if($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('profiles', 'public');
                DB::table('customer_profiles')->where('user_id', $user->id)->update([
                    'profile_image_url' => url('storage/' . $path)
                ]);
            }

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

            // 🌟 ถ้า Mobile แนบ FCM token มาด้วยกับการสมัครสมาชิก ให้บันทึกไว้เลย
            DeviceTokenService::captureFromRequest($request, $user->id);

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone
                ]
            ], 'สมัครสมาชิกและเข้าสู่ระบบสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('ไม่สามารถสมัครสมาชิกได้: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 2. ยืนยัน OTP (Verify OTP) สำหรับฝั่ง API
    // ==========================================
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6', // บังคับว่าต้องมี 6 หลัก
        ]);

        // 1. ดึง Token ของ ThaiBulkSMS จากฐานข้อมูล
        $otpRecord = DB::table('otp_codes')
            ->where('phone', $request->phone)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return $this->errorResponse('รหัสอ้างอิงหมดอายุ หรือยังไม่ได้ขอ OTP', 400);
        }

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
                'token' => $otpRecord->code, // ส่ง Token กลับไป (คอลัมน์ code ของเราเก็บค่า Token ไว้)
                'pin' => $request->code      // รหัส 6 หลักที่ลูกค้าพิมพ์จากแอป
            ]);

            $responseData = $response->json();

            // 3. ตรวจสอบว่า ThaiBulkSMS คอนเฟิร์มว่าถูกต้องไหม
            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                
                // ยืนยันสำเร็จ ลบ Token ทิ้ง
                DB::table('otp_codes')->where('phone', $request->phone)->delete();

                // แจ้งแอปพลิเคชันว่ายืนยันตัวตนผ่านแล้ว
                return $this->successResponse([
                    'phone' => $request->phone,
                    'is_verified' => true
                ], 'ยืนยันรหัส OTP สำเร็จ กรุณากรอกข้อมูลเพื่อลงทะเบียน');

            } else {
                // ถ้ารหัสผิด
                return $this->errorResponse('รหัส OTP ไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง', 400);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('ระบบขัดข้อง ไม่สามารถตรวจสอบ OTP ได้: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 3. Social Login (Line / Gmail / Apple / Facebook / WhatsApp)
    // ==========================================
    public function socialLogin(Request $request)
    {
        if ($request->has('provider')) {
            $request->merge([
                'provider' => strtolower($request->provider)
            ]);
        }

        // 1. รับค่าที่ Mobile App ส่งมาให้ (🌟 เพิ่ม facebook และ whatsapp)
        $request->validate([
            'provider' => 'required|in:line,google,apple,facebook,whatsapp',
            'provider_id' => 'required|string', 
            'name' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $provider = $request->provider;
        $providerId = $request->provider_id;
        
        // 🌟 แมตช์ชื่อคอลัมน์ในฐานข้อมูลตาม Provider
        $column = 'google_id';
        if ($provider === 'line') {
            $column = 'line_id';
        } elseif ($provider === 'apple') {
            $column = 'apple_id';
        } elseif ($provider === 'facebook') {
            $column = 'facebook_id';
        } elseif ($provider === 'whatsapp') {
            $column = 'whatsapp_id';
        }

        try {
            // 2. ค้นหาว่าเคยผูกบัญชีด้วย Social ID นี้หรือยัง
            $user = User::where($column, $providerId)->first();

            // 3. ถ้ายังไม่เคยผูก ลองเช็คจาก Email เผื่อลูกค้าเคยสมัครด้วยอีเมลนี้ไว้แล้ว
            if (!$user && $request->email) {
                $user = User::where('email', $request->email)->first();
                
                // ถ้าเจออีเมลตรงกัน ให้ผูก Social ID เข้าไปในบัญชีเก่าเลย
                if ($user) {
                    $user->update([$column => $providerId]);
                }
            }
            
            // logger()->info("Social Login Attempt: Provider={$provider}, ProviderID={$providerId}, UserID=" . ($user ? $user->id : 'null'). "Column: ".$column." ProviderID: ".$providerId);

            // 4. ถ้าเป็นลูกค้าใหม่แกะกล่อง (ไม่มีทั้ง ID และ Email ในระบบ) ให้สมัครสมาชิกใหม่
            if (!$user) {
                DB::beginTransaction();
                
                $dummyUsername = $provider . '_' . substr($providerId, 0, 10);
                
                $user = User::create([
                    'username' => $dummyUsername,
                    'email' => $request->email ?? ($dummyUsername . '@temp.com'),
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                    $column => $providerId,
                    'role' => 'customer',
                    'is_active' => true,
                    'phone_verified_at' => now(), // Social ถือว่ายืนยันตัวตนระดับนึงแล้ว
                ]);

                // สร้าง Profile พื้นฐาน
                DB::table('customer_profiles')->insert([
                    'user_id' => $user->id,
                    'first_name' => $request->name ?? ucfirst($provider) . ' User ' . $user->id,
                    'created_at' => now(),
                ]);

                // สร้างกระเป๋า EASE Coins
                DB::table('customer_wallets')->insert([
                    'user_id' => $user->id,
                    'current_points' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();
            }

            // 5. ออก Token (Sanctum) ให้แอปมือถือเอาไปใช้
            $token = $user->createToken('auth_token')->plainTextToken;

            // 🌟 ถ้า Mobile แนบ FCM token มาด้วยกับการ login ให้บันทึกไว้เลย
            DeviceTokenService::captureFromRequest($request, $user->id);

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 'เข้าสู่ระบบสำเร็จ');

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return $this->errorResponse('เกิดข้อผิดพลาดในการสร้างบัญชี: ' . $e->getMessage(), 500);
        }
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

        // 2. จัดการฟอร์แมตเบอร์โทรเป็นของ ThaiBulkSMS
        // แปลงเบอร์ 0639149666 ให้เป็นแบบที่ไทยบัลค์ต้องการ (บางทีอาจรับแค่เบอร์ 10 หลักปกติ หรือ 66 ให้ยึด 0 นำหน้าไว้ก่อน ถ้าเขาตีกลับค่อยเปลี่ยนครับ)
        $formattedPhone = $request->phone;

        try {
            // 3. ยิง API ขอ OTP จาก ThaiBulkSMS (ใช้ Endpoint สำหรับขอ OTP โดยเฉพาะ)
            // *ข้อมูล App Key และ App Secret เอามาจากในรูปภาพที่แนบมา
            $appKey = env('THAIBULK_APP_KEY', '17828749558276');
            $appSecret = env('THAIBULK_APP_SECRET', '5fc3972aecabf0f433a9174bf885a0d5');

            $apiUrl = 'https://otp.thaibulksms.com/v2/otp/request'; // Endpoint มาตรฐานของบริการ OTP ThaiBulk

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($apiUrl, [
                'key' => $appKey,
                'secret' => $appSecret,
                'msisdn' => $formattedPhone
            ]);

            $responseData = $response->json();

            // 🌟 เพิ่มบรรทัดนี้เพื่อเก็บ Log ไว้ดูว่า ThaiBulkSMS ส่งโครงสร้างหน้าตาแบบไหนมาให้เรา
            \Illuminate\Support\Facades\Log::info('ThaiBulkSMS Request OTP:', $responseData);

            // 4. เช็คผลลัพธ์จาก ThaiBulkSMS
            if ($response->successful() && isset($responseData['token'])) {

                // 🌟 แก้ไขบรรทัดนี้: ใช้ ?? ดักไว้ ถ้าไม่มี refno ให้หา ref_code ถ้าไม่มีอีกให้ใช้คำว่า 'N/A'
                $refCode = $responseData['refno'] ?? $responseData['ref_code'] ?? 'N/A'; 
                $token = $responseData['token']; 
                
                // 5. บันทึกลงตาราง otp_codes
                DB::table('otp_codes')->updateOrInsert(
                    ['phone' => $request->phone],
                    [
                        'code' => $token,
                        'expires_at' => \Carbon\Carbon::now()->addMinutes(5),
                        'created_at' => now()
                    ]
                );

                return $this->successResponse([
                    'ref_code' => $refCode, // ส่งกลับไปให้แอปโชว์ N/A หรือรหัสจริง
                    'phone' => $request->phone
                ], 'ส่งรหัส OTP เรียบร้อยแล้วผ่านระบบ EASE CLUB');

            } else {
                // กรณีระบบ ThaiBulkSMS ปฏิเสธการส่ง
                $errorMsg = $responseData['error']['description'] ?? 'ระบบ SMS ขัดข้อง';
                return $this->errorResponse('ไม่สามารถส่ง SMS ได้: ' . $errorMsg, 500);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('เกิดข้อผิดพลาดในการเชื่อมต่อระบบ SMS: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // ลบบัญชีผู้ใช้งาน (Delete Account) สำหรับ Mobile App
    // ==========================================
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ไม่พบผู้ใช้งาน หรือเซสชันหมดอายุ', 401);
        }

        DB::beginTransaction();
        try {
            // 1. เพิกถอน Token ทั้งหมด (บังคับล็อกเอาท์จากทุกอุปกรณ์)
            $user->tokens()->delete();

            // 2. ลบข้อมูลที่ผูกกับ User คนนี้ (ป้องกันขยะค้างในระบบ กรณีไม่ได้ทำ Cascade ไว้ใน DB)
            DB::table('customer_profiles')->where('user_id', $user->id)->delete();
            DB::table('customer_wallets')->where('user_id', $user->id)->delete();
            
            // 💡 ถ้าพี่แชมเปญมีตารางอื่นๆ เช่น ประวัติการแจ้งซ่อม ก็สามารถสั่งลบ หรือจะเก็บไว้เป็นประวัติ (ไม่ลบ) ก็ได้ครับ
            // DB::table('service_requests')->where('customer_id', $user->id)->delete();

            // 3. ลบตัวบัญชี User หลัก
            $user->delete();

            DB::commit();

            return $this->successResponse(null, 'ลบบัญชีผู้ใช้งานเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาดในการลบบัญชี: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // ตรวจสอบสถานะการล็อกอิน WhatsApp (สำหรับ Mobile App)
    // ==========================================
    public function checkWhatsappLoginApi(Request $request)
    {
        $request->validate([
            'login_text' => 'required|string' // เช่น "Login-123456"
        ]);

        $loginText = trim($request->login_text);
        
        // 1. เช็คว่า Webhook ได้รับข้อความและบันทึกเบอร์ลง Cache หรือยัง
        $phone = \Illuminate\Support\Facades\Cache::get('wa_login_' . $loginText);

        // ถ้าเจอเบอร์โทร แสดงว่าลูกค้ากด "ส่ง" ในแอป WhatsApp แล้ว
        if ($phone) {
            // ล้าง Cache ทิ้งทันทีเพื่อป้องกันการใช้ซ้ำ
            \Illuminate\Support\Facades\Cache::forget('wa_login_' . $loginText);

            // 2. ค้นหาผู้ใช้งานจากเบอร์โทร (หรือสร้างใหม่ถ้ายังไม่เคยมี)
            $user = User::where('phone', $phone)->orWhere('whatsapp_id', $phone)->first();

            if (!$user) {
                DB::beginTransaction();
                try {
                    $dummyUsername = 'wa_' . substr($phone, -6);
                    $user = User::create([
                        'username' => $dummyUsername,
                        'email' => $dummyUsername . '@temp.com',
                        'phone' => $phone,
                        'password' => Hash::make(\Illuminate\Support\Str::random(16)),
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
                    return $this->errorResponse('ไม่สามารถสร้างบัญชีผู้ใช้งานได้: ' . $e->getMessage(), 500);
                }
            } elseif (empty($user->whatsapp_id)) {
                $user->update(['whatsapp_id' => $phone]);
            }

            // 3. สร้าง Token ให้แอปมือถือเอาไปใช้
            $token = $user->createToken('auth_token')->plainTextToken;

            // 🌟 ถ้า Mobile แนบ FCM token มาด้วยกับการ login ให้บันทึกไว้เลย
            DeviceTokenService::captureFromRequest($request, $user->id);

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'phone' => $user->phone
                ]
            ], 'เข้าสู่ระบบผ่าน WhatsApp สำเร็จ');
        }

        // 4. ถ้ายังไม่มีข้อมูลใน Cache (ลูกค้ายังไม่กดส่ง) ให้บอกแอปว่ารอก่อน
        return response()->json([
            'status' => 'pending', 
            'message' => 'Waiting for user to send the message'
        ]);
    }
}
