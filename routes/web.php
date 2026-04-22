<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ServiceRequestAdminController;
use App\Http\Controllers\Admin\CustomerAdminController;
use App\Http\Controllers\Admin\CmsAdminController;
use App\Http\Controllers\Admin\StaffAdminController;
use App\Http\Controllers\Admin\ProductAdminController;

// --- หน้า Login (ยังไม่ได้ล็อกอิน) ---
Route::middleware('guest')->group(function() {
    Route::get('login', [AuthController::class, 'loginView'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.check');
});

// --- หน้า Admin Dashboard (ล็อกอินแล้ว) ---
Route::middleware('auth')->group(function() {
    
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 1. เมนูจัดการแจ้งซ่อม
    Route::get('/admin/service-requests', [ServiceRequestAdminController::class, 'index'])->name('admin.service-requests');
    Route::get('/admin/service-requests/{id}', [ServiceRequestAdminController::class, 'show'])->name('admin.service-requests.show');
    // 🌟 ระบบอัปเดตสถานะ และ ส่งแชทของ Admin
    Route::post('/admin/service-requests/{id}/status', [ServiceRequestAdminController::class, 'updateStatus'])->name('admin.service-requests.status');
    Route::post('/admin/service-requests/{id}/chat', [ServiceRequestAdminController::class, 'sendChat'])->name('admin.service-requests.chat');
    
    // 2. เมนูจัดการลูกค้า
    // จัดการลูกค้าและแพ็กเกจ (Customers & Customer Products)
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
    
    // พนักงาน/ช่างซ่อม
    Route::get('/admin/staff', [StaffAdminController::class, 'index'])->name('admin.staff');
    Route::post('/admin/staff', [StaffAdminController::class, 'store'])->name('admin.staff.store'); // 🌟 เพิ่มบรรทัดนี้

    // 3. เมนู CMS (จัดการแอป)
    Route::prefix('admin/cms')->name('admin.cms.')->group(function() {
        // --- ระบบแบนเนอร์ ---
        Route::get('/banners', [CmsAdminController::class, 'banners'])->name('banners');
        Route::post('/banners', [CmsAdminController::class, 'storeBanner'])->name('banners.store');
        Route::post('/banners/{id}/delete', [CmsAdminController::class, 'deleteBanner'])->name('banners.delete');

        // --- ระบบ FAQ ---
        Route::get('/faqs', [CmsAdminController::class, 'faqs'])->name('faqs');
        Route::post('/faqs', [CmsAdminController::class, 'storeFaq'])->name('faqs.store');
        Route::post('/faqs/{id}/delete', [CmsAdminController::class, 'deleteFaq'])->name('faqs.delete');
        
        // --- ระบบสิทธิประโยชน์ (เตรียมไว้สเตปถัดไป) ---
        Route::get('/ease-club', [CmsAdminController::class, 'easeClub'])->name('ease-club');
    });

});

Route::get('dark-mode-switcher', [DarkModeController::class, 'switch'])->name('dark-mode-switcher');
Route::get('color-scheme-switcher/{color_scheme}', [ColorSchemeController::class, 'switch'])->name('color-scheme-switcher');