<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * ตั้งค่าภาษาที่ใช้แสดงผลของทั้งเว็บ (frontend) จากค่าที่เลือกไว้ใน session
     * (เขียนโดย DashboardController::switchLanguage ตอนกดปุ่มสลับภาษาที่ header)
     * ถ้ายังไม่เคยเลือกเลย ใช้ค่า default จาก config/app.php (locale)
     */
    public function handle(Request $request, Closure $next): Response
    {
        // หมายเหตุ: ตั้งค่า default เป็น 'th' ตรงๆ (ไม่ใช้ config('app.locale'))
        // เพราะไฟล์ .env ของโปรเจกต์นี้ตั้ง APP_LOCALE=en ไว้ (ค่า default ของ Laravel skeleton)
        // แต่เนื้อหาเว็บทั้งหมดเขียนเป็นภาษาไทยมาแต่ต้น ผู้ใช้ที่ยังไม่เคยกดเปลี่ยนภาษาจึงควรเห็นภาษาไทยก่อนเสมอ
        $locale = session('app_locale', 'th');

        if (!in_array($locale, ['th', 'en'])) {
            $locale = 'th';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
