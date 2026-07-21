<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\MainPageController;
use App\Http\Controllers\Api\EaseClubController;
use App\Http\Controllers\Api\EcommerceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\ServiceCategoryApiController;
use App\Http\Controllers\Api\SmartLockerController;
use App\Http\Controllers\Frontend\SupportChatController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\BblPaymentController;

// --- Smart Lockers (ตู้เซฟนิรภัยให้เช่า) ---
// ฝั่งที่ต้องล็อกอิน (จองตู้เซฟ)

Route::get('/smart-lockers/categorys', [SmartLockerController::class, 'getSmartLockers']);
Route::get('/smart-lockers', [SmartLockerController::class, 'index']);
Route::get('/smart-lockers/{id}', [SmartLockerController::class, 'show']);

Route::middleware('auth:sanctum')->prefix('smart-lockers')->group(function () {
    Route::post('/calculate', [SmartLockerController::class, 'calculatePrice']); // 🌟 เส้นคำนวณราคา
    Route::post('/book', [SmartLockerController::class, 'book']);                // เส้นสร้างใบจอง
    Route::post('/cancel/{id}', [SmartLockerController::class, 'cancelBooking']); // 🌟 เส้นยกเลิกจอง
});

// --- FAQ (ไม่ต้องล็อกอินก็ดูได้) ---
Route::get('/faqs', [SupportController::class, 'getFaqs']);
// BBL Webhook
Route::post('/ecommerce/payment/bbl-webhook', [BblPaymentController::class, 'webhook']);

// --- Public Routes ---
// 1. เช็คเบอร์ว่ามีในระบบไหม ถ้าไม่มีให้ส่ง OTP ไปเบอร์นั้น
Route::post('/request-otp', [AuthController::class, 'requestOtp']);

// 2. ยืนยัน OTP (มีอยู่แล้ว)
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// 3. กรอกข้อมูลส่วนตัวต่อให้จบ (มีอยู่แล้ว)
Route::post('/register', [AuthController::class, 'register']);

// API สำหรับล็อกอินและสมัครสมาชิก
Route::post('/auth/social-login', [AuthController::class, 'socialLogin']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/social-login', [AuthController::class, 'socialLogin']);
Route::get('/service-categories', [ServiceCategoryApiController::class, 'index']);

// ระบบค้นหา Global Search
Route::get('/search', [EcommerceController::class, 'globalSearch']);

Route::post('/login', [AuthController::class, 'login']);

// --- E-Commerce Public Routes ---
Route::prefix('ecommerce')->group(function () {
    Route::get('/products', [EcommerceController::class, 'getProducts']);
    Route::get('/products/{id}', [EcommerceController::class, 'getProductDetail']);

    // 🌟 เพิ่ม API เส้นใหม่สำหรับแพ็กเกจดูแลอุปกรณ์ (Type = 5)
    Route::get('/packages', [EcommerceController::class, 'getPackages']);

    Route::post('/payment/webhook', [EcommerceController::class, 'paymentWebhook']);
});

// --- E-Commerce Protected Routes (ต้องล็อกอิน) ---
Route::middleware('auth:sanctum')->prefix('ecommerce')->group(function () {
    // --- ระบบตะกร้าสินค้า (เพิ่มเส้น Count) ---
    Route::get('/cart', [EcommerceController::class, 'getCart']);
    Route::get('/cart/count', [EcommerceController::class, 'getCartCount']); // 🌟 เพิ่มเส้นนี้สำหรับนับจำนวนตะกร้า
    Route::post('/cart/add', [EcommerceController::class, 'addToCart']);
    Route::delete('/cart/remove/{cartItemId}', [EcommerceController::class, 'removeFromCart']);
    Route::post('/cart/clear', [EcommerceController::class, 'clearCart']);

    // --- ให้คะแนนบริการและรีวิวแพ็กเกจ ---
    Route::post('/items/{itemId}/review', [EcommerceController::class, 'submitReview']); // 🌟 เพิ่มเส้นนี้สำหรับส่งรีวิว

    // ระบบที่อยู่
    Route::get('/addresses', [EcommerceController::class, 'getAddresses']);
    Route::post('/addresses', [EcommerceController::class, 'createAddress']);

    // 🌟 เพิ่ม 2 เส้นนี้สำหรับการแสดงข้อมูลและการแก้ไข
    Route::get('/addresses/{id}', [EcommerceController::class, 'getAddressDetail']);
    Route::post('/addresses/{id}/update', [EcommerceController::class, 'updateAddress']);
    Route::get('/orders/{id}', [EcommerceController::class, 'getOrderDetail']);

    // ระบบ Checkout และ Payment
    Route::post('/checkout', [EcommerceController::class, 'checkout']);
    Route::post('/buy-now', [EcommerceController::class, 'buyNow']); // 🌟 เพิ่มตรงนี้
    Route::get('/orders', [EcommerceController::class, 'getMyOrders']);

    // ระบบยืนยันการชำระเงินสำเร็จ
    Route::post('/payment-success', [EcommerceController::class, 'paymentSuccess']);

    // ระบบดึงออเดอร์ที่ค้างชำระ และขอชำระเงินใหม่
    Route::get('/orders/pending', [EcommerceController::class, 'getPendingPayments']);
    Route::post('/orders/{id}/retry-payment', [EcommerceController::class, 'retryPayment']);
});

// --- Public Routes (ไม่ต้องล็อกอิน) ---
Route::prefix('main')->group(function () {
    Route::get('/banners', [MainPageController::class, 'getBanners']);
    Route::get('/recommended-services', [MainPageController::class, 'getRecommendedServices']);
});

Route::prefix('ease-club')->group(function () {
        //No Auth
        Route::get('/overview', [EaseClubController::class, 'getOverview']); // หมวดหมู่ + พิเศษสำหรับ Advance
        Route::get('/categories/{categoryId}/rewards', [EaseClubController::class, 'getRewardsByCategory']); // รายการสินค้าตามหมวด
        Route::get('/rewards/{rewardId}', [EaseClubController::class, 'getRewardDetail']); // รายละเอียดของรางวัล
    });

// --- Protected Routes (ต้องล็อกอิน) ---
Route::middleware('auth:sanctum')->group(function () {

    // --- ระบบ Reward Codes ---
    Route::post('/rewards/redeem', [EcommerceController::class, 'redeemReward']);
    Route::get('/rewards/my-codes', [EcommerceController::class, 'getMyRewardCodes']);

    // Main Page (ส่วนบุคคล)
    Route::prefix('main')->group(function () {
        Route::get('/expiring-services', [MainPageController::class, 'getExpiringServices']);
        Route::get('/recommended-privileges', [MainPageController::class, 'getRecommendedPrivileges']);
    });

    // EASE CLUB Rewards
    Route::prefix('ease-club')->group(function () {
        Route::get('/user-info', [EaseClubController::class, 'getUserInfo']); // ข้อมูล User + คะแนน
        Route::get('/banners', [EaseClubController::class, 'getBanners']); // Banner หน้า EASE CLUB
        Route::get('/banners/category', [EaseClubController::class, 'getBannersCategory']); // Banner หน้า EASE CLUB / Category
        Route::post('/rewards/{rewardId}/redeem', [EaseClubController::class, 'redeemReward']); // ยืนยันการแลกพอยท์
    });

    // --- Support Chats (แชทติดต่อสอบถามทั่วไป สำหรับ Mobile App) ---
    Route::prefix('support-chats')->group(function () {
        // ดึงประวัติแชททั้งหมด (แยกตาม topic ได้ เช่น ?topic=general)
        Route::get('/history', [SupportChatController::class, 'getHistory']);

        // ส่งข้อความหาแอดมิน
        Route::post('/send', [SupportChatController::class, 'sendMessage']);
    });

    // 🌟 API สำหรับลบบัญชีตัวเอง
    Route::delete('/auth/delete-account', [AuthController::class, 'deleteAccount']);
});

// --- Profile & User Activity (ต้องล็อกอิน) ---
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    // ข้อมูลส่วนตัว
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);

    // แพ็กเกจและบริการของฉัน (My Packages / Devices)
    Route::get('/my-packages', [ProfileController::class, 'getMyPackages']);

    // รายการโปรด (Favorites)
    Route::get('/favorites', [ProfileController::class, 'getFavorites']);
    Route::post('/favorites/toggle', [ProfileController::class, 'toggleFavorite']);

    // การแจ้งเตือนและ Device Token
    Route::post('/device-token', [ProfileController::class, 'saveDeviceToken']);
    Route::get('/notifications', [ProfileController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [ProfileController::class, 'readNotification']);

    // --- ติดต่อเรา (Contact Admin Email) ---
    Route::post('/contact-admin', [SupportController::class, 'submitContactAdmin']);

    // --- ประวัติแต้ม EASE CLUB (เข้า-ออก) ---
    Route::get('/point-history', [ProfileController::class, 'getPointHistory']);

});

// --- Service Request (ระบบแจ้งซ่อม) ---
Route::middleware('auth:sanctum')->prefix('service-requests')->group(function () {
    Route::get('/', [ServiceRequestController::class, 'getMyRequests']); // ดูประวัติการแจ้งซ่อม
    Route::post('/', [ServiceRequestController::class, 'createRequest']); // สร้างใบแจ้งซ่อมใหม่
    Route::get('/{id}', [ServiceRequestController::class, 'getRequestDetail']); // ดูรายละเอียดใบแจ้งซ่อม
});

// --- Support, Chat & Tracking (ต้องล็อกอิน) ---
Route::middleware('auth:sanctum')->prefix('service-requests')->group(function () {
    // ระบบแชทในใบแจ้งซ่อม
    Route::get('/{id}/chats', [SupportController::class, 'getChats']);
    Route::post('/{id}/chats', [SupportController::class, 'sendMessage']);

    // ติดตามสถานะงานซ่อม
    Route::get('/{id}/tracking', [SupportController::class, 'getTrackingLogs']);
});

// --- ประกันภัย (Insurance) ---
Route::prefix('insurances')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\InsuranceApiController::class, 'index']); // รายการประกัน
    Route::get('/{id}', [\App\Http\Controllers\Api\InsuranceApiController::class, 'show']); // รายละเอียดประกัน
});

// ==========================================
// --- Technician Routes (สำหรับช่างซ่อม) ---
// ==========================================
Route::middleware(['auth:sanctum'])->prefix('technician')->group(function () {

    // 1. ดูรายการงานทั้งหมด
    Route::get('/tasks', [TechnicianController::class, 'getMyTasks']);

    // 2. ดูรายละเอียดงานแต่ละงาน
    Route::get('/tasks/{id}', [TechnicianController::class, 'getTaskDetail']);

    // 3. อัปเดตสถานะงาน (accepted, traveling, arrived, in_progress)
    Route::post('/tasks/{id}/update-status', [TechnicianController::class, 'updateTaskStatus']);

    // 4. ปิดจ๊อบงาน (ส่งรูปหน้างาน ก่อน-หลัง, ลายเซ็น)
    // หมายเหตุ: ต้องยิงเข้ามาแบบ Multipart/form-data
    Route::post('/tasks/{id}/complete', [TechnicianController::class, 'submitCompletion']);

    // 5. ดูข้อมูลโปรไฟล์ตัวเอง
    Route::get('/profile', [TechnicianController::class, 'getProfile']);

    // 6. แก้ไขข้อมูลโปรไฟล์ (ส่งแบบ multipart/form-data ถ้ามีรูปภาพ)
    Route::post('/profile/update', [TechnicianController::class, 'updateProfile']);

    Route::get('/kpi', [TechnicianController::class, 'getTechnicianKPI']);
});
