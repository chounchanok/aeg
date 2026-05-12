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

// --- FAQ (ไม่ต้องล็อกอินก็ดูได้) ---
Route::get('/faqs', [SupportController::class, 'getFaqs']);

// --- Public Routes ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/social-login', [AuthController::class, 'socialLogin']);
Route::get('/service-categories', [ServiceCategoryApiController::class, 'index']);

Route::post('/login', [AuthController::class, 'login']);

// --- E-Commerce Public Routes ---
Route::prefix('ecommerce')->group(function () {
    Route::get('/products', [EcommerceController::class, 'getProducts']);
    Route::get('/products/{id}', [EcommerceController::class, 'getProductDetail']);
    
    // Webhook สำหรับรับข้อมูลจาก Payment Gateway (ไม่ต้อง Auth)
    Route::post('/payment/webhook', [EcommerceController::class, 'paymentWebhook']);
});

// --- E-Commerce Protected Routes (ต้องล็อกอิน) ---
Route::middleware('auth:sanctum')->prefix('ecommerce')->group(function () {
    // ระบบตะกร้าสินค้า
    Route::get('/cart', [EcommerceController::class, 'getCart']);
    Route::post('/cart/add', [EcommerceController::class, 'addToCart']);
    Route::delete('/cart/remove/{cartItemId}', [EcommerceController::class, 'removeFromCart']);
    Route::post('/cart/clear', [EcommerceController::class, 'clearCart']);

    // ระบบที่อยู่
    Route::get('/addresses', [EcommerceController::class, 'getAddresses']);
    Route::post('/addresses', [EcommerceController::class, 'createAddress']);

    // ระบบ Checkout และ Payment
    Route::post('/checkout', [EcommerceController::class, 'checkout']);
    Route::post('/buy-now', [EcommerceController::class, 'buyNow']); // 🌟 เพิ่มตรงนี้
    Route::get('/orders', [EcommerceController::class, 'getMyOrders']);
});

// --- Public Routes (ไม่ต้องล็อกอิน) ---
Route::prefix('main')->group(function () {
    Route::get('/banners', [MainPageController::class, 'getBanners']);
    Route::get('/recommended-services', [MainPageController::class, 'getRecommendedServices']);
});

// --- Protected Routes (ต้องล็อกอิน) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Main Page (ส่วนบุคคล)
    Route::prefix('main')->group(function () {
        Route::get('/expiring-services', [MainPageController::class, 'getExpiringServices']);
        Route::get('/recommended-privileges', [MainPageController::class, 'getRecommendedPrivileges']);
    });

    // EASE CLUB Rewards
    Route::prefix('ease-club')->group(function () {
        Route::get('/user-info', [EaseClubController::class, 'getUserInfo']); // ข้อมูล User + คะแนน
        Route::get('/banners', [EaseClubController::class, 'getBanners']); // Banner หน้า EASE CLUB
        Route::get('/overview', [EaseClubController::class, 'getOverview']); // หมวดหมู่ + พิเศษสำหรับ Advance
        Route::get('/categories/{categoryId}/rewards', [EaseClubController::class, 'getRewardsByCategory']); // รายการสินค้าตามหมวด
        Route::get('/rewards/{rewardId}', [EaseClubController::class, 'getRewardDetail']); // รายละเอียดของรางวัล
        Route::post('/rewards/{rewardId}/redeem', [EaseClubController::class, 'redeemReward']); // ยืนยันการแลกพอยท์
    });
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