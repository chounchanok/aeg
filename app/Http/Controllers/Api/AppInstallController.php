<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AppDownloadService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * 🌟 POST /api/app-installs — แอปเรียกครั้งเดียวตอนเปิดครั้งแรกหลังติดตั้ง (ไม่ต้องล็อกอิน)
 */
class AppInstallController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request)
    {
        $data = $request->validate([
            'platform' => 'required|in:ios,android',
            'device_id' => 'required|string|max:191',
            'install_referrer' => 'nullable|string|max:1000', // Android: ค่าจาก Play Install Referrer API
            'team_code' => 'nullable|string|max:40',         // ถ้าแอปให้กรอกรหัสทีม/ผู้แนะนำเอง
            'app_version' => 'nullable|string|max:30',
        ]);

        $result = AppDownloadService::recordInstall($data, $request->ip(), optional($request->user('sanctum'))->id);
        $install = $result['install'];

        return $this->successResponse([
            'recorded' => $result['created'],               // false = เครื่องนี้เคยรายงานแล้ว (ไม่นับซ้ำ)
            'team_code' => isset($result['team']) && $result['team'] ? $result['team']->code : null,
            'attribution' => $install->attribution,
        ], $result['created'] ? 'Install recorded' : 'Install already recorded');
    }
}
