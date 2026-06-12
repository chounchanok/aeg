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
        $userId = $request->user()->id;

        // 1. ดึงข้อมูลจาก customer_products โดยตรง (เอา Join ออก)
        $rawPackages = DB::table('customer_products')
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. ดึงประวัติการเรียกช่างทั้งหมดของลูกค้านี้มาเตรียมไว้
        $serviceRequests = DB::table('service_requests')
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('customer_product_id');

        $activePackages = [];
        $historyPackages = [];

        foreach ($rawPackages as $pkg) {
            // คำนวณวันหมดอายุและระยะเวลาคงเหลือ
            $expireDate = \Carbon\Carbon::parse($pkg->warranty_expire_date);
            $now = \Carbon\Carbon::now();
            $isExpired = $expireDate->isPast();

            $remainingText = 'หมดอายุแล้ว';
            if (!$isExpired) {
                $diff = $now->diff($expireDate);
                $remainingMonths = $diff->m + ($diff->y * 12);
                $remainingDays = $diff->d;

                if ($remainingMonths > 0) {
                    $remainingText = "เหลือเวลา $remainingMonths เดือน $remainingDays วัน";
                } else {
                    $remainingText = "เหลือเวลา $remainingDays วัน";
                }
            }

            // คำนวณโควต้าจำนวนครั้งคงเหลือ (ป้องกันค่า null)
            $totalCount = $pkg->total_service_count ?? 0;
            $usedCount = $pkg->used_service_count ?? 0;
            $remainingServices = max(0, $totalCount - $usedCount);

            // ดึงประวัติการเรียกช่างของแพ็กเกจนี้มาเรียงลำดับ
            $requests = isset($serviceRequests[$pkg->id]) ? $serviceRequests[$pkg->id] : collect([]);
            $history = $requests->map(function($req, $index) use ($requests) {
                $count = $requests->count() - $index;
                return [
                    'id' => $req->id,
                    'ticket_number' => $req->ticket_number,
                    'title' => "บริการครั้งที่ $count",
                    'problem_description' => $req->problem_description,
                    'status' => $req->status,
                    'preferred_date' => $req->preferred_date,
                    'created_at' => $req->created_at
                ];
            })->toArray();

            // ประกอบร่างข้อมูล
            $formattedPackage = [
                'id' => $pkg->id,
                'product_name' => $pkg->product_name ?? 'ไม่ระบุชื่อ', // ดึงชื่อจากตารางเดิมเลย
                'serial_number' => $pkg->serial_number ?? '-',
                'image_url' => $pkg->image_url ?? null, // ถ้าไม่มีรูปก็จะส่ง null ให้แอปโชว์รูป Default แทน
                // 'warranty_start_date' => $pkg->created_at->format('Y-m-d'),
                'warranty_expire_date' => $expireDate->format('Y-m-d'),
                'remaining_text' => $remainingText,
                'total_service_count' => $totalCount,
                'used_service_count' => $usedCount,
                'remaining_services' => $remainingServices,
                'service_history' => $history
            ];

            if (($pkg->status ?? 'active') === 'active' && !$isExpired) {
                $activePackages[] = $formattedPackage;
            } else {
                $historyPackages[] = $formattedPackage;
            }
        }

        return $this->successResponse([
            'active' => $activePackages,
            'history' => $historyPackages
        ], 'My packages retrieved successfully');
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
        $userId = $request->user()->id;
        $type = $request->query('type'); // รับค่าจาก App (เช่น ?type=promotion)

        $query = DB::table('notifications')->where('user_id', $userId)->orderBy('created_at', 'desc');

        // ถ้ามีการส่ง type มา และไม่ใช่คำว่า "ทั้งหมด" (all) ให้ filter
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        $notifications = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function readNotification(Request $request, $id)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['is_read' => true, 'updated_at' => now()]);

        return $this->successResponse(null, 'Notification marked as read');
    }

    // ดึงประวัติแต้มเข้า-ออก
    public function getPointHistory(Request $request)
    {
        $userId = $request->user()->id;

        $history = DB::table('point_transactions')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'amount' => $tx->amount, // ถ้าเป็นลบคือใช้แต้ม, ถ้าเป็นบวกคือได้รับแต้ม
                    'type' => $tx->type, // เช่น 'redeem', 'earn'
                    'description' => $tx->description,
                    'created_at' => \Carbon\Carbon::parse($tx->created_at)->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Point history retrieved successfully',
            'data' => $history
        ]);
    }
}
