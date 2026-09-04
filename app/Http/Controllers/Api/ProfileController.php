<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponseTrait;
use App\Models\User;

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

        // 🌟 คลายล็อก String จาก Database ให้กลับเป็น Array ก่อนส่งกลับ
        if ($profile && !empty($profile->service_interesting)) {
            // ถอดรหัส JSON ถ้าพังให้คืนค่าเป็น Array ว่าง []
            $profile->service_interesting = json_decode($profile->service_interesting, true) ?? [];
        }

        if(!empty($user->google_id)){
            $provider = 'google';
        }else if(!empty($user->line_id)){
            $provider = 'line';
        }else if(!empty($user->apple_id)){
            $provider = 'apple';
        }else if(!empty($user->whatsapp_id)){
            $provider = 'whatsapp';
        }else if(!empty($user->facebook_id)){
            $provider = 'facebook';
        }else{
            $provider = 'email';
        }

        return $this->successResponse([
            'user' => $user,
            'profile' => $profile,
            'provider' => $provider,
            'question_company_type' => ['ร้านทอง', 'ร้านเพชรพลอยและอัญมณี', 'โรงรับจำนำ', 'โรงงาน/คลังสินค้า', 'สำนักงาน', 'อาคารและสิ่งปลูกสร้าง', 'บ้าน', 'อื่นๆ'],
            'question_service_interesting' => ['ระบบสัญญาณกันขโมย', 'ระบบควบคุมการเข้า-ออก', 'ระบบสัญญาณเตือนอัคคีภัย', 'ระบบกล้องวงจรปิด', 'ประกันภัยอัญมณี ทองและทรัพย์สินมูลค่าสูง', 'ประกันวินาศภัยสิ่งปลูกสร้าง', 'ประกันวินาศภัยเพื่อการขนส่งสินค้ามูลค่าสูง', 'AEG Gold Cap-Lock', 'ตู้นิรภัยให้เช่า', 'ขนส่งสินค้ามูลค่าสูง']
        ], 'Profile retrieved');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'required|string|email',
            'phone' => 'nullable|string',
            'gender' => 'nullable|string',
            'birthday' => 'nullable|date',
            'other' => 'nullable|string',
            'company' => 'nullable|string',
            'company_type' => 'nullable|string',
            'service_interesting' => 'nullable|array',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' 
        ]);

        // แยก service_interesting ออกมาก่อน เพราะเราต้องจัดฟอร์แมตมันใหม่
        $updateData = $request->only(['first_name', 'last_name', 'phone', 'gender', 'birthday', 'other', 'company', 'company_type']);

        // 🌟 ดักจับ Array และบังคับเข้ารหัส JSON แบบอ่านภาษาไทยออก ก่อนเซฟลง Database
        if ($request->has('service_interesting')) {
            $updateData['service_interesting'] = json_encode($request->service_interesting, JSON_UNESCAPED_UNICODE);
        }

        // จัดการอัปโหลดไฟล์รูปภาพ (ถ้ามีการส่งมา)
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $updateData['profile_image_url'] = url('storage/' . $path);
        }

        $updateData['update_first'] = now();
        $updateData['updated_at'] = now();

        DB::table('customer_profiles')
            ->updateOrInsert(
                ['user_id' => $user->id],
                $updateData
            );

        User::where('id', $user->id)->update(['email' => $request->email]);

        $updatedProfile = DB::table('customer_profiles')->where('user_id', $user->id)->first();

        // 🌟 คลายล็อกกลับเป็น Array สวยๆ ก่อนส่ง Response ไปแสดงผลที่หน้าแอป
        if ($updatedProfile && !empty($updatedProfile->service_interesting)) {
            $updatedProfile->service_interesting = json_decode($updatedProfile->service_interesting, true) ?? [];
        }

        return $this->successResponse($updatedProfile, 'Profile updated successfully');
    }

    // ==========================================
    // 2. สินค้าและบริการของฉัน (My Packages)
    // ==========================================
    public function getMyPackages(Request $request)
    {
        $userId = $request->user()->id;

        // 1. ดึงข้อมูลจาก customer_products
        $rawPackages = DB::table('customer_products')
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // ดึงข้อมูล Master มาเตรียมไว้
        $productIds = $rawPackages->where('reference_type', 'product')->pluck('reference_id')->filter()->toArray();
        $insuranceIds = $rawPackages->where('reference_type', 'insurance')->pluck('reference_id')->filter()->toArray();
        $lockerIds = $rawPackages->where('reference_type', 'locker')->pluck('reference_id')->filter()->toArray();

        $masterProducts = DB::table('products')->whereIn('id', $productIds)->get()->keyBy('id');
        $masterInsurances = DB::table('insurances')->whereIn('id', $insuranceIds)->get()->keyBy('id');
        $masterSmartLockers = DB::table('smart_lockers')->whereIn('id', $lockerIds)->get()->keyBy('id');

        // 2. ดึงประวัติการเรียกช่าง
        $serviceRequests = DB::table('service_requests')
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('customer_product_id');

        // 🌟 3. ดึงประวัติการรีวิวทั้งหมดของลูกค้าคนนี้ (เอามาทั้งก้อนเลย จะได้ส่งให้แอปโชว์ดาวได้)
        $reviews = DB::table('package_reviews')
            ->where('user_id', $userId)
            ->get()
            ->keyBy('order_item_id'); // จัดกลุ่มด้วย ID ของรายการนั้นๆ เพื่อให้หาง่ายขึ้น

        $activePackages = [];
        $historyPackages = [];

        foreach ($rawPackages as $pkg) {
            $expireDate = \Carbon\Carbon::parse($pkg->warranty_expire_date);
            $now = \Carbon\Carbon::now();
            $isExpired = $expireDate->isPast();
            $startDate = \Carbon\Carbon::parse($pkg->purchase_date);

            // คำนวณระยะเวลาคงเหลือ
            $remainingText = 'หมดอายุแล้ว';
            if (!$isExpired) {
                $diff = $now->diff($expireDate);
                $remainingMonths = $diff->m + ($diff->y * 12);
                $remainingDays = $diff->d;
                $remainingText = $remainingMonths > 0 ? "เหลือเวลา $remainingMonths เดือน $remainingDays วัน" : "เหลือเวลา $remainingDays วัน";
            }

            $totalCount = $pkg->total_service_count ?? 0;
            $usedCount = $pkg->used_service_count ?? 0;
            $remainingServices = max(0, $totalCount - $usedCount);

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

            // 🌟 แมตช์ข้อมูล Detail และ DetailPrice
            $productDetail = '';
            $productDetailPrice = '';
            if ($pkg->reference_type === 'product' && isset($masterProducts[$pkg->reference_id])) {
                $master = $masterProducts[$pkg->reference_id];
                $productDetail = $master->description_th;
                $productDetailPrice = $master->description_en;
            } elseif ($pkg->reference_type === 'insurance' && isset($masterInsurances[$pkg->reference_id])) {
                $master = $masterInsurances[$pkg->reference_id];
                $productDetail = $master->description_th;
                $productDetailPrice = $master->insurance_coverage;
            } elseif ($pkg->reference_type === 'locker' && isset($masterSmartLockers[$pkg->reference_id])) {
                $master = $masterSmartLockers[$pkg->reference_id];
                $productDetail = $master->description_th;
                $productDetailPrice = $master->description_en;
            }

            // 🌟 [ข้อ 1] คำนวณสถานะการใช้งาน (Usage Status)
            $usageStatus = 'ปกติ';
            if ($pkg->reference_type === 'product') {
                $hasActiveRepair = $requests->whereNotIn('status', ['completed', 'cancelled', 'done'])->count() > 0;
                $usageStatus = $hasActiveRepair ? 'แจ้งซ่อม' : 'ใช้งานปกติ';
            } else {
                $usageStatus = $isExpired ? 'หมดอายุ' : 'ปกติ';
            }

            $setting = DB::table('general_setting')->where('id', 1)->first();
            // 🌟 [ข้อ 2 & 3] ตรวจสอบสถานะการรีวิว
            $reviewData = isset($reviews[$pkg->id]) ? $reviews[$pkg->id] : null;
            $isReviewed = $reviewData ? true : false;
            $reviewStatusText = $isReviewed ? 'รีวิวเรียบร้อยแล้ว' : 'ยังไม่ได้รีวิว';
            $earnedCoins = $setting->review_point;

            // 🌟 [ข้อ 4] วันเริ่มต้นการดูแล
            $careStartDate = null;
            if ($pkg->reference_type === 'product') {
                $careStartDate = $startDate->format('Y-m-d');
            }

            // ประกอบร่างข้อมูลส่งกลับ
            $formattedPackage = [
                'id' => $pkg->id,
                'reference_type' => $pkg->reference_type ?? 'product',
                'product_name' => $pkg->product_name ?? 'ไม่ระบุชื่อ',
                'product_detail' => $productDetail,
                'product_detailprice' => $productDetailPrice,
                'serial_number' => $pkg->serial_number ?? '-',
                'image_url' => $pkg->image_url ?? null,

                // --- 🌟 ข้อมูลที่เพิ่มเข้ามาใหม่ ---
                'usage_status' => $usageStatus,
                'care_start_date' => $careStartDate,
                'is_reviewed' => $isReviewed,
                'review_status_text' => $reviewStatusText,
                'earned_coins' => $earnedCoins,
                'review_info' => $reviewData, // 🌟 แนบข้อมูลการให้คะแนนและคอมเมนต์กลับไปให้แอปด้วย
                // -----------------------------

                'warranty_start_date' => $startDate->format('Y-m-d'),
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
        $favorites_rewards = DB::table('favorites')
            ->join('rewards', 'favorites.product_id', '=', 'rewards.id')
            ->where('favorites.user_id', $request->user()->id)
            ->where('favorites.item_type', 'reward')
            ->select('rewards.*', 'favorites.created_at as favorited_at')
            ->get();

        $favotites_products = DB::table('favorites')
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->where('favorites.user_id', $request->user()->id)
            ->where('favorites.item_type', 'product')
            ->select('products.*', 'favorites.created_at as favorited_at')
            ->get();

        return $this->successResponse([
            'rewards' => $favorites_rewards,
            'products' => $favotites_products
        ], 'Favorites retrieved');
    }

    public function toggleFavorite(Request $request)
    {
        $request->validate(['product_id' => 'required|integer',
            'item_type' => 'nullable|string|in:product,reward']); // เพิ่มการตรวจสอบ item_type

        $userId = $request->user()->id;
        $productId = $request->product_id;
        $itemType = $request->item_type ?? 'product'; // กำหนดค่า default เป็น 'product' ถ้าไม่ได้ส่งมา

        // เช็คว่าเคยกดหัวใจไว้หรือยัง
        $existing = DB::table('favorites')
            ->where('user_id', $userId)
            ->where('item_type', $itemType)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            // ถ้ามีแล้วให้ ลบออก (Unfavorite)
            DB::table('favorites')->where('id', $existing->id)->delete();
            return $this->successResponse(['is_favorited' => false], 'Removed from favorites');
        } else {
            // ถ้ายังไม่มีให้ เพิ่ม (Favorite)
            DB::table('favorites')->insert([
                'item_type' => $request->item_type ?? 'product', // กำหนดค่า default เป็น 'product' ถ้าไม่ได้ส่งมา
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
