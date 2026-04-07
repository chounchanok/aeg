<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponseTrait;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    // ==========================================
    // 1. จัดการ Profile (ดูข้อมูล & อัปเดต)
    // ==========================================
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $profile = DB::table('customer_profiles')->where('user_id', $user->id)->first();
        
        return $this->successResponse([
            'user' => $user,
            'profile' => $profile
        ], 'Profile retrieved');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // ตรวจสอบไฟล์รูปภาพ
        ]);

        $updateData = $request->only(['first_name', 'last_name', 'phone']);

        // จัดการอัปโหลดไฟล์รูปภาพ (ถ้ามีการส่งมา)
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $updateData['profile_image_url'] = url('storage/' . $path);
        }

        $updateData['updated_at'] = now();

        DB::table('customer_profiles')
            ->updateOrInsert(
                ['user_id' => $user->id],
                $updateData
            );

        $updatedProfile = DB::table('customer_profiles')->where('user_id', $user->id)->first();

        return $this->successResponse($updatedProfile, 'Profile updated successfully');
    }

    // ==========================================
    // 2. สินค้าและบริการของฉัน (My Packages)
    // ==========================================
    public function getMyPackages(Request $request)
    {
        // อ้างอิงจากตาราง customer_products ที่สร้างไว้ในเฟส 1
        $packages = DB::table('customer_products')
            ->where('customer_id', $request->user()->id)
            ->get();

        return $this->successResponse($packages, 'My packages retrieved');
    }

    // ==========================================
    // 3. รายการโปรด (Favorites)
    // ==========================================
    public function getFavorites(Request $request)
    {
        $favorites = DB::table('favorites')
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->where('favorites.user_id', $request->user()->id)
            ->select('products.*', 'favorites.created_at as favorited_at')
            ->get();

        return $this->successResponse($favorites, 'Favorites retrieved');
    }

    public function toggleFavorite(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        
        $userId = $request->user()->id;
        $productId = $request->product_id;

        // เช็คว่าเคยกดหัวใจไว้หรือยัง
        $existing = DB::table('favorites')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            // ถ้ามีแล้วให้ ลบออก (Unfavorite)
            DB::table('favorites')->where('id', $existing->id)->delete();
            return $this->successResponse(['is_favorited' => false], 'Removed from favorites');
        } else {
            // ถ้ายังไม่มีให้ เพิ่ม (Favorite)
            DB::table('favorites')->insert([
                'user_id' => $userId,
                'product_id' => $productId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return $this->successResponse(['is_favorited' => true], 'Added to favorites');
        }
    }

    // ==========================================
    // 4. Notifications & Device Token
    // ==========================================
    public function saveDeviceToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|in:ios,android,web'
        ]);

        DB::table('device_tokens')->updateOrInsert(
            ['token' => $request->token], // เช็คด้วย token ว่ามีในระบบหรือยัง
            [
                'user_id' => $request->user()->id,
                'device_type' => $request->device_type,
                'updated_at' => now()
            ]
        );

        return $this->successResponse(null, 'Device token saved successfully');
    }

    public function getNotifications(Request $request)
    {
        $notifications = DB::table('notifications')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($notifications, 'Notifications retrieved');
    }

    public function readNotification(Request $request, $id)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['is_read' => true, 'updated_at' => now()]);

        return $this->successResponse(null, 'Notification marked as read');
    }
}