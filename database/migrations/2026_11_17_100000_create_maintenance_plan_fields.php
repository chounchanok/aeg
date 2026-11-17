<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🌟 แผนบำรุงรักษา (Maintenance Plan)
 * - products          : ค่าตั้งต้นของแผนต่อ "บริการแต่ละตัว" (ระยะสัญญา, จำนวนครั้ง, เข้าทุกกี่เดือน, เปิด ticket ล่วงหน้า, เปิดอัตโนมัติ)
 * - customer_products : แผนของลูกค้าแต่ละราย (คัดลอกจากบริการตอนซื้อ/แอดมินเพิ่ม แล้วปรับรายคนได้)
 * - service_requests  : source = customer (ลูกค้าแจ้งเอง) | scheduled (ระบบเปิดตามรอบ)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'contract_months')) {
                $table->unsignedSmallInteger('contract_months')->nullable()->comment('ระยะสัญญาบริการ (เดือน)');
            }
            if (!Schema::hasColumn('products', 'service_visits')) {
                $table->unsignedSmallInteger('service_visits')->nullable()->comment('จำนวนครั้งเข้าบริการต่อสัญญา');
            }
            if (!Schema::hasColumn('products', 'visit_interval_months')) {
                $table->unsignedSmallInteger('visit_interval_months')->nullable()->comment('เข้าบริการทุกกี่เดือน');
            }
            if (!Schema::hasColumn('products', 'ticket_lead_days')) {
                $table->unsignedSmallInteger('ticket_lead_days')->default(10)->comment('เปิด ticket ล่วงหน้ากี่วัน');
            }
            if (!Schema::hasColumn('products', 'auto_schedule')) {
                $table->boolean('auto_schedule')->default(false)->comment('สร้าง ticket ตามรอบอัตโนมัติ');
            }
        });

        Schema::table('customer_products', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_products', 'visit_interval_months')) {
                $table->unsignedSmallInteger('visit_interval_months')->nullable();
            }
            if (!Schema::hasColumn('customer_products', 'next_service_date')) {
                $table->date('next_service_date')->nullable()->index();
            }
            if (!Schema::hasColumn('customer_products', 'preferred_day')) {
                $table->unsignedTinyInteger('preferred_day')->nullable()->comment('วันที่ของเดือนที่สะดวก 1-28');
            }
            if (!Schema::hasColumn('customer_products', 'preferred_time_slot')) {
                $table->string('preferred_time_slot', 20)->nullable();
            }
            if (!Schema::hasColumn('customer_products', 'maintenance_technician_id')) {
                $table->unsignedBigInteger('maintenance_technician_id')->nullable()->comment('ช่างประจำ');
            }
            if (!Schema::hasColumn('customer_products', 'maintenance_address_id')) {
                $table->unsignedBigInteger('maintenance_address_id')->nullable()->comment('ที่อยู่เข้าบริการ');
            }
            if (!Schema::hasColumn('customer_products', 'ticket_lead_days')) {
                $table->unsignedSmallInteger('ticket_lead_days')->nullable();
            }
            if (!Schema::hasColumn('customer_products', 'auto_schedule')) {
                $table->boolean('auto_schedule')->default(false);
            }
            if (!Schema::hasColumn('customer_products', 'maintenance_note')) {
                $table->string('maintenance_note', 500)->nullable();
            }
        });

        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'source')) {
                $table->string('source', 20)->default('customer')->comment('customer | scheduled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'source')) $table->dropColumn('source');
        });
        Schema::table('customer_products', function (Blueprint $table) {
            foreach (['visit_interval_months', 'next_service_date', 'preferred_day', 'preferred_time_slot', 'maintenance_technician_id',
                      'maintenance_address_id', 'ticket_lead_days', 'auto_schedule', 'maintenance_note'] as $c) {
                if (Schema::hasColumn('customer_products', $c)) $table->dropColumn($c);
            }
        });
        Schema::table('products', function (Blueprint $table) {
            foreach (['contract_months', 'service_visits', 'visit_interval_months', 'ticket_lead_days', 'auto_schedule'] as $c) {
                if (Schema::hasColumn('products', $c)) $table->dropColumn($c);
            }
        });
    }
};
