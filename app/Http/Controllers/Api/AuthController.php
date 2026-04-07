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

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:2550',
            'last_name' => 'required|string|max:2550',
            'email' => 'required|string|email|max:2550|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'company' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|integer|exists:roles,role_id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Str::uuid();
        $data['password'] = Hash::make($request->password);
        $data['notification'] = 'on';

        // จัดการการอัปโหลดไฟล์
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image_path'] = $path;
        }

        $user = User::create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user) // ใช้ UserResource ในการตอบกลับ
        ], 201);
    }

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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request['email'])->first();

        if (!$user || !Hash::check($request['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user) // ใช้ UserResource ในการตอบกลับ
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
}
