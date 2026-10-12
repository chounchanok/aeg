<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ปรับ process ของรางวัลประเภทสินค้า: ลูกค้าระบุที่อยู่ครบ = "ยืนยันการจัดส่ง" ทันที
 * รายการเก่าที่ยังเป็น active แต่มีชื่อ เบอร์ และที่อยู่ครบแล้ว → เปลี่ยนเป็น shipping_confirm ให้แอดมินจัดส่งต่อได้เลย
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_reward_code_logs')) return;

        $rows = DB::table('customer_reward_codes as c')
            ->join('rewards as r', 'c.reward_id', '=', 'r.id')
            ->where('r.reward_type', 'product')
            ->where('c.status', 'active')
            ->where(fn ($q) => $q->whereNotNull('c.address_id')->orWhere(fn ($w) => $w->whereNotNull('c.address_text')->where('c.address_text', '!=', '')))
            ->whereNotNull('c.customer_name')->where('c.customer_name', '!=', '')
            ->whereNotNull('c.customer_phone')->where('c.customer_phone', '!=', '')
            ->pluck('c.id');

        foreach ($rows as $id) {
            DB::table('customer_reward_codes')->where('id', $id)->update([
                'status' => 'shipping_confirm',
                'requested_at' => DB::raw('COALESCE(requested_at, created_at)'),
                'updated_at' => now(),
            ]);
            DB::table('customer_reward_code_logs')->insert([
                'customer_reward_code_id' => $id,
                'status' => 'shipping_confirm',
                'note' => 'ระบุที่อยู่จัดส่งแล้วตอนแลก — ปรับเป็นยืนยันการจัดส่ง (ปรับ process)',
                'actor' => 'system',
                'changed_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // ไม่ย้อนสถานะกลับ (อาจมีการจัดส่งต่อไปแล้ว)
    }
};
