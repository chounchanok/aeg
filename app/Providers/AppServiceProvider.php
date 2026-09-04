<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            [\SocialiteProviders\Line\LineExtendSocialite::class, 'handle']
        );

        View::share('layout', 'side-menu');
        View::share('dark_mode', false);
        View::share('color_scheme', 'default');

        // 🌟 เมนูทั้งหมด — แต่ละรายการที่ต้องมีสิทธิ์เฉพาะ (RBAC) จะมี key 'permission' กำกับไว้
        // รายการที่ไม่มี key 'permission' หมายถึงพนักงาน/แอดมินทุกคนเห็นได้เสมอ (เช่น dashboard, customers)
        $fullSideMenu = [
            'dashboard' => [
                'icon' => 'home', 'title' => 'หน้าหลัก (Dashboard)',
                'route_name' => 'admin.dashboard', 'params' => []
            ],
            'devider',
            'service-requests' => [
                'icon' => 'wrench', 'title' => 'จัดการแจ้งซ่อม',
                'route_name' => 'admin.service-requests', 'params' => [],
                'permission' => 'service_requests.manage',
            ],
            'customers' => [
                'icon' => 'users', 'title' => 'ลูกค้าและแพ็กเกจ',
                'route_name' => 'admin.customers', 'params' => []
                // 🌟 ไม่ผูก permission — ตามที่กำหนดไว้ว่า "ดูได้ทุก Role แต่ไม่สามารถแก้ไขได้"
            ],
            'orders' => [
                'icon' => 'shopping-cart', 'title' => 'ประวัติคำสั่งซื้อ',
                'route_name' => 'admin.orders', 'params' => [],
                'permission' => 'orders.manage',
            ],
            'products' => [
                'icon' => 'box', 'title' => 'สินค้าและบริการ (Master)',
                'route_name' => 'admin.products', 'params' => [],
                'permission' => 'products.manage',
            ],
            'service-categories' => [
                'icon' => 'grid', 'title' => 'หมวดหมู่บริการ',
                'route_name' => 'admin.service-categories', 'params' => [],
                'permission' => 'service_categories.manage',
            ],
            'smart-lockers' => [
                'icon' => 'lock', 'title' => 'ตู้เซฟนิรภัย (Smart Lockers)',
                'route_name' => 'admin.smart-lockers.index', 'params' => [],
                'permission' => 'smart_lockers.manage',
            ],
            'insurances' => [
                'icon' => 'shield', 'title' => 'จัดการข้อมูลประกันภัย',
                'route_name' => 'admin.insurances.index', 'params' => [],
                'permission' => 'insurances.manage',
            ],
            'notifications' => [
                'icon' => 'bell', 'title' => 'จัดการแจ้งเตือน (Push)',
                'route_name' => 'admin.notifications.index', 'params' => [],
                'permission' => 'notifications.manage',
            ],
            'support-chats' => [
                'icon' => 'message-circle', 'title' => 'แชทติดต่อสอบถาม',
                'route_name' => 'admin.support-chats.index', 'params' => [],
                'permission' => 'support_chats.reply',
            ],
            'devider',
            'cms' => [
                'icon' => 'layout', 'title' => 'จัดการแอป (CMS)',
                'sub_menu' => [
                    'banners' => ['icon' => 'image', 'title' => 'แบนเนอร์โปรโมชัน', 'route_name' => 'admin.cms.banners', 'params' => [], 'permission' => 'cms.manage'],
                    'popup-ads' => ['icon' => 'maximize', 'title' => 'ป๊อปอัพโฆษณา', 'route_name' => 'admin.cms.popup-ads', 'params' => [], 'permission' => 'cms.manage'],
                    'ease-club' => ['icon' => 'gift', 'title' => 'สิทธิประโยชน์ EASE CLUB', 'route_name' => 'admin.cms.ease-club', 'params' => [], 'permission' => 'cms.manage'],
                    'faqs' => ['icon' => 'help-circle', 'title' => 'คำถามที่พบบ่อย (FAQ)', 'route_name' => 'admin.cms.faqs', 'params' => [], 'permission' => 'cms.manage'],
                    'reviews' => ['icon' => 'star', 'title' => 'รีวิวจากลูกค้า', 'route_name' => 'admin.reviews', 'params' => [], 'permission' => 'cms.manage'],
                ]
            ],
            'settings' => [
                'icon' => 'settings', 'title' => 'ตั้งค่าระบบ',
                'sub_menu' => [
                    'staff' => ['icon' => 'user-check', 'title' => 'พนักงานและช่างซ่อม', 'route_name' => 'admin.staff', 'params' => [], 'permission' => 'staff.manage']
                ]
            ]
        ];

        // 🌟 ใช้ View::composer แทน View::share ตรงๆ เพราะต้องอ่านค่า Auth::user() ซึ่งยังไม่พร้อมใช้งาน
        // ตอน ServiceProvider::boot() ทำงาน (เกิดก่อน middleware pipeline/session) — composer จะรันตอน
        // view กำลัง render จริง ซึ่ง auth resolve เสร็จแล้วแน่นอน
        View::composer('*', function ($view) use ($fullSideMenu) {
            $view->with('side_menu', $this->filterMenuByPermission($fullSideMenu));
        });
    }

    /**
     * กรองเมนู sidebar ตามสิทธิ์ (RBAC) ของผู้ใช้ที่ล็อกอินอยู่
     * - ยังไม่ล็อกอิน หรือเป็นลูกค้า (role=customer): คืนเมนูเต็ม (ไม่มีผล เพราะเข้าหน้า admin ไม่ได้อยู่แล้ว)
     * - super_admin (ตามคอลัมน์ users.role เดิม ก่อนมี RBAC): เห็นเมนูครบทุกอัน (backward-compat)
     * - อื่นๆ: กรองตาม permission ที่ role (RBAC ใหม่) ของ user มี — role แบบ full access (เช่น 'it') เห็นครบ
     */
    protected function filterMenuByPermission(array $menu): array
    {
        $user = Auth::user();

        if (!$user || $user->role === 'customer') {
            return $menu;
        }

        $permissionKeys = $user->role === 'super_admin' ? ['*'] : $user->permissionKeys();
        $hasFullAccess = in_array('*', $permissionKeys, true);

        $filtered = [];
        foreach ($menu as $key => $item) {
            if ($item === 'devider') {
                $filtered[$key] = $item;
                continue;
            }

            if (isset($item['sub_menu'])) {
                $subMenu = [];
                foreach ($item['sub_menu'] as $subKey => $subItem) {
                    $required = $subItem['permission'] ?? null;
                    if ($required === null || $hasFullAccess || in_array($required, $permissionKeys, true)) {
                        $subMenu[$subKey] = $subItem;
                    }
                }
                if (!empty($subMenu)) {
                    $item['sub_menu'] = $subMenu;
                    $filtered[$key] = $item;
                }
                continue;
            }

            $required = $item['permission'] ?? null;
            if ($required === null || $hasFullAccess || in_array($required, $permissionKeys, true)) {
                $filtered[$key] = $item;
            }
        }

        return $filtered;
    }
}
