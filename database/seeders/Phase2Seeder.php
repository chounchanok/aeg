<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Phase2Seeder extends Seeder
{
    public function run(): void
    {
        // 1. สร้างสินค้าและบริการ (Products / Packages)
        $products = [
            [
                'name' => 'แพ็กเกจดูแลรักษากล้องวงจรปิด (รายปี)',
                'description' => 'บริการทำความสะอาดและตรวจเช็คสถานะการทำงานของกล้องวงจรปิด จำนวนสูงสุด 8 ตัว เข้าบริการ 2 ครั้ง/ปี',
                'type' => 'package',
                'price' => 2990.00,
                'compare_at_price' => 3500.00,
                'image_url' => 'https://example.com/products/cctv_maintenance.jpg',
                'point_earn' => 299,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'บริการติดตั้งระบบสัญญาณกันขโมย (จุดละ)',
                'description' => 'บริการเดินสายและติดตั้งเซ็นเซอร์กันขโมยตามจุดต่างๆ ภายในบ้าน (ราคานี้ไม่รวมค่าอุปกรณ์เสริม)',
                'type' => 'service',
                'price' => 500.00,
                'compare_at_price' => null,
                'image_url' => 'https://example.com/products/alarm_install.jpg',
                'point_earn' => 50,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'AEG Smart IP Camera รุ่น 360',
                'description' => 'กล้องวงจรปิดไร้สาย ความละเอียด 2K หมุนได้ 360 องศา ดูผ่านแอปได้เรียลไทม์',
                'type' => 'equipment',
                'price' => 1290.00,
                'compare_at_price' => 1590.00,
                'image_url' => 'https://example.com/products/ip_camera.jpg',
                'point_earn' => 129,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];
        DB::table('products')->insert($products);

        // ดึงข้อมูล User 'customer01' ที่สร้างไว้จาก Phase 1 มาผูกข้อมูล
        $customer = DB::table('users')->where('username', 'customer01')->first();

        if ($customer) {
            // 2. สร้างที่อยู่ลูกค้า
            $addressId = DB::table('customer_addresses')->insertGetId([
                'user_id' => $customer->id,
                'title' => 'บ้าน (ที่อยู่หลัก)',
                'contact_name' => 'ใจดี มีตังค์',
                'contact_phone' => '0812345678',
                'address_line' => '123/45 หมู่บ้านสุขสันต์ ซอยสุขุมวิท 101',
                'province' => 'กรุงเทพมหานคร',
                'district' => 'พระโขนง',
                'subdistrict' => 'บางจาก',
                'zipcode' => '10260',
                'lat' => 13.6920,
                'lng' => 100.6050,
                'is_default' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 3. สร้างตะกร้าสินค้าแบบมีของอยู่แล้ว 1 ชิ้น เพื่อไว้เทส API getCart
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $customer->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_id' => 2, // ใส่บริการติดตั้งไป 2 จุด
                'quantity' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 4. สร้างประวัติคำสั่งซื้อจำลอง เพื่อไว้เทส API getMyOrders
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => 'ORD-' . date('Ym') . '-TST001',
                'user_id' => $customer->id,
                'address_id' => $addressId,
                'subtotal' => 2990.00,
                'discount' => 0.00,
                'total_amount' => 2990.00,
                'status' => 'completed',
                'payment_gateway' => 'omise',
                'gateway_transaction_id' => 'chrg_test_12345',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ]);

            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => 1,
                'product_name' => 'แพ็กเกจดูแลรักษากล้องวงจรปิด (รายปี)',
                'price' => 2990.00,
                'quantity' => 1,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ]);
        }
    }
}