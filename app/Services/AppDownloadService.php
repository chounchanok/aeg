<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * 🌟 QR ดาวน์โหลดแอปแยกทีม
 *
 * Flow: ลูกค้าสแกน QR ของทีม → https://app.aeginc.co/d/{code}
 *   → บันทึกการสแกน (แยก iOS/Android) → ส่งต่อไป App Store / Play Store พร้อมแนบรหัสทีม
 *   - Android: ?referrer=utm_source=aeg_qr&utm_campaign={code} → แอปอ่านด้วย Install Referrer API ตอนเปิดครั้งแรก → ยืนยันได้แม่นยำ
 *   - iOS: Apple ไม่ส่ง referrer ให้แอป → จับคู่จาก IP เดียวกับที่สแกน QR ภายใน 24 ชม. (โดยประมาณ)
 *          + แนบ ct={code} (และ pt ถ้าตั้งไว้) ให้ไปดูยอดจริงใน App Store Connect → Analytics → Campaigns
 */
class AppDownloadService
{
    public const PLATFORMS = ['ios', 'android', 'other'];

    public static function detectPlatform(?string $ua): string
    {
        $ua = (string) $ua;
        if (preg_match('/iPhone|iPad|iPod/i', $ua) || (str_contains($ua, 'Macintosh') && str_contains($ua, 'Mobile'))) {
            return 'ios';
        }
        if (stripos($ua, 'Android') !== false) {
            return 'android';
        }
        return 'other';
    }

    /** ไม่นับบอท/ตัวดึง preview ลิงก์ (เช่น ตอนแปะลิงก์ในแชท) */
    public static function isBot(?string $ua): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|curl|wget|python-requests|headless/i', (string) $ua);
    }

    public static function ipHash(?string $ip): ?string
    {
        return $ip ? hash('sha256', $ip . '|' . config('app.key')) : null;
    }

    public static function storeUrl(string $platform, string $code): string
    {
        if ($platform === 'android') {
            $referrer = 'utm_source=aeg_qr&utm_medium=qr&utm_campaign=' . $code;
            return config('app_download.play_store_url') . '&referrer=' . rawurlencode($referrer);
        }
        $url = config('app_download.app_store_url');
        $params = ['ct' => $code, 'mt' => 8];
        if ($pt = config('app_download.app_store_provider_token')) {
            $params = ['pt' => $pt] + $params;
        }
        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
    }

    public static function teamLink($team): string
    {
        return url('/d/' . $team->code);
    }

    public static function recordClick($team, string $platform, ?string $ip, ?string $ua): void
    {
        if (self::isBot($ua)) return;
        DB::table('app_download_clicks')->insert([
            'team_id' => $team->id,
            'platform' => in_array($platform, self::PLATFORMS, true) ? $platform : 'other',
            'ip_hash' => self::ipHash($ip),
            'user_agent' => Str::limit((string) $ua, 490, ''),
            'created_at' => now(),
        ]);
    }

    /** แปลงชื่อทีมเป็นรหัสลิงก์ (a-z 0-9 -) ไม่ซ้ำ */
    public static function makeCode(string $source, ?int $ignoreId = null): string
    {
        // รหัสที่พิมพ์มาถูกรูปแบบอยู่แล้ว (เช่น TEAM01) ใช้ตามนั้น ไม่แปลงเป็นตัวเล็ก
        $base = preg_match('/^[A-Za-z0-9\-]+$/', $source) ? $source : (Str::slug($source) ?: 'team');
        $base = Str::limit($base, 30, '');
        $code = $base;
        $i = 2;
        while (DB::table('app_download_teams')->where('code', $code)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $code = $base . '-' . $i++;
        }
        return $code;
    }

    /** ดึงรหัสทีมจาก install referrer ของ Play Store (utm_campaign=...) */
    public static function codeFromReferrer(?string $referrer): ?string
    {
        if (!$referrer) return null;
        parse_str(urldecode($referrer), $q);
        $code = $q['utm_campaign'] ?? null;
        return $code ? Str::limit(preg_replace('/[^A-Za-z0-9\-_]/', '', $code), 40, '') : null;
    }

    /**
     * แอปรายงานการติดตั้ง (เปิดครั้งแรก) — ส่งซ้ำได้ ไม่นับซ้ำ (unique platform + device_id)
     */
    public static function recordInstall(array $data, ?string $ip, ?int $userId = null): array
    {
        $platform = $data['platform'];
        $existing = DB::table('app_installs')->where('platform', $platform)->where('device_id', $data['device_id'])->first();
        if ($existing) {
            if ($userId && !$existing->user_id) {
                DB::table('app_installs')->where('id', $existing->id)->update(['user_id' => $userId, 'updated_at' => now()]);
            }
            return ['created' => false, 'install' => $existing];
        }

        $team = null;
        $attribution = 'none';

        // 1) Android: Install Referrer จาก Play Store (แม่นยำ)
        if ($code = self::codeFromReferrer($data['install_referrer'] ?? null)) {
            $team = DB::table('app_download_teams')->where('code', $code)->first();
            if ($team) $attribution = 'referrer';
        }
        // 2) แอปส่งรหัสทีมมาตรงๆ (เช่น ให้ผู้ใช้กรอกรหัสพนักงาน/ทีมแนะนำ)
        if (!$team && !empty($data['team_code'])) {
            $team = DB::table('app_download_teams')->where('code', $data['team_code'])->first();
            if ($team) $attribution = 'team_code';
        }
        // 3) iOS: จับคู่กับการสแกน QR บน iPhone จาก IP เดียวกันล่าสุด (โดยประมาณ)
        $ipHash = self::ipHash($ip);
        if (!$team && $platform === 'ios' && $ipHash) {
            $click = DB::table('app_download_clicks')
                ->where('platform', 'ios')->where('ip_hash', $ipHash)
                ->where('created_at', '>=', now()->subHours(config('app_download.ios_match_hours', 24)))
                ->orderByDesc('created_at')->first();
            if ($click) {
                $team = DB::table('app_download_teams')->where('id', $click->team_id)->first();
                $attribution = 'ip_match';
            }
        }

        $id = DB::table('app_installs')->insertGetId([
            'platform' => $platform,
            'device_id' => $data['device_id'],
            'team_id' => $team->id ?? null,
            'attribution' => $team ? $attribution : 'none',
            'install_referrer' => isset($data['install_referrer']) ? Str::limit($data['install_referrer'], 490, '') : null,
            'app_version' => $data['app_version'] ?? null,
            'ip_hash' => $ipHash,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['created' => true, 'install' => DB::table('app_installs')->find($id), 'team' => $team];
    }

    /**
     * สรุปยอดแยกทีม เรียงอันดับ 1, 2, 3, ... (sort: scans | installs | unique)
     */
    public static function leaderboard(?string $from, ?string $to, string $sort = 'installs')
    {
        $range = function ($q, $col) use ($from, $to) {
            if ($from) $q->where($col, '>=', $from . ' 00:00:00');
            if ($to) $q->where($col, '<=', $to . ' 23:59:59');
        };

        $clicks = DB::table('app_download_clicks')->tap(fn ($q) => $range($q, 'created_at'))
            ->groupBy('team_id')
            ->select('team_id',
                DB::raw('COUNT(*) as scans'),
                DB::raw("SUM(CASE WHEN platform = 'ios' THEN 1 ELSE 0 END) as scans_ios"),
                DB::raw("SUM(CASE WHEN platform = 'android' THEN 1 ELSE 0 END) as scans_android"),
                DB::raw("SUM(CASE WHEN platform = 'other' THEN 1 ELSE 0 END) as scans_other"),
                DB::raw('COUNT(DISTINCT ip_hash) as scans_unique'))
            ->get()->keyBy('team_id');

        $installs = DB::table('app_installs')->whereNotNull('team_id')->tap(fn ($q) => $range($q, 'created_at'))
            ->groupBy('team_id')
            ->select('team_id',
                DB::raw('COUNT(*) as installs'),
                DB::raw("SUM(CASE WHEN platform = 'android' THEN 1 ELSE 0 END) as installs_android"),
                DB::raw("SUM(CASE WHEN platform = 'ios' THEN 1 ELSE 0 END) as installs_ios"))
            ->get()->keyBy('team_id');

        $rows = DB::table('app_download_teams')->orderBy('code')->orderBy('id')->get()->map(function ($t) use ($clicks, $installs) {
            $c = $clicks[$t->id] ?? null;
            $i = $installs[$t->id] ?? null;
            $t->scans = (int) ($c->scans ?? 0);
            $t->scans_ios = (int) ($c->scans_ios ?? 0);
            $t->scans_android = (int) ($c->scans_android ?? 0);
            $t->scans_other = (int) ($c->scans_other ?? 0);
            $t->scans_unique = (int) ($c->scans_unique ?? 0);
            $t->installs = (int) ($i->installs ?? 0);
            $t->installs_android = (int) ($i->installs_android ?? 0);
            $t->installs_ios = (int) ($i->installs_ios ?? 0);
            $t->link = self::teamLink($t);
            $t->member_count = (int) ($t->member_count ?? 0);
            $t->scans_per_member = $t->member_count > 0 ? round($t->scans / $t->member_count, 2) : 0;
            $t->installs_per_member = $t->member_count > 0 ? round($t->installs / $t->member_count, 2) : 0;
            return $t;
        });

        $key = in_array($sort, ['scans', 'installs', 'scans_unique', 'scans_per_member', 'installs_per_member'], true) ? $sort : 'installs';
        $rows = $rows->sort(function ($a, $b) use ($key) {
            return [$b->$key, $b->scans, $a->id] <=> [$a->$key, $a->scans, $b->id];
        })->values();

        // อันดับ (คะแนนเท่ากัน = อันดับเดียวกัน)
        $rank = 0; $prev = null;
        foreach ($rows as $i => $r) {
            if ($prev === null || $r->$key !== $prev) { $rank = $i + 1; $prev = $r->$key; }
            $r->rank = $rank;
        }

        $allInstalls = DB::table('app_installs')->tap(fn ($q) => $range($q, 'created_at'))
            ->select(DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN platform = 'android' THEN 1 ELSE 0 END) as android"),
                DB::raw("SUM(CASE WHEN platform = 'ios' THEN 1 ELSE 0 END) as ios"),
                DB::raw('SUM(CASE WHEN team_id IS NULL THEN 1 ELSE 0 END) as no_team'))
            ->first();

        return [
            'rows' => $rows,
            'totals' => [
                'members' => $rows->sum('member_count'),
                'scans' => $rows->sum('scans'),
                'scans_ios' => $rows->sum('scans_ios'),
                'scans_android' => $rows->sum('scans_android'),
                'scans_other' => $rows->sum('scans_other'),
                'scans_unique' => $rows->sum('scans_unique'),
                'installs' => $rows->sum('installs'),
                'installs_android' => $rows->sum('installs_android'),
                'installs_ios' => $rows->sum('installs_ios'),
                'app_installs_all' => (int) ($allInstalls->total ?? 0),
                'app_installs_android' => (int) ($allInstalls->android ?? 0),
                'app_installs_ios' => (int) ($allInstalls->ios ?? 0),
                'app_installs_no_team' => (int) ($allInstalls->no_team ?? 0),
            ],
        ];
    }
}
