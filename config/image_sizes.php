<?php

/**
 * ขนาดรูปที่แนะนำสำหรับทุก input อัปโหลดรูปในหลังบ้าน (คอมเมนต์ข้อ 10)
 * วัดจากพื้นที่แสดงผลจริงบนหน้าเว็บ (CSS) และรูปที่ใช้งานอยู่ในระบบ แล้วคูณ ~2 เท่าให้คมชัดบนจอ Retina/มือถือ
 * แสดงใต้ input ผ่าน @include('admin.partials.image-hint', ['key' => '...'])
 */
return [
    'banner_desktop' => [
        'size' => '2592 × 800 px', 'ratio' => '', 'min' => '1296 × 400 px',
        'where' => '',
    ],
    'banner_mobile' => [
        'size' => '732 × 500 px', 'ratio' => '', 'min' => '366 × 250 px',
        'where' => '',
    ],
    'popup' => [
        'size' => '1080 × 1350 px', 'ratio' => '', 'min' => '500 × 625 px',
        'where' => '',
    ],
    'reward' => [
        'size' => '800 × 600 px', 'ratio' => '', 'min' => '520 × 390 px',
        'where' => '',
    ],
    'product' => [
        'size' => '1000 × 850 px', 'ratio' => '', 'min' => '500 × 425 px',
        'where' => '',
    ],
    'service_category' => [
        'size' => '200 × 200 px', 'ratio' => '', 'min' => '70 × 70 px',
        'where' => '',
    ],
    'smart_locker' => [
        'size' => '1900 × 840 px', 'ratio' => '', 'min' => '950 × 420 px',
        'where' => '',
    ],
    'insurance' => [
        'size' => '1800 × 800 px', 'ratio' => '', 'min' => '900 × 400 px',
        'where' => '',
    ],
    'insurance_inside' => [
        'size' => '1200 × 800 px', 'ratio' => '', 'min' => '600 × 400 px',
        'where' => '',
    ],
];
