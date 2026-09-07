<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบ "รายการติดต่อจากลูกค้า" ในหลังบ้าน (/admin/contacts/{type}) — รวมฟอร์มติดต่อทุกช่องทาง
 * ให้แต่ละแผนกดู/ดำเนินการเฉพาะของตัวเอง (RBAC) และเก็บประวัติว่าดำเนินการอะไรไปแล้วบ้าง:
 *
 *   type       ตาราง                   แผนกที่ดูแล (permission)
 *   insurance  insurance_contacts       Insurance     (contacts.insurance)
 *   safe       safe_contacts            Smart Locker  (contacts.safe)
 *   product    product_contacts         Sales Admin   (contacts.product)
 *   sales      contact_admin_requests   Sales Admin   (contacts.sales)  ← ฟอร์ม "ติดต่อฝ่ายขาย" จากแอป
 *
 * 1) contact_activities — timeline การดำเนินการของแต่ละรายการ (เปลี่ยนสถานะ / มอบหมาย / โทร / อีเมล / บันทึก)
 * 2) เพิ่ม assigned_to (พนักงานที่รับผิดชอบ) ให้ตารางติดต่อทั้ง 4 ตาราง
 */
return new class extends Migration
{
    private array $contactTables = ['insurance_contacts', 'safe_contacts', 'product_contacts', 'contact_admin_requests'];

    public function up(): void
    {
        if (!Schema::hasTable('contact_activities')) {
            Schema::create('contact_activities', function (Blueprint $table) {
                $table->id();
                $table->string('contact_type', 20)->comment('insurance | safe | product | sales');
                $table->unsignedBigInteger('contact_id')->comment('id ในตารางติดต่อของ type นั้นๆ');
                $table->unsignedBigInteger('staff_id')->nullable()->comment('users.id ของพนักงานที่ทำรายการ');
                $table->string('action', 30)->comment('status_changed | assigned | call | email | chat | appointment | note');
                $table->string('from_status', 20)->nullable();
                $table->string('to_status', 20)->nullable();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['contact_type', 'contact_id']);
                $table->index('staff_id');
            });
        }

        foreach ($this->contactTables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'assigned_to')) {
                    $table->unsignedBigInteger('assigned_to')->nullable()->after('status')->comment('users.id ของพนักงานที่รับผิดชอบรายการนี้');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->contactTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'assigned_to')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('assigned_to'));
            }
        }

        Schema::dropIfExists('contact_activities');
    }
};
