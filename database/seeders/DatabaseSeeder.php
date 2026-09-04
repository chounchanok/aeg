<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. สร้าง Loyalty Tiers (ตาม TOR)
        $tiers = [
            ['name' => 'Advance', 'min_spending' => 0, 'point_multiplier' => 1.00],
            ['name' => 'Platinum', 'min_spending' => 200000, 'point_multiplier' => 1.20],
            ['name' => 'Beyond', 'min_spending' => 1000000, 'point_multiplier' => 1.50],
        ];
        DB::table('loyalty_tiers')->insert($tiers);
        $advanceTierId = DB::table('loyalty_tiers')->where('name', 'Advance')->value('id');

        // 2. สร้าง Users (Admin, Technician, Customer)
        $password = Hash::make('password'); // Password เดียวกันหมดเพื่อง่ายต่อการเทส

        // 2.1 Admin (พี่แชมเปญ)
        $adminId = DB::table('users')->insertGetId([
            'username' => 'champagne_admin',
            'email' => 'admin@aeg.co.th',
            'password' => $password,
            'role' => 'super_admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2.2 Technician (ช่างสมชาย)
        $techId = DB::table('users')->insertGetId([
            'username' => 'somchai_tech',
            'email' => 'tech@aeg.co.th',
            'password' => $password,
            'role' => 'technician',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('technician_profiles')->insert([
            'user_id' => $techId,
            'employee_id' => 'TECH-001',
            'skills' => json_encode(['CCTV', 'Alarm System', 'Wiring']),
            'current_lat' => 13.7563,
            'current_long' => 100.5018,
            'is_available' => true,
        ]);

        // 2.3 Customer (คุณลูกค้า)
        $custId = DB::table('users')->insertGetId([
            'username' => 'customer01',
            'email' => 'customer@gmail.com',
            'password' => $password,
            'role' => 'customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customer_profiles')->insert([
            'user_id' => $custId,
            'first_name' => 'ใจดี',
            'last_name' => 'มีตังค์',
            'phone' => '0812345678',
            'address' => '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ',
            'tax_id' => '1234567890123',
        ]);

        // 3. สร้าง Wallet ให้ลูกค้า
        DB::table('customer_wallets')->insert([
            'user_id' => $custId,
            'current_tier_id' => $advanceTierId,
            'total_spending' => 5000.00,
            'current_points' => 500,
        ]);

        // 4. สินค้าของลูกค้า (Customer Products)
        DB::table('customer_products')->insert([
            'customer_id' => $custId,
            'product_name' => 'AEG Smart CCTV Model X',
            'serial_number' => 'SN-2026-998877',
            'purchase_date' => Carbon::now()->subMonths(3),
            'warranty_expire_date' => Carbon::now()->addMonths(9),
            'status' => 'active',
            'policy_document_url' => 'https://example.com/policy/123.pdf',
        ]);

        // 5. จำลอง Flow การแจ้งซ่อม (Service Request -> Job -> Payment)

        // 5.1 ใบแจ้งซ่อมที่ "เสร็จแล้ว"
        $reqId = DB::table('service_requests')->insertGetId([
            'customer_id' => $custId,
            'service_type' => 'Maintenance',
            'description' => 'กล้องตัวที่ 3 หน้าบ้านภาพล้ม',
            'location_lat' => 13.7200,
            'location_long' => 100.5500,
            'site_address' => 'บ้านลูกค้า สุขุมวิท',
            'preferred_date' => Carbon::now()->subDays(2),
            'status' => 'completed',
            'created_at' => Carbon::now()->subDays(5),
        ]);

        // 5.2 สร้าง Job งานซ่อม
        $jobId = DB::table('jobs')->insertGetId([
            'service_request_id' => $reqId,
            'technician_id' => $techId,
            'assigned_by' => $adminId,
            'started_at' => Carbon::now()->subDays(2)->hour(10),
            'completed_at' => Carbon::now()->subDays(2)->hour(12),
            'customer_rating' => 5,
            'customer_comment' => 'ช่างบริการดีมากครับ',
        ]);

        // 5.3 รูปภาพงาน (Before/After)
        DB::table('job_photos')->insert([
            ['job_id' => $jobId, 'photo_url' => 'http://img.com/before.jpg', 'type' => 'before'],
            ['job_id' => $jobId, 'photo_url' => 'http://img.com/after.jpg', 'type' => 'after'],
        ]);

        // 5.4 การจ่ายเงิน (Payment)
        DB::table('payments')->insert([
            'job_id' => $jobId,
            'amount' => 1500.00,
            'payment_method' => 'qr_code',
            'payment_status' => 'paid',
            'transaction_ref' => 'TXN-888888',
            'payment_date' => Carbon::now()->subDays(2)->hour(12)->minute(5),
            'receipt_url' => 'http://doc.com/receipt.pdf',
        ]);

        // 5.5 Transaction แต้ม (ได้แต้มจากการจ่ายเงิน)
        DB::table('point_transactions')->insert([
            'user_id' => $custId,
            'amount' => 150, // สมมติ 10 บาท = 1 แต้ม
            'type' => 'earn',
            'reference_id' => 'JOB-' . $jobId,
            'description' => 'ได้รับแต้มจากงานซ่อม',
            'created_at' => Carbon::now(),
        ]);

        // 6. ข่าวสาร (CMS)
        DB::table('contents')->insert([
            'title' => 'เปิดตัวแอปพลิเคชัน AEG ใหม่!',
            'slug' => 'launch-new-aeg-app',
            'category' => 'news',
            'body' => '<p>ยินดีต้อนรับสู่แอปพลิเคชันใหม่ของเรา...</p>',
            'status' => 'published',
            'author_id' => $adminId,
            'published_at' => Carbon::now(),
            'created_at' => Carbon::now(),
        ]);

        // สร้างใบแจ้งซ่อมที่ยัง "รอคิว" (Pending) ไว้ทดสอบ
        DB::table('service_requests')->insert([
            'customer_id' => $custId,
            'service_type' => 'Installation',
            'description' => 'ติดตัั้งสัญญาณกันขโมยเพิ่ม 2 จุด',
            'location_lat' => 13.7200,
            'location_long' => 100.5500,
            'site_address' => 'บ้านลูกค้า สุขุมวิท',
            'preferred_date' => Carbon::now()->addDays(3),
            'status' => 'pending',
            'created_at' => Carbon::now(),
        ]);

        $this->call([
            Phase1Seeder::class, // Banners & Rewards
            Phase2Seeder::class, // Products & Cart
            Phase3Seeder::class, // Notifications & Device Token
            Phase4Seeder::class, // Service Requests (ที่มีรูปภาพ)
            Phase5Seeder::class,  // FAQs & Chats
            RolePermissionSeeder::class, // 🌟 RBAC: สร้างแผนก (roles) และสิทธิ์ (permissions)
            StaffTestSeeder::class,      // 🌟 RBAC: user ทดสอบ 1 คนต่อแผนก สำหรับทดสอบสิทธิ์
        ]);
    }
}
