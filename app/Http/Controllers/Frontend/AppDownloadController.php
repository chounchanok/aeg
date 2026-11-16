<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\AppDownloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 🌟 ลิงก์/QR ดาวน์โหลดแอปของแต่ละทีม: /d/{code}
 * มือถือ → นับการสแกนแล้วส่งไปสโตร์ทันที, คอมพิวเตอร์ → หน้าเลือก App Store / Google Play
 */
class AppDownloadController extends Controller
{
    public function go(Request $request, string $code)
    {
        $team = DB::table('app_download_teams')->where('code', $code)->first();
        if (!$team || !$team->is_active) {
            // ทีมถูกปิด/ไม่มี → ยังพาไปดาวน์โหลดได้ แต่ไม่นับยอด
            $platform = AppDownloadService::detectPlatform($request->userAgent());
            if ($platform === 'other') {
                return view('frontend.app-download', ['team' => null, 'code' => $code]);
            }
            return redirect()->away($platform === 'android' ? config('app_download.play_store_url') : config('app_download.app_store_url'));
        }

        $platform = AppDownloadService::detectPlatform($request->userAgent());
        if ($platform === 'other') {
            return view('frontend.app-download', ['team' => $team, 'code' => $code]);
        }

        AppDownloadService::recordClick($team, $platform, $request->ip(), $request->userAgent());
        return redirect()->away(AppDownloadService::storeUrl($platform, $team->code));
    }

    /** ปุ่มบนหน้าเลือกสโตร์ (เปิดจากคอมพิวเตอร์) */
    public function goPlatform(Request $request, string $code, string $platform)
    {
        abort_unless(in_array($platform, ['ios', 'android'], true), 404);
        $team = DB::table('app_download_teams')->where('code', $code)->where('is_active', true)->first();
        if ($team) {
            AppDownloadService::recordClick($team, $platform, $request->ip(), $request->userAgent());
            return redirect()->away(AppDownloadService::storeUrl($platform, $team->code));
        }
        return redirect()->away($platform === 'android' ? config('app_download.play_store_url') : config('app_download.app_store_url'));
    }
}
