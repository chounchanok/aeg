<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // 🌟 ระบบสร้างใบแจ้งหนี้รายเดือนอัตโนมัติ — รันทุกวันตอนตี 1 (รันรายวันเพราะสัญญาแต่ละอันมี
        // billing_day ไม่ตรงกัน และช่วยไล่ตามออกบิลย้อนหลังในเดือนเดียวกันได้เองถ้าวันก่อนหน้า cron ไม่ทำงาน)
        // ⚠️ ต้องตั้ง Windows Task Scheduler ให้รัน `php artisan schedule:run` ทุกนาทีบนเครื่อง production
        // ด้วย (ดูคำแนะนำ schtasks ที่แนบมาพร้อมงานนี้) มิฉะนั้นคำสั่งนี้จะไม่ถูกเรียกเลย
        // หมายเหตุ: ไม่ใส่ ->onOneServer() เพราะ production รันเครื่องเดียว (Windows/XAMPP) ไม่ต้องกันชนข้าม
        // เซิร์ฟเวอร์ — ถ้าในอนาคตขยายเป็นหลายเครื่อง ค่อยเพิ่ม (ต้องใช้ cache driver ที่รองรับ atomic lock เช่น database/redis)
        $schedule->command('invoices:generate')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/invoices-generate.log'));
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // ใช้เมธอด alias เพื่อลงทะเบียนชื่อย่อให้ Middleware
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdminRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
        $middleware->preventRequestsDuringMaintenance(except: [
            'api/*', // ยอมให้แอปฝั่งช่างและลูกค้าเข้า API ได้ปกติ
        ]);
        // ตั้งค่าภาษาที่แสดงผล (TH/EN) จาก session ทุก request ฝั่งเว็บ (ไม่รวม API)
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
