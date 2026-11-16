<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 🌟 จำนวนคนในทีม (ใช้คำนวณยอดสแกนต่อคน) + ข้อมูลทีมเริ่มต้น 12 ทีม (98 คน)
 * รันซ้ำได้ — ทีมที่มีรหัสอยู่แล้วจะอัปเดตชื่อ/จำนวนคน ไม่สร้างซ้ำ
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_download_teams', function (Blueprint $table) {
            if (!Schema::hasColumn('app_download_teams', 'member_count')) {
                $table->unsignedInteger('member_count')->default(0)->after('name');
            }
        });

        $teams = [
            // [รหัสทีม, ชื่อทีม, จำนวนคน]
            ['TEAM01', 'CMS & IT', 8],
            ['TEAM02', 'Admin & Resource Support (Stock, Purchasing, Locker)', 5],
            ['TEAM03', 'Insurance', 4],
            ['TEAM04', 'Accounting Department (AETH)', 7],
            ['TEAM05', 'Human Resources (HR)', 4],
            ['TEAM06', 'Sales', 16],
            ['TEAM07', 'Service Support (Messenger, F&B, Tenant Services)', 5],
            ['TEAM08', 'Accounting Department (AESB) + Marketing', 6],
            ['TEAM09', 'Operation', 6],
            ['TEAM10', 'SEC – Installation Team', 18],
            ['TEAM11', 'SEC – Cooperation Team', 11],
            ['TEAM12', 'SEC – Support Team', 8],
        ];

        foreach ($teams as [$code, $name, $members]) {
            $exists = DB::table('app_download_teams')->where('code', $code)->exists();
            if ($exists) {
                DB::table('app_download_teams')->where('code', $code)->update([
                    'name' => $name, 'member_count' => $members, 'updated_at' => now(),
                ]);
            } else {
                DB::table('app_download_teams')->insert([
                    'name' => $name, 'code' => $code, 'member_count' => $members,
                    'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('app_download_teams', function (Blueprint $table) {
            if (Schema::hasColumn('app_download_teams', 'member_count')) {
                $table->dropColumn('member_count');
            }
        });
    }
};
