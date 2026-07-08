<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmartLockerCategorySeeder extends Seeder
{
    public function run(): void
    {
        // ล้างข้อมูลเก่าก่อน (เผื่อรันซ้ำ)
        DB::table('smart_locker_categories')->truncate();

        // 1. ข้อมูล Smart Locker
        $smartLockerId = DB::table('smart_locker_categories')->insertGetId([
            'slug' => 'smartlocker',
            'title_th' => 'AEG Smart Locker',
            'title_en' => 'AEG Smart Locker',
            'description_th' => "AEG Smart Locker\nบริการให้เช่าตู้เซฟนิรภัย ในห้องนิรภัย\nAEG Smart Locker คือบริการเช่าตู้เซฟนิรภัยภายในห้องนิรภัยมาตรฐานสากลที่มีความเป็นส่วนตัวและมีความปลอดภัยสูง ด้วยระบบรักษาความปลอดภัยขั้นสูงที่ออกแบบมาเพื่อเก็บรักษาทรัพย์สินมูลค่าสูงโดยเฉพาะ มาพร้อมระบบความปลอดภัยขั้นสูง ถึง 4 ขั้น ทั้งระบบควบคุมการเข้า — ออกรวมถึงระบบยืนยันตัวตนทางชีวภาพ (Biometrics) 2 ขั้นเต็มรูปแบบ และให้ความเป็นส่วนตัวสูงสุด ภายใต้แนวคิด \"Full Privacy, Maximum Security\"\n\nค่าธรรมเนียมการเช่า (บาท)\nระยะเวลา\nขนาด\n3 เดือน 6 เดือน 9 เดือน 1 ปี 3 ปี 6 ปี\nPrime ขนาดเล็ก (H:5\", L:12\", W:18\")\n4,200.- 8,050.- 11,550.- 14,000.- 39,900.- 42,000.- 75,600.- 84,000.-\n\nPrivilege ขนาดใหญ่ (H:10\", L:12\", W:18\")\n4,800.- 9,200.- 13,200.- 16,000.- 45,600.- 48,000.- 86,400.- 96,000.-\n\nโปรดนำบัตรประชาชนตัวจริง หรือพาสปอร์ต ตัวจริงมาในวันที่ลงทะเบียนที่สาขา\nลูกค้าสามารถเข้ารับบริการได้ภายใน 30 วัน หลังจากชำระค่าบริการ\nสัญญา 3 ปี ได้รับส่วนลด 5% / สัญญา 6 ปี ได้รับส่วนลด 10%\n* ครั้งแรกของการทำสัญญาต้องชำระค่ามัดจำ\nเป็นจำนวน 5,000 บาท (ได้รับคืนเต็มจำนวนเมื่อสิ้นสุดสัญญา)\n* ราคาข้างต้นยังไม่รวมภาษีมูลค่าเพิ่ม 7%",
            
            'description_en' => "AEG Smart Locker\nSecurity Safe Rental Service\nAEG Smart Locker is a rental service for secure, private, and highly secure security safes within internationally standardized vaults. It features advanced security systems designed specifically for storing high-value assets. The lockers come with four levels of security, including access control and a full two-stage biometric authentication system. Providing maximum privacy under the concept of \"Full Privacy, Maximum Security\".\n\nRental Fees (Baht)\nDuration: 3 months, 6 months, 9 months, 1 year, 3 years, 6 years\n\nPrime\nSmall size (H:5\", L:12\", W:18\")\n4,200.- 8,050.- 11,550.- 14,000.- 39,900.- 42,000.- 75,600.- 84,000.-\n\nPrivilege\nLarge size (H:10\", L:12\", W:18\")\n4,800.- 9,200.- 13,200.- 16,000.- 45,600.- 48,000.- 86,400.- 96,000.-\n\nPlease bring your original ID card or passport. The actual person must arrive on the registration day at the branch.\nCustomers can receive services within 30 days after payment.\n\n3-year contract: 5% discount / 6-year contract: 10% discount\n\n* A deposit of 5,000 baht is required for the first contract signing (fully refundable upon contract termination).\n* The above prices do not include 7% VAT.",
            'image_url' => 'https://aeg.champagne.orangeworkshop.info/storage/products/fb90e825349d8bc5474a4af6f2b574fae7e4420c.jpg',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. ข้อมูล Safety Locker
        DB::table('smart_locker_categories')->insert([
            'slug' => 'safetylocker',
            'title_th' => 'แพ็กเกจบริการตู้เซฟนิรภัย',
            'title_en' => 'Security safe deposit box service package.',
            'description_th' => "ตู้เซฟนิรภัยกันไฟ\nป้องกันเอกสารและทรัพย์สินจากความร้อนและเปลวไฟ\nโครงสร้างวัสดุทนไฟ ป้องกันความเสียหายจากอัคคีภัย\nเสริมความปลอดภัยด้วยระบบล็อกหลากหลายรูปแบบ\n\nตู้เซฟนิรภัยกันการเจาะโจรกรรม\nโครงสร้างเหล็กหนา แข็งแรง ทนต่อการงัดแงะและการเจาะทำลาย\nระบบล็อกหลายชั้น เพิ่มความปลอดภัยต่อเหตุการณ์ไม่คาดฝัน\nสามารถติดตั้งยึดกับพื้นหรือผนังเพื่อป้องกันการเคลื่อนย้าย",
            
            'description_en' => "Fireproof Safe\nProtects documents and valuables from heat and flames.\nFire-resistant construction protects against fire damage.\nEnhanced security with multiple locking systems.\n\nBurglar-proof safe.\nThick steel structure, strong and resistant to prying and drilling.\nMulti-layer locking system provides added security against unforeseen events.\nCan be bolted to the floor or wall to prevent movement.",
            'image_url' => 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=1200&q=80',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 🌟 3. อัปเดตตู้ล็อกเกอร์เดิมทั้งหมดที่ยังไม่มีหมวดหมู่ ให้ผูกกับ ID ของ Smart Locker ก่อน (ป้องกันเว็บพัง)
        DB::table('smart_lockers')
            ->whereNull('category_id')
            ->update(['category_id' => $smartLockerId]);
    }
}