<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppDownloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 🌟 เมนู "QR ดาวน์โหลดแอป (ทีม)" — สร้างทีม, QR ของแต่ละทีม และ Dashboard จัดอันดับยอดดาวน์โหลด
 */
class AppDownloadAdminController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        if ($request->query('range') === '30d') { $from = now()->subDays(29)->toDateString(); $to = now()->toDateString(); }
        if ($request->query('range') === 'month') { $from = now()->startOfMonth()->toDateString(); $to = now()->toDateString(); }
        $showInstalls = (bool) config('app_download.show_installs');
        $sort = $request->query('sort', $showInstalls ? 'installs' : 'scans');
        if (!$showInstalls && in_array($sort, ['installs', 'installs_per_member'], true)) $sort = 'scans';

        $board = AppDownloadService::leaderboard($from, $to, $sort);

        // กราฟรายวัน 30 วันล่าสุด (สแกน / ติดตั้ง)
        $since = now()->subDays(29)->startOfDay();
        $scanDaily = DB::table('app_download_clicks')->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');
        $installDaily = DB::table('app_installs')->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');
        $daily = [];
        for ($d = $since->copy(); $d->lte(now()); $d->addDay()) {
            $k = $d->toDateString();
            $daily[] = ['date' => $d->format('d/m'), 'scans' => (int) ($scanDaily[$k] ?? 0), 'installs' => (int) ($installDaily[$k] ?? 0)];
        }

        return view('admin.app-downloads.index', [
            'rows' => $board['rows'],
            'totals' => $board['totals'],
            'daily' => $daily,
            'from' => $from,
            'to' => $to,
            'sort' => $sort,
            'showInstalls' => $showInstalls,
            'first_level_active_index' => 'app-downloads',
            'second_level_active_index' => '',
            'third_level_active_index' => '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $code = $data['code'] ? AppDownloadService::makeCode($data['code']) : AppDownloadService::makeCode($data['name']);

        DB::table('app_download_teams')->insert([
            'name' => $data['name'],
            'member_count' => (int) ($data['member_count'] ?? 0),
            'code' => $code,
            'description' => $data['description'] ?? null,
            'is_active' => true,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'สร้างทีม "' . $data['name'] . '" แล้ว — ลิงก์ ' . url('/d/' . $code));
    }

    public function update(Request $request, $id)
    {
        $team = DB::table('app_download_teams')->where('id', $id)->first();
        abort_unless($team, 404);
        $data = $this->validated($request);

        $update = [
            'name' => $data['name'],
            'member_count' => (int) ($data['member_count'] ?? 0),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'updated_at' => now(),
        ];
        // ⚠️ เปลี่ยนรหัสลิงก์ = QR เดิมที่พิมพ์ไปแล้วจะใช้ไม่ได้ (ไม่นับยอด) จึงเปลี่ยนเฉพาะเมื่อกรอกมาและไม่ซ้ำ
        if (!empty($data['code']) && $data['code'] !== $team->code) {
            $update['code'] = AppDownloadService::makeCode($data['code'], (int) $id);
        }
        DB::table('app_download_teams')->where('id', $id)->update($update);

        return back()->with('success', 'บันทึกทีม "' . $data['name'] . '" แล้ว');
    }

    public function destroy($id)
    {
        $team = DB::table('app_download_teams')->where('id', $id)->first();
        abort_unless($team, 404);
        // ลบทีม = ลบประวัติสแกนของทีม (การติดตั้งยังอยู่ แต่ไม่ผูกทีม) — ถ้าแค่หยุดใช้ ให้ "ปิดใช้งาน" แทน
        DB::table('app_download_teams')->where('id', $id)->delete();
        return back()->with('success', 'ลบทีม "' . $team->name . '" แล้ว');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'member_count' => 'nullable|integer|min:0|max:100000',
            'code' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9\-]+$/'],
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'กรุณากรอกชื่อทีม',
            'code.regex' => 'รหัสลิงก์ใช้ได้เฉพาะ a-z, 0-9 และ -',
        ]);
    }
}
