<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * รูปภาษาอังกฤษสำหรับแบนเนอร์และป๊อปอัพ (คอมเมนต์ข้อ 9)
 * ถ้าไม่ได้อัปโหลดรูปอังกฤษ API จะ fallback ไปใช้รูปภาษาไทยให้อัตโนมัติ
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'image_url_en')) {
                $table->string('image_url_en')->nullable()->after('image_url');
            }
            if (!Schema::hasColumn('banners', 'image_url_m_en')) {
                // image_url_m ถูกเพิ่มนอก migration (2026_07_15 ว่างเปล่า) — ใช้ after() เฉพาะเมื่อมีคอลัมน์นี้จริง
                $column = $table->string('image_url_m_en')->nullable();
                if (Schema::hasColumn('banners', 'image_url_m')) {
                    $column->after('image_url_m');
                }
            }
        });

        Schema::table('popup_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('popup_ads', 'image_url_en')) {
                $table->string('image_url_en')->nullable()->after('image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            foreach (['image_url_en', 'image_url_m_en'] as $column) {
                if (Schema::hasColumn('banners', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('popup_ads', function (Blueprint $table) {
            if (Schema::hasColumn('popup_ads', 'image_url_en')) {
                $table->dropColumn('image_url_en');
            }
        });
    }
};
