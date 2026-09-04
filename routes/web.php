<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Frontend Controllers
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\RewardController;
use App\Http\Controllers\Frontend\PackageController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\InsuranceController;
use App\Http\Controllers\Frontend\ServiceRequestController;
use App\Http\Controllers\Frontend\SupportChatController;
use App\Http\Controllers\Frontend\FaqController;

// Admin Controllers
use App\Http\Controllers\Admin\ServiceRequestAdminController;
use App\Http\Controllers\Admin\CustomerAdminController;
use App\Http\Controllers\Admin\CmsAdminController;
use App\Http\Controllers\Admin\StaffAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\ServiceCategoryAdminController;
use App\Http\Controllers\Admin\InsuranceAdminController;
use App\Http\Controllers\Admin\ReviewAdminController;
use App\Http\Controllers\Admin\SupportChatAdminController;

use App\Http\Controllers\Api\BblPaymentController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\ChatbotController;

Route::get('/download-app', function (Request $request) {
    $userAgent = $request->header('User-Agent');

    // ตรวจสอบว่าเป็น iOS (iPhone, iPad, iPod)
    if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
        return redirect()->away('https://apps.apple.com/us/app/anglo-east-surety/id6786582784');
    } 
    // ตรวจสอบว่าเป็น Android
    elseif (preg_match('/Android/i', $userAgent)) {
        return redirect()->away('https://play.google.com/store/apps/details?id=com.aeginc.angloeast');
    }

    // กรณีสแกนหรือเปิดผ่านคอมพิวเตอร์ (Desktop) ให้กลับไปหน้าหลัก
    return redirect('/');
})->name('app.download');

// 🌟 เส้นทางสำหรับให้แอปเปิด WebView
Route::get('/payment/bbl/redirect/{order_number}/{type}', [BblPaymentController::class, 'redirect'])->name('bbl.redirect');

// 🌟 เส้นทางสำหรับโชว์ผลลัพธ์หลังจากลูกค้าจ่ายเงินเสร็จ 
Route::get('/payment/bbl/result', function (\Illuminate\Http\Request $request) {
    $status = $request->query('status');
    $msg = "ดำเนินการสำเร็จ";
    if ($status === 'fail') $msg = "การชำระเงินถูกปฏิเสธ";
    if ($status === 'cancel') $msg = "คุณได้ยกเลิกการชำระเงิน";
    
    // โชว์หน้าจอธรรมดา หรือจะ Redirect กลับไปหน้าอื่นก็ได้ครับ
    return "<h2>สถานะ: {$msg}</h2><p>กรุณากลับไปที่แอปพลิเคชัน</p>";
});

// --- WhatsApp Web OTP Login ---
Route::post('auth/whatsapp/request-otp', [AuthController::class, 'requestWhatsappOtp'])->name('whatsapp.request-otp');
Route::post('auth/whatsapp/verify-otp', [AuthController::class, 'verifyWhatsappOtp'])->name('whatsapp.verify-otp');

Route::get('dark-mode-switcher', function() { return back(); })->name('dark-mode-switcher');
Route::get('color-scheme-switcher', function() { return back(); })->name('color-scheme-switcher');

// ==========================================
// 1. PUBLIC ROUTES (ไม่ต้องล็อกอินก็เข้าได้)
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('language-switch/{lang}', [DashboardController::class, 'switchLanguage'])->name('language.switch');
Route::get('/run-link', function () {
    Artisan::call('storage:link');
    return "Storage link has been created.";
});

// ระบบประกันภัย
Route::get('/insurance', [InsuranceController::class, 'index'])->name('insurance');
Route::get('/insurance/{id}', [InsuranceController::class, 'show'])->where('id', '[0-9]+')->name('insurance-detail');
Route::get('/insurance/{id}/contact', [InsuranceController::class, 'contact'])->where('id', '[0-9]+')->name('insurance-contact');
Route::post('/insurance-contact/submit', [InsuranceController::class, 'submitContact'])->name('insurance-contact.submit'); // 🌟 เพิ่มเส้นรับค่าฟอร์มประกัน

// ระบบตู้เซฟ (Safe / Locker)
Route::get('/safe-contact/{id}/contact', [ProductController::class, 'safeContact'])->where('id', '[0-9]+')->name('safe-contact'); // 🌟 ย้ายมาใช้ ProductController แทน
Route::post('/safe-contact/submit', [ProductController::class, 'submitSafeContact'])->name('safe-contact.submit'); // 🌟 เพิ่มเส้นรับค่าฟอร์มตู้เซฟ

// ระบบสินค้าทั่วไป
Route::get('/product-contact/{id}/contact', [ProductController::class, 'contact'])->where('id', '[0-9]+')->name('product-contact');
Route::post('/product-contact/submit', [ProductController::class, 'submitContact'])->name('product-contact.submit');

// --- Chat Bot (public - ดูเมนู/ข้อมูลบริการได้แม้ไม่ได้ล็อกอิน ส่วนถามเพิ่มเติม/สนใจซื้อ เช็คสิทธิ์ในตัว controller) ---
Route::post('/support-chat/bot/ask', [SupportController::class, 'askBot'])->name('support-chat.bot.ask');
Route::prefix('support-chat/bot')->name('support-chat.bot.')->group(function () {
    Route::get('/topics', [ChatbotController::class, 'topics'])->name('topics');
    Route::get('/topics/{topicId}/services', [ChatbotController::class, 'services'])->name('services');
    Route::get('/services/{serviceId}', [ChatbotController::class, 'serviceDetail'])->name('service-detail');
    Route::post('/search', [ChatbotController::class, 'keywordSearch'])->name('search');
    Route::post('/leads', [ChatbotController::class, 'submitLead'])->name('leads');
    Route::post('/rating', [ChatbotController::class, 'submitRating'])->name('rating');
});

// --- Static Pages (หน้าทั่วไป) ---
Route::get('/faq', [FaqController::class, 'index'])->name('faq'); // ดึง FAQ จริงจากชุดข้อมูล Chat Bot
Route::view('/lockers', 'frontend.locker-service')->name('lockers');
Route::view('/locker-detail', 'frontend.locker-detail')->name('locker-detail');
Route::view('/safe-detail', 'frontend.safe-detail')->name('safe-detail');
Route::view('/privacy-policy', 'frontend.privacy-policy')->name('privacy-policy');
Route::view('/terms-conditions', 'frontend.terms-conditions')->name('terms-conditions');
Route::view('/account-deletion', 'frontend.account-deletion')->name('account-deletion');

// --- E-Commerce & Packages (ดูสินค้าและแพ็กเกจได้) ---
// 1. หน้าสินค้าทั้งหมด
Route::get('/products', [ProductController::class, 'index'])->name('product-categories');
// 2. หน้ารายละเอียดสินค้า (ล็อก {id} ให้เป็นตัวเลขเท่านั้น เพื่อป้องกันการชนกับชื่อ group)
Route::get('/products/{id}', [ProductController::class, 'show'])->where('id', '[0-9]+')->name('product-detail');
// 3. หน้าสินค้าแบ่งตามกลุ่ม และ หมวดหมู่ย่อย (🌟 ยุบรวมเหลือบรรทัดเดียว และเติม ? หลัง categoryId)
Route::get('/products/{group}/{categoryId?}', [ProductController::class, 'index'])->name('products');

// ----------------------------------------------------

Route::get('/packages/{type}', [PackageController::class, 'index'])->name('packages');
Route::get('/services', [PackageController::class, 'packagesServices'])->name('services');
Route::get('/rewards-detail/{id}', [RewardController::class, 'show'])->name('rewards-detail');
Route::get('/rewards', [RewardController::class, 'index'])->name('rewards');


// ==========================================
// 2. GUEST ROUTES (สำหรับคนที่ยังไม่ได้ล็อกอิน)
// ==========================================
Route::middleware('guest')->group(function() {
    Route::get('login', [AuthController::class, 'loginView'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.check');
    Route::post('auth/whatsapp/check-login', [AuthController::class, 'checkWhatsappLogin'])->name('whatsapp.check-login');

    Route::get('register', [AuthController::class, 'registerView'])->name('register');
    Route::post('register', [AuthController::class, 'registerSubmit'])->name('register.submit');

    Route::get('verify-otp', [AuthController::class, 'verifyOtpView'])->name('verify-otp');
    Route::post('verify-otp', [AuthController::class, 'verifyOtpSubmit'])->name('verify-otp.submit');

    Route::get('forgot-password', [AuthController::class, 'forgotPasswordView'])->name('forgot-password');
    Route::post('forgot-password', [AuthController::class, 'forgotPasswordSubmit'])->name('forgot-password.submit');
    Route::get('reset-password/{token}', [AuthController::class, 'resetPasswordView'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPasswordSubmit'])->name('password.update');

    // --- Social Login ---
    Route::get('auth/{provider}/redirect', [AuthController::class, 'redirectToProvider'])->name('social.redirect');
    Route::get('auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->name('social.callback');
});


// ==========================================
// 3. AUTH ROUTES (ต้องล็อกอินก่อนถึงจะเข้าได้)
// ==========================================
Route::middleware('auth')->group(function() {

    // --- Support Chats (สำหรับลูกค้า) ---
    Route::get('/support-chat', [SupportChatController::class, 'index'])->name('support-chat');
    Route::get('/support-chat/history', [SupportChatController::class, 'getHistory'])->name('support-chat.history');
    Route::post('/support-chat/send', [SupportChatController::class, 'sendMessage'])->name('support-chat.send');

    // --- API จำลองสำหรับกดอ่านแจ้งเตือนหน้าเว็บ (AJAX) ---
    Route::post('/notifications/{id}/read', function (\Illuminate\Http\Request $request, $id) {
        \Illuminate\Support\Facades\DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->update([
                'is_read' => true,
                'updated_at' => now()
            ]);
        return response()->json(['status' => 'success']);
    })->name('notifications.read');

    // --- Authentication ---
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout.post');

    // --- User Profile & Account ---
    Route::get('/my-account', [ProfileController::class, 'index'])->name('my-account');
    Route::post('/my-account/update', [ProfileController::class, 'update'])->name('my-account.update');
    Route::post('/my-account/delete', [ProfileController::class, 'destroy'])->name('my-account.delete');
    Route::post('/my-account/update-additional', [ProfileController::class, 'updateAdditionalInfo'])->name('my-account.update-additional');

    // 🌟 3 เส้นทางที่เพิ่มใหม่สำหรับจัดการที่อยู่หน้าเว็บ
    Route::post('/my-account/address', [ProfileController::class, 'storeAddress'])->name('my-account.address.store');
    Route::post('/my-account/address/{id}/update', [ProfileController::class, 'updateAddress'])->name('my-account.address.update');
    Route::post('/my-account/address/{id}/delete', [ProfileController::class, 'deleteAddress'])->name('my-account.address.delete');

    // --- Cart (ตะกร้าสินค้า) ---
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');

    // --- My Packages & Service Requests ---
    Route::get('/my-packages', [PackageController::class, 'myPackages'])->name('packages.mine'); // ย้ายเข้ามาอยู่ใน auth ป้องกันบัคเวลาไม่ได้ Login
    // เส้นทางดูรายละเอียดและ Timeline ประวัติงานซ่อมของไอเทมชิ้นนั้นๆ
    Route::get('/my-packages/{id}/detail', [PackageController::class, 'showDetail'])->name('repair-status');
    Route::get('/my-packages/repair-completion/{repairId}', [PackageController::class, 'getRepairCompletionDetail'])->name('packages.repair-completion');
    Route::get('/packages/feedback/{id}', [PackageController::class, 'feedback'])->name('packages.feedback');
    Route::post('/packages/feedback/{id}', [PackageController::class, 'submitFeedback']);

    // 🌟 เปลี่ยนเป็น:
    Route::get('/repairs/request/{id}', [ServiceRequestController::class, 'requestForm'])->name('repair-request');
    Route::post('/repairs/request/{id}', [ServiceRequestController::class, 'submitRequest'])->name('repair-request.submit');

    // 🌟 2 เส้นที่เพิ่มใหม่สำหรับดึงข้อมูลสถานะ และ ประวัติ
    Route::get('/repairs/status/{id}', [ServiceRequestController::class, 'status'])->name('repair-status');
    Route::get('/repairs/history', [ServiceRequestController::class, 'history'])->name('repair-history');



    // ==========================================
    // 4. ADMIN ROUTES (เฉพาะ Admin)
    // ==========================================
    Route::middleware('admin')->group(function() {
        Route::get('/home', [DashboardAdminController::class, 'index'])->name('admin.home');
        Route::get('/admin/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');

        // --- Service Requests ---
        Route::get('/admin/service-requests', [ServiceRequestAdminController::class, 'index'])->name('admin.service-requests');
        Route::get('/admin/service-requests/{id}', [ServiceRequestAdminController::class, 'show'])->name('admin.service-requests.show');
        Route::post('/admin/service-requests/{id}/status', [ServiceRequestAdminController::class, 'updateStatus'])->name('admin.service-requests.status');
        Route::post('/admin/service-requests/{id}/chat', [ServiceRequestAdminController::class, 'sendChat'])->name('admin.service-requests.chat');

        // --- Customers ---
        // ⚠️ ต้องอยู่ก่อน '/admin/customers/{id}' ไม่งั้น Laravel จะจับคำว่า "points-import" เป็นค่า {id} แทน
        Route::get('/admin/customers/points-import', [CustomerAdminController::class, 'pointsImportForm'])->name('admin.customers.points-import');
        Route::post('/admin/customers/points-import', [CustomerAdminController::class, 'pointsImportStore'])->name('admin.customers.points-import.store');
        Route::get('/admin/customers/points-import/{batchId}', [CustomerAdminController::class, 'pointsImportShow'])->name('admin.customers.points-import.show');

        Route::get('/admin/customers', [CustomerAdminController::class, 'index'])->name('admin.customers');
        Route::get('/admin/customers/{id}', [CustomerAdminController::class, 'show'])->name('admin.customers.show');
        Route::post('/admin/customers/{id}/products', [CustomerAdminController::class, 'storeProduct'])->name('admin.customers.products.store');

        // 🌟 2 เส้นทางใหม่ สำหรับเพิ่มประกันและตู้เซฟ
        Route::post('/admin/customers/{id}/insurances', [CustomerAdminController::class, 'storeInsurance'])->name('admin.customers.insurances.store');
        Route::post('/admin/customers/{id}/lockers', [CustomerAdminController::class, 'storeLocker'])->name('admin.customers.lockers.store');

        // 🌟 ใช้คูปองแทนลูกค้า (แอดมินกดปิดโค้ดที่ยัง active ให้กลายเป็น used)
        Route::post('/admin/customers/{id}/reward-codes/{codeId}/redeem', [CustomerAdminController::class, 'markRewardCodeUsed'])->name('admin.customers.reward-codes.redeem');

        // --- Products ---
        Route::get('/admin/products', [ProductAdminController::class, 'index'])->name('admin.products');
        Route::resource('admin/products', ProductAdminController::class)->names([
            'index'   => 'admin.products',
            'create'  => 'admin.products.create',
            'store'   => 'admin.products.store',
            'edit'    => 'admin.products.edit',
            'update'  => 'admin.products.update',
            'destroy' => 'admin.products.destroy',
        ]);

        // --- Notifications (แจ้งเตือน) ---
        Route::prefix('admin/notifications')->name('admin.notifications.')->group(function() {
            Route::get('/', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'store'])->name('store');
            Route::delete('/{id}/delete', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'destroy'])->name('delete');
        });

        // --- Support Chats (แชทติดต่อสอบถามทั่วไป) ---
        Route::prefix('admin/support-chats')->name('admin.support-chats.')->group(function() {
            Route::get('/', [\App\Http\Controllers\Admin\SupportChatAdminController::class, 'index'])->name('index');
            // รับค่า user_id และ topic ผ่าน URL เพื่อดูประวัติแชทเจาะจงเรื่องนั้นๆ
            Route::get('/{user_id}', [\App\Http\Controllers\Admin\SupportChatAdminController::class, 'show'])->name('show');
            Route::post('/{user_id}/reply', [\App\Http\Controllers\Admin\SupportChatAdminController::class, 'reply'])->name('reply');
        });

        // --- Staff ---
        Route::get('/admin/staff', [StaffAdminController::class, 'index'])->name('admin.staff');
        Route::post('/admin/staff', [StaffAdminController::class, 'store'])->name('admin.staff.store');

        // --- Orders ---
        Route::get('/admin/orders', [OrderAdminController::class, 'index'])->name('admin.orders');
        Route::get('/admin/orders/{id}', [OrderAdminController::class, 'show'])->name('admin.orders.show');
        Route::post('/admin/orders/{id}/status', [OrderAdminController::class, 'updateStatus'])->name('admin.orders.status');

        // --- Service Categories ---
        Route::get('/admin/service-categories', [ServiceCategoryAdminController::class, 'index'])->name('admin.service-categories');
        Route::post('/admin/service-categories', [ServiceCategoryAdminController::class, 'store'])->name('admin.service-categories.store');
        Route::post('/admin/service-categories/{id}/update', [ServiceCategoryAdminController::class, 'update'])->name('admin.service-categories.update');
        Route::post('/admin/service-categories/{id}/delete', [ServiceCategoryAdminController::class, 'destroy'])->name('admin.service-categories.delete');

        // --- CMS ---
        Route::prefix('admin/cms')->name('admin.cms.')->group(function() {
            Route::get('/banners', [CmsAdminController::class, 'banners'])->name('banners');
            Route::post('/banners', [CmsAdminController::class, 'storeBanner'])->name('banners.store');
            Route::post('/banners/{id}/update', [CmsAdminController::class, 'updateBanner'])->name('banners.update');
            Route::post('/banners/{id}/delete', [CmsAdminController::class, 'deleteBanner'])->name('banners.delete');

            Route::get('/popup-ads', [CmsAdminController::class, 'popupAds'])->name('popup-ads');
            Route::post('/popup-ads', [CmsAdminController::class, 'storePopupAd'])->name('popup-ads.store');
            Route::post('/popup-ads/{id}/update', [CmsAdminController::class, 'updatePopupAd'])->name('popup-ads.update');
            Route::post('/popup-ads/{id}/delete', [CmsAdminController::class, 'deletePopupAd'])->name('popup-ads.delete');

            Route::get('/faqs', [CmsAdminController::class, 'faqs'])->name('faqs');
            Route::post('/faqs', [CmsAdminController::class, 'storeFaq'])->name('faqs.store');
            Route::post('/faqs/{id}/update', [CmsAdminController::class, 'updateFaq'])->name('faqs.update');
            Route::post('/faqs/{id}/delete', [CmsAdminController::class, 'deleteFaq'])->name('faqs.delete');

            Route::get('/ease-club', [CmsAdminController::class, 'easeClub'])->name('ease-club');
            Route::post('/ease-club/rewards', [CmsAdminController::class, 'storeReward'])->name('ease-club.rewards.store');
            Route::post('/ease-club/rewards/{id}/update', [CmsAdminController::class, 'updateReward'])->name('ease-club.rewards.update');
            Route::post('/ease-club/rewards/{id}/delete', [CmsAdminController::class, 'deleteReward'])->name('ease-club.rewards.delete');
        });

        // จัดการตู้เซฟ (Smart Lockers)
        Route::get('/admin/smart-lockers', [App\Http\Controllers\Admin\SmartLockerAdminController::class, 'index'])->name('admin.smart-lockers.index');
        Route::post('/admin/smart-lockers', [App\Http\Controllers\Admin\SmartLockerAdminController::class, 'store'])->name('admin.smart-lockers.store');
        Route::post('/admin/smart-lockers/{id}/update', [App\Http\Controllers\Admin\SmartLockerAdminController::class, 'update'])->name('admin.smart-lockers.update');
        Route::post('/admin/smart-lockers/{id}/delete', [App\Http\Controllers\Admin\SmartLockerAdminController::class, 'destroy'])->name('admin.smart-lockers.delete');

        // 🌟 ย้ายมาวางตรงนี้ครับ (ให้ระบบมันสร้าง prefix คำว่า admin ให้อัตโนมัติ)
        Route::resource('admin/insurances', InsuranceAdminController::class)->names([
            'index'   => 'admin.insurances.index',
            'create'  => 'admin.insurances.create',
            'store'   => 'admin.insurances.store',
            'edit'    => 'admin.insurances.edit',
            'update'  => 'admin.insurances.update',
            'destroy' => 'admin.insurances.destroy',
        ]);
        // เพิ่มในฝั่ง Backend (ใน group admin):
        Route::get('/admin/reviews', [ReviewAdminController::class, 'index'])->name('admin.reviews');

    });

});
