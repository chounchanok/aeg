<?php

/**
 * 🌟 ลิงก์สโตร์ของแอป AEG EASE CLUB (ใช้กับ QR ดาวน์โหลดแยกทีม)
 */
return [
    'app_store_url' => env('APP_STORE_URL', 'https://apps.apple.com/us/app/anglo-east-surety/id6786582784'),
    'play_store_url' => env('PLAY_STORE_URL', 'https://play.google.com/store/apps/details?id=com.aeginc.angloeast'),

    // App Store Connect → Analytics → Campaigns: ต้องมี Provider Token (pt) ยอดติดตั้งแยกทีม (ct) ถึงจะไปขึ้นใน App Store Connect
    // หาได้จาก App Store Connect → Analytics → Sources → "Generate a Campaign Link" (ตัวเลขหลัง pt=)
    'app_store_provider_token' => env('APP_STORE_PROVIDER_TOKEN'),

    // แสดงยอดติดตั้งใน Dashboard — ปิดไว้จนกว่าแอปเวอร์ชันที่เรียก POST /api/app-installs จะออก
    // พร้อมแล้วตั้ง APP_DOWNLOAD_SHOW_INSTALLS=true ใน .env (API ยังรับข้อมูลอยู่ตลอด แค่ซ่อนการแสดงผล)
    'show_installs' => (bool) env('APP_DOWNLOAD_SHOW_INSTALLS', false),

    // iOS ไม่มี install referrer → จับคู่การติดตั้งกับการสแกน QR บน iPhone จาก IP เดียวกันภายในกี่ชั่วโมง (โดยประมาณ)
    'ios_match_hours' => (int) env('APP_INSTALL_IOS_MATCH_HOURS', 24),
];
