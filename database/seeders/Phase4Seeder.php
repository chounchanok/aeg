<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Phase4Seeder extends Seeder
{
    public function run(): void
    {
        $customer = DB::table('users')->where('username', 'customer01')->first();

        if ($customer) {
            $product = DB::table('customer_products')->where('customer_id', $customer->id)->first();
            $address = DB::table('customer_addresses')->where('user_id', $customer->id)->first();

            if ($product && $address) {
                // ใบที่ 1
                $requestId1 = DB::table('service_requests')->insertGetId([
                    'ticket_number' => 'SR-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                    'customer_id' => $customer->id, // อัปเดตเป็น customer_id
                    'service_type' => 'Repair', // เพิ่ม service_type
                    'customer_product_id' => $product->id,
                    'problem_description' => 'กล้องวงจรปิดตัวหน้าบ้านภาพดับไปเลยครับ รีสตาร์ทแล้วไม่หาย',
                    'address_id' => $address->id,
                    'custom_address_text' => null,
                    'preferred_date' => Carbon::now()->addDays(2)->toDateString(),
                    'time_slot' => '09:00-12:00',
                    'status' => 'pending',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                DB::table('service_request_images')->insert([
                    ['service_request_id' => $requestId1, 'image_url' => 'https://example.com/images/broken_cctv_1.jpg', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
                    ['service_request_id' => $requestId1, 'image_url' => 'https://example.com/images/broken_cctv_2.jpg', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
                ]);

                // ใบที่ 2
                $requestId2 = DB::table('service_requests')->insertGetId([
                    'ticket_number' => 'SR-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                    'customer_id' => $customer->id, // อัปเดตเป็น customer_id
                    'service_type' => 'Maintenance', // เพิ่ม service_type
                    'customer_product_id' => $product->id,
                    'problem_description' => 'สัญญาณกันขโมยดังเองตอนกลางคืนบ่อยมาก',
                    'address_id' => null,
                    'custom_address_text' => 'บ้านพักตากอากาศ หัวหิน ซอย 112',
                    'preferred_date' => Carbon::now()->toDateString(),
                    'time_slot' => '13:00-18:00',
                    'status' => 'in_progress',
                    'created_at' => Carbon::now()->subDays(1),
                    'updated_at' => Carbon::now()->subDays(1),
                ]);

                DB::table('service_request_images')->insert([
                    'service_request_id' => $requestId2,
                    'image_url' => 'https://example.com/images/alarm_error.jpg',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}