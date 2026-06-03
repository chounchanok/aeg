<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Share ค่าพื้นฐานให้ทุกหน้า
        View::share('layout', 'side-menu');
        View::share('dark_mode', false);
        View::share('color_scheme', 'default');

        // 2. Share ตัวแปรเมนู ให้ทุกไฟล์มองเห็น (จบปัญหา Undefined variable)
        $side_menu = [
            'dashboard' => [
                'icon' => 'home',
                'title' => 'หน้าหลัก (Dashboard)',
                'route_name' => 'admin.dashboard',
                'params' => []
            ],
            'devider', // เส้นคั่นเมนู
            'service-requests' => [
                'icon' => 'wrench',
                'title' => 'จัดการแจ้งซ่อม',
                'route_name' => 'admin.service-requests',
                'params' => []
            ],
            'customers' => [
                'icon' => 'users',
                'title' => 'ลูกค้าและแพ็กเกจ',
                'route_name' => 'admin.customers',
                'params' => []
            ],
            'orders' => [
                'icon' => 'shopping-cart',
                'title' => 'ประวัติคำสั่งซื้อ',
                'route_name' => 'admin.orders',
                'params' => []
            ],
            'products' => [
                'icon' => 'box',
                'title' => 'สินค้าและบริการ (Master)',
                'route_name' => 'admin.products',
                'params' => []
            ],
            'service-categories' => [
                'icon' => 'grid', // ใช้ไอคอนตารางสี่เหลี่ยม
                'title' => 'หมวดหมู่บริการ',
                'route_name' => 'admin.service-categories',
                'params' => []
            ],
            'smart-lockers' => [
                'icon' => 'lock', // ใช้ไอคอนรูปแม่กุญแจ
                'title' => 'ตู้เซฟนิรภัย (Smart Lockers)',
                'route_name' => 'admin.smart-lockers.index',
                'params' => []
            ],
            'insurances' => [
                'icon' => 'shield', // ใช้ไอคอนรูปโล่
                'title' => 'จัดการข้อมูลประกันภัย',
                'route_name' => 'admin.insurances.index',
                'params' => []
            ],
            'devider', // เส้นคั่นเมนู
            'cms' => [
                'icon' => 'layout',
                'title' => 'จัดการแอป (CMS)',
                'sub_menu' => [
                    'banners' => [
                        'icon' => 'image',
                        'title' => 'แบนเนอร์โปรโมชัน',
                        'route_name' => 'admin.cms.banners',
                        'params' => []
                    ],
                    'ease-club' => [
                        'icon' => 'gift',
                        'title' => 'สิทธิประโยชน์ EASE CLUB',
                        'route_name' => 'admin.cms.ease-club',
                        'params' => []
                    ],
                    'faqs' => [
                        'icon' => 'help-circle',
                        'title' => 'คำถามที่พบบ่อย (FAQ)',
                        'route_name' => 'admin.cms.faqs',
                        'params' => []
                    ],
                    'reviews' => [
                        'icon' => 'star',
                        'title' => 'รีวิวจากลูกค้า',
                        'route_name' => 'admin.reviews',
                        'params' => []
                    ],
                ]
            ],
            'settings' => [
                'icon' => 'settings',
                'title' => 'ตั้งค่าระบบ',
                'sub_menu' => [
                    'staff' => [
                        'icon' => 'user-check',
                        'title' => 'พนักงานและช่างซ่อม',
                        'route_name' => 'admin.staff',
                        'params' => []
                    ]
                ]
            ]
        ];

        View::share('side_menu', $side_menu);
    }
}
