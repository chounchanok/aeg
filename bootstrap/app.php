<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ใช้เมธอด alias เพื่อลงทะเบียนชื่อย่อให้ Middleware
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdminRole::class,
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
