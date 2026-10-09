<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * แจ้งเตือนกดแล้วไปต่อได้ (คอมเมนต์ข้อ 5)
 * - url         : ลิงก์ภายนอก (https://...) ที่แอดมินกรอกเอง → แอปเปิดผ่าน browser/WebView
 * - target_type : ชนิดหน้าภายในแอป เช่น order, service_request, invoice, reward_code, smart_locker_booking, reward, product
 * - target_id   : id ของหน้านั้น → แอป map target_type + target_id ไปหน้าภายในเอง
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'url')) {
                $table->string('url', 2048)->nullable()->after('body');
            }
            if (!Schema::hasColumn('notifications', 'target_type')) {
                $table->string('target_type', 50)->nullable()->after('url');
            }
            if (!Schema::hasColumn('notifications', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            }
        });

        if (!Schema::hasIndex('notifications', 'notifications_user_unread_index')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'is_read'], 'notifications_user_unread_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasIndex('notifications', 'notifications_user_unread_index')) {
                $table->dropIndex('notifications_user_unread_index');
            }
            foreach (['url', 'target_type', 'target_id'] as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
