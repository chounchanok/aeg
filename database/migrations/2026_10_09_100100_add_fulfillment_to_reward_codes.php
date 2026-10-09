<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบจัดการคูปอง/ของรางวัลที่ลูกค้าแลก (คอมเมนต์ข้อ 2)
 *
 * rewards.reward_type
 *   - product  : ของรางวัลเป็นสินค้า ต้องจัดส่ง
 *   - voucher  : วอยเชอร์ภายนอก แอดมินกรอกรหัสส่งกลับให้ลูกค้า
 *   - discount : ส่วนลดใช้ตอน checkout ในแอป (ใช้โค้ด RWD-xxxx อัตโนมัติเหมือนเดิม)
 *
 * customer_reward_codes.status
 *   - active            : แลกแล้ว ยังไม่ได้กดใช้
 *   - shipping_confirm  : (สินค้า) ลูกค้ากดใช้แล้ว รอแอดมินตรวจสอบที่อยู่ = "ยืนยันการจัดส่ง"
 *   - processing        : (สินค้า) กำลังดำเนินการ  — เปลี่ยนได้จากหลังบ้านเท่านั้น
 *   - shipping          : (สินค้า) กำลังจัดส่ง      — เปลี่ยนได้จากหลังบ้านเท่านั้น
 *   - delivered         : (สินค้า) จัดส่งสำเร็จ      — เปลี่ยนได้จากหลังบ้านเท่านั้น
 *   - used              : (วอยเชอร์/ส่วนลด) ลูกค้ากดใช้แล้ว หรือใช้ตอน checkout แล้ว
 *   - cancelled         : ยกเลิก (คืนแต้มแล้ว)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            if (!Schema::hasColumn('rewards', 'reward_type')) {
                $table->string('reward_type', 20)->default('product')->after('category_id');
            }
        });

        // ค่าเริ่มต้นตาม logic เดิม: หมวด 1 + มีมูลค่าส่วนลด = คูปองส่วนลด, นอกนั้นเป็นสินค้า
        DB::table('rewards')->where('category_id', 1)->where('discount_amount', '>', 0)->update(['reward_type' => 'discount']);

        // เปลี่ยน status จาก enum('active','used') เป็น string เพื่อรองรับสถานะการจัดส่ง
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE customer_reward_codes MODIFY status VARCHAR(30) NOT NULL DEFAULT 'active'");
        } else {
            Schema::table('customer_reward_codes', function (Blueprint $table) {
                $table->string('status', 30)->default('active')->change();
            });
        }

        Schema::table('customer_reward_codes', function (Blueprint $table) {
            // คอลัมน์ผู้รับ/ที่อยู่ (มีใช้อยู่แล้วในโค้ด — เพิ่มเฉพาะกรณีฐานข้อมูลยังไม่มี)
            if (!Schema::hasColumn('customer_reward_codes', 'customer_name')) {
                $table->string('customer_name')->nullable();
            }
            if (!Schema::hasColumn('customer_reward_codes', 'customer_phone')) {
                $table->string('customer_phone', 30)->nullable();
            }
            if (!Schema::hasColumn('customer_reward_codes', 'address_id')) {
                $table->unsignedBigInteger('address_id')->nullable();
            }
            if (!Schema::hasColumn('customer_reward_codes', 'address_text')) {
                $table->text('address_text')->nullable();
            }

            if (!Schema::hasColumn('customer_reward_codes', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->comment('รหัสวอยเชอร์/ส่วนลดที่แอดมินกรอกส่งกลับให้ลูกค้า');
            }
            if (!Schema::hasColumn('customer_reward_codes', 'voucher_note')) {
                $table->text('voucher_note')->nullable()->comment('วิธีใช้/หมายเหตุที่แสดงคู่กับรหัส');
            }
            if (!Schema::hasColumn('customer_reward_codes', 'voucher_sent_at')) {
                $table->timestamp('voucher_sent_at')->nullable();
            }
            if (!Schema::hasColumn('customer_reward_codes', 'requested_at')) {
                $table->timestamp('requested_at')->nullable()->comment('เวลาที่ลูกค้ากดใช้คูปอง (ขอจัดส่ง)');
            }
            if (!Schema::hasColumn('customer_reward_codes', 'shipping_carrier')) {
                $table->string('shipping_carrier', 100)->nullable();
            }
            if (!Schema::hasColumn('customer_reward_codes', 'tracking_number')) {
                $table->string('tracking_number', 100)->nullable();
            }
            if (!Schema::hasColumn('customer_reward_codes', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable();
            }
            if (!Schema::hasColumn('customer_reward_codes', 'admin_note')) {
                $table->text('admin_note')->nullable();
            }
        });

        if (!Schema::hasTable('customer_reward_code_logs')) {
            Schema::create('customer_reward_code_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_reward_code_id')->constrained('customer_reward_codes')->cascadeOnDelete();
                $table->string('status', 30);
                $table->text('note')->nullable();
                $table->string('actor', 20)->default('admin'); // admin | customer | system
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_reward_code_logs');

        Schema::table('customer_reward_codes', function (Blueprint $table) {
            foreach (['voucher_code', 'voucher_note', 'voucher_sent_at', 'requested_at', 'shipping_carrier', 'tracking_number', 'delivered_at', 'admin_note'] as $column) {
                if (Schema::hasColumn('customer_reward_codes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('rewards', function (Blueprint $table) {
            if (Schema::hasColumn('rewards', 'reward_type')) {
                $table->dropColumn('reward_type');
            }
        });
    }
};
