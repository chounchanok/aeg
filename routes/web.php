<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Frontend\HomeController; // เรียกใช้ Frontend Controller
use App\Http\Controllers\Admin\ServiceRequestAdminController;
use App\Http\Controllers\Admin\CustomerAdminController;
use App\Http\Controllers\Admin\CmsAdminController;
use App\Http\Controllers\Admin\StaffAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\ServiceCategoryAdminController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\RewardController;
use App\Http\Controllers\Frontend\PackageController;
use App\Http\Controllers\Frontend\CartController;
// --- นำไปใส่ไว้ในส่วนของ Public (ไม่ต้องล็อกอินก็ดูสินค้าได้) ---
use App\Http\Controllers\Frontend\ProductController;

Route::get('/products', [ProductController::class, 'index'])->name('product-categories');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('product-detail');

// --- นำไปใส่ไว้ในกลุ่ม Route::middleware('auth') (ต้องล็อกอินถึงจะจัดการตะกร้าได้) ---
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
// (ส่วน Route::get('/cart' เดิมที่คุณมีอยู่แล้ว ให้เก็บไว้ได้เลยครับ)
// --- Routes สำหรับดูสิทธิพิเศษ (คนทั่วไปดูได้) ---
Route::get('/rewards', [RewardController::class, 'index'])->name('rewards');
Route::get('/my-packages', [PackageController::class, 'myPackages'])->name('packages.mine');
Route::get('/packages/feedback/{id}', [PackageController::class, 'feedback'])->name('packages.feedback');
Route::view('/packages/{type}', [PackageController::class, 'index'])->name('packages');

// --- Frontend Routes ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// Main Pages
Route::view('/faq', 'frontend.faq')->name('faq');
Route::view('/insurance', 'frontend.insurance')->name('insurance');
Route::view('/lockers', 'frontend.locker-service')->name('lockers');
Route::view('/my-account', 'frontend.my-account')->name('my-account');
Route::view('/privacy-policy', 'frontend.privacy-policy')->name('privacy-policy');
Route::view('/product-categories', 'frontend.product-catagry')->name('product-categories');
Route::view('/services', 'frontend.services')->name('services');
Route::view('/terms-conditions', 'frontend.terms-conditions')->name('terms-conditions');

// Repair related pages
Route::view('/repairs/history', 'frontend.repair-history')->name('repair-history');
Route::view('/repairs/request', 'frontend.repair-request')->name('repair-request');
Route::view('/repairs/status', 'frontend.repair-status')->name('repair-status');

// Other specialized pages
Route::view('/insurance/contact', 'frontend.insurance-contact')->name('insurance-contact');
// Route::view('/packages/feedback', 'frontend.package-feedback')->name('package-feedback');
Route::view('/service-packages', 'frontend.service-package')->name('service-packages');

// Specific Detail Pages
Route::view('/insurance/detail', 'frontend.insurance-detail')->name('insurance-detail');
Route::view('/history-detail', 'frontend.history-detail')->name('history-detail');
Route::view('/locker-detail', 'frontend.locker-detail')->name('locker-detail');
Route::view('/package-detail-s', 'frontend.package-detail-s')->name('package-detail-simple');
Route::view('/package-detail', 'frontend.package-detail')->name('package-detail');
Route::view('/product-detail', 'frontend.product-detail')->name('product-detail');
Route::view('/safe-detail', 'frontend.safe-detail')->name('safe-detail');

Route::get('language-switch/{lang}', [DashboardController::class, 'switchLanguage'])->name('language.switch');

// --- หน้าที่ยังไม่ได้ล็อกอิน (Guest) ---
Route::middleware('guest')->group(function() {
    // เข้าสู่ระบบ
    Route::get('login', [AuthController::class, 'loginView'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.check');

    // สมัครสมาชิก
    Route::get('register', [AuthController::class, 'registerView'])->name('register');
    Route::post('register', [AuthController::class, 'registerSubmit'])->name('register.submit');

    // ยืนยัน OTP
    Route::get('verify-otp', [AuthController::class, 'verifyOtpView'])->name('verify-otp');
    Route::post('verify-otp', [AuthController::class, 'verifyOtpSubmit'])->name('verify-otp.submit');

    // ลืมรหัสผ่าน
    Route::get('forgot-password', [AuthController::class, 'forgotPasswordView'])->name('forgot-password');
    Route::post('forgot-password', [AuthController::class, 'forgotPasswordSubmit'])->name('forgot-password.submit');
    Route::get('reset-password/{token}', [AuthController::class, 'resetPasswordView'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPasswordSubmit'])->name('password.update');
});

// --- Routes ที่ต้องล็อกอินก่อนถึงจะเข้าได้ (ทั้ง Customer และ Admin) ---
Route::middleware('auth')->group(function() {

    // ใช้ POST หรือ GET ก็ได้ตามที่ออกแบบไว้
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout.post');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');

    //Frontend Pages ที่ต้องล็อกอิน
    // จัดการข้อมูลส่วนตัว (My Account)
    Route::get('/my-account', [ProfileController::class, 'index'])->name('my-account');
    Route::post('/my-account/update', [ProfileController::class, 'update'])->name('my-account.update');

    // --- หน้า Admin Dashboard (ล็อกอินแล้ว + ต้องไม่ใช่ Customer) ---
    // หมายเหตุ: ตรง 'admin' คือชื่อ Alias ของ Middleware CheckAdminRole ที่เราสร้างขึ้น
    Route::middleware('admin')->group(function() {

        Route::get('/home', [DashboardAdminController::class, 'index'])->name('admin.home');
        Route::get('/admin/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');

        // 1. เมนูจัดการแจ้งซ่อม
        Route::get('/admin/service-requests', [ServiceRequestAdminController::class, 'index'])->name('admin.service-requests');
        Route::get('/admin/service-requests/{id}', [ServiceRequestAdminController::class, 'show'])->name('admin.service-requests.show');
        Route::post('/admin/service-requests/{id}/status', [ServiceRequestAdminController::class, 'updateStatus'])->name('admin.service-requests.status');
        Route::post('/admin/service-requests/{id}/chat', [ServiceRequestAdminController::class, 'sendChat'])->name('admin.service-requests.chat');

        // 2. เมนูจัดการลูกค้า
        Route::get('/admin/customers', [CustomerAdminController::class, 'index'])->name('admin.customers');
        Route::get('/admin/customers/{id}', [CustomerAdminController::class, 'show'])->name('admin.customers.show');
        Route::post('/admin/customers/{id}/products', [CustomerAdminController::class, 'storeProduct'])->name('admin.customers.products.store');

        // สินค้าหลัก
        Route::get('/admin/products', [ProductAdminController::class, 'index'])->name('admin.products');
        Route::resource('admin/products', ProductAdminController::class)->names([
            'index'   => 'admin.products',
            'create'  => 'admin.products.create',
            'store'   => 'admin.products.store',
            'edit'    => 'admin.products.edit',
            'update'  => 'admin.products.update',
            'destroy' => 'admin.products.destroy',
        ]);

        // พนักงาน/ช่างซ่อม
        Route::get('/admin/staff', [StaffAdminController::class, 'index'])->name('admin.staff');
        Route::post('/admin/staff', [StaffAdminController::class, 'store'])->name('admin.staff.store');

        // จัดการคำสั่งซื้อ (Orders)
        Route::get('/admin/orders', [OrderAdminController::class, 'index'])->name('admin.orders');
        Route::get('/admin/orders/{id}', [OrderAdminController::class, 'show'])->name('admin.orders.show');
        Route::post('/admin/orders/{id}/status', [OrderAdminController::class, 'updateStatus'])->name('admin.orders.status');

        Route::get('/admin/service-categories', [ServiceCategoryAdminController::class, 'index'])->name('admin.service-categories');
        Route::post('/admin/service-categories', [ServiceCategoryAdminController::class, 'store'])->name('admin.service-categories.store');
        Route::post('/admin/service-categories/{id}/update', [ServiceCategoryAdminController::class, 'update'])->name('admin.service-categories.update');
        Route::post('/admin/service-categories/{id}/delete', [ServiceCategoryAdminController::class, 'destroy'])->name('admin.service-categories.delete');

        // 3. เมนู CMS (จัดการแอป)
        Route::prefix('admin/cms')->name('admin.cms.')->group(function() {
            // --- ระบบแบนเนอร์ ---
            Route::get('/banners', [CmsAdminController::class, 'banners'])->name('banners');
            Route::post('/banners', [CmsAdminController::class, 'storeBanner'])->name('banners.store');
            Route::post('/banners/{id}/update', [CmsAdminController::class, 'updateBanner'])->name('banners.update');
            Route::post('/banners/{id}/delete', [CmsAdminController::class, 'deleteBanner'])->name('banners.delete');

            // --- ระบบ FAQ ---
            Route::get('/faqs', [CmsAdminController::class, 'faqs'])->name('faqs');
            Route::post('/faqs', [CmsAdminController::class, 'storeFaq'])->name('faqs.store');
            Route::post('/faqs/{id}/delete', [CmsAdminController::class, 'deleteFaq'])->name('faqs.delete');

            // --- ระบบสิทธิประโยชน์ EASE CLUB ---
            Route::get('/ease-club', [CmsAdminController::class, 'easeClub'])->name('ease-club');
            Route::post('/ease-club/rewards', [CmsAdminController::class, 'storeReward'])->name('ease-club.rewards.store');
            Route::post('/ease-club/rewards/{id}/update', [CmsAdminController::class, 'updateReward'])->name('ease-club.rewards.update');
            Route::post('/ease-club/rewards/{id}/delete', [CmsAdminController::class, 'deleteReward'])->name('ease-club.rewards.delete');
        });
    });
});

Route::get('dark-mode-switcher', [DarkModeController::class, 'switch'])->name('dark-mode-switcher');
Route::get('color-scheme-switcher/{color_scheme}', [ColorSchemeController::class, 'switch'])->name('color-scheme-switcher');

Route::get('/run-link', function () {
    Artisan::call('storage:link');
    return "Storage link has been created.";
});
