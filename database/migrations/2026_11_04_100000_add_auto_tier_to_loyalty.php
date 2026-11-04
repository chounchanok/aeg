<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 🌟 ระดับสมาชิก (Tier) อัตโนมัติ
 * - loyalty_tiers.min_points : แต้มสะสมขั้นต่ำในรอบ 12 เดือน (Advance 0 / Platinum 2,000 / Beyond 10,000)
 * - loyalty_tiers.min_spending : ยอดซื้อขั้นต่ำในรอบ 12 เดือนที่ให้ขึ้น Tier ได้ทันที (ว่าง = ไม่ใช้ยอดซื้อ)
 * - customer_wallets.tier_started_at / tier_expires_at : สถานะอยู่ได้ 1 ปีหลังเปลี่ยน แล้วประเมินใหม่
 * - customer_tier_histories : ประวัติการเปลี่ยนระดับ
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_tiers', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_tiers', 'min_points')) {
                $table->unsignedInteger('min_points')->nullable()->after('name');
            }
        });
        // ยอดซื้อว่างได้ (= Tier นี้ขึ้นได้ด้วยแต้มอย่างเดียว)
        Schema::table('loyalty_tiers', function (Blueprint $table) {
            $table->decimal('min_spending', 12, 2)->nullable()->change();
        });

        $defaults = ['advance' => 0, 'platinum' => 2000, 'beyond' => 10000];
        foreach ($defaults as $name => $points) {
            $tier = DB::table('loyalty_tiers')->whereRaw('LOWER(TRIM(name)) = ?', [$name])->first();
            if ($tier) {
                if ($tier->min_points === null) {
                    DB::table('loyalty_tiers')->where('id', $tier->id)->update(['min_points' => $points, 'updated_at' => now()]);
                }
            } else {
                DB::table('loyalty_tiers')->insert([
                    'name' => ucfirst($name),
                    'min_points' => $points,
                    'min_spending' => $name === 'advance' ? 0 : null,
                    'point_multiplier' => 1.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('customer_wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_wallets', 'tier_started_at')) {
                $table->dateTime('tier_started_at')->nullable()->after('current_tier_id');
            }
            if (!Schema::hasColumn('customer_wallets', 'tier_expires_at')) {
                $table->dateTime('tier_expires_at')->nullable()->after('tier_started_at');
            }
        });

        if (!Schema::hasTable('customer_tier_histories')) {
            Schema::create('customer_tier_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('from_tier_id')->nullable();
                $table->unsignedBigInteger('to_tier_id');
                $table->string('reason', 30); // upgrade_points, upgrade_spending, renew, downgrade, init, admin
                $table->integer('points_12m')->default(0);
                $table->decimal('spending_12m', 12, 2)->default(0);
                $table->dateTime('expires_at')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable(); // แอดมินที่กด (null = ระบบ)
                $table->string('note')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tier_histories');
        Schema::table('customer_wallets', function (Blueprint $table) {
            foreach (['tier_started_at', 'tier_expires_at'] as $c) {
                if (Schema::hasColumn('customer_wallets', $c)) $table->dropColumn($c);
            }
        });
        Schema::table('loyalty_tiers', function (Blueprint $table) {
            if (Schema::hasColumn('loyalty_tiers', 'min_points')) $table->dropColumn('min_points');
        });
    }
};
