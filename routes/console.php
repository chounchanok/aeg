<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 🌟 ประเมินระดับสมาชิกที่ครบ 1 ปีทุกวัน (ต้องตั้ง cron: * * * * * php artisan schedule:run)
\Illuminate\Support\Facades\Schedule::command('loyalty:refresh-tiers')->dailyAt('01:15');

// 🌟 เปิดใบงานบำรุงรักษาตามรอบทุกเช้า
\Illuminate\Support\Facades\Schedule::command('maintenance:generate-tickets')->dailyAt('06:10');
