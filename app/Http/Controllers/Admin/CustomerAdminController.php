<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CustomerAdminController extends Controller
{
    // 1. หน้าแสดงรายชื่อลูกค้าทั้งหมด
    public function index()
    {
        $customers = DB::table('users')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->where('users.role', 'customer')
            ->select('users.id', 'users.username', 'users.email', 'users.phone', 'customer_profiles.first_name', 'customer_profiles.last_name', 'users.created_at')
            ->orderBy('users.created_at', 'desc')
            ->get();

        return view('admin.customers.index', [
            'customers' => $customers,
            'first_level_active_index' => 'customers',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 2. หน้าดูรายละเอียดลูกค้าและสินค้าที่ลูกค้ามี
    public function show($id)
    {
        $customer = DB::table('users')->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')->where('users.id', $id)->select('users.*', 'customer_profiles.first_name', 'customer_profiles.last_name', 'customer_profiles.address', 'customer_profiles.profile_image_url')->first();
        if (!$customer) abort(404);

        $customerProducts = DB::table('customer_products')->where('customer_id', $id)->orderBy('created_at', 'desc')->get();
        $masterProducts = DB::table('products')->where('is_active', true)->get();

        // 🌟 ดึงข้อมูล Master ของประกันและตู้เซฟ
        $masterInsurances = DB::table('insurances')->get();
        $masterLockers = DB::table('smart_lockers')->get();

        // 🌟 ข้อมูลแต้มสะสม (wallet + tier)
        $wallet = DB::table('customer_wallets')
            ->leftJoin('loyalty_tiers', 'customer_wallets.current_tier_id', '=', 'loyalty_tiers.id')
            ->where('customer_wallets.user_id', $id)
            ->select('customer_wallets.current_points', 'customer_wallets.member_id', 'loyalty_tiers.name as tier_name')
            ->first();

        // 🌟 ประวัติการได้รับ/ใช้แต้ม (ล่าสุด 100 รายการ)
        $pointHistory = DB::table('point_transactions')
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // 🌟 ของรางวัล/คูปองที่ลูกค้าแลกไปแล้วทั้งหมด (ทั้งที่ใช้แล้วและยังไม่ได้ใช้)
        $redeemedRewards = DB::table('customer_reward_codes')
            ->join('rewards', 'customer_reward_codes.reward_id', '=', 'rewards.id')
            ->where('customer_reward_codes.user_id', $id)
            ->select(
                'customer_reward_codes.id',
                'customer_reward_codes.code',
                'customer_reward_codes.status',
                'customer_reward_codes.discount_amount',
                'customer_reward_codes.used_at',
                'customer_reward_codes.created_at as redeemed_at',
                'rewards.title_th as reward_title',
                'rewards.image_url'
            )
            ->orderBy('customer_reward_codes.created_at', 'desc')
            ->get();

        return view('admin.customers.show', [
            'customer' => $customer,
            'customerProducts' => $customerProducts,
            'masterProducts' => $masterProducts,
            'masterInsurances' => $masterInsurances, // 🌟 ส่งตัวแปรเพิ่ม
            'masterLockers' => $masterLockers,       // 🌟 ส่งตัวแปรเพิ่ม
            'wallet' => $wallet,
            'pointHistory' => $pointHistory,
            'redeemedRewards' => $redeemedRewards,
            'first_level_active_index' => 'customers',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 🌟 กดใช้คูปองแทนลูกค้า (เช่น ลูกค้าโทรมาแจ้งว่าจะใช้คูปองหน้าร้าน แอดมินกดปิดโค้ดให้)
    public function markRewardCodeUsed($id, $codeId)
    {
        $code = DB::table('customer_reward_codes')->where('id', $codeId)->where('user_id', $id)->first();
        if (!$code) abort(404);

        if ($code->status !== 'active') {
            return redirect()->back()->with('error', 'คูปองนี้ถูกใช้งานไปแล้ว หรือไม่สามารถใช้ได้อีก');
        }

        DB::table('customer_reward_codes')->where('id', $codeId)->update([
            'status' => 'used',
            'used_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'ใช้คูปองแทนลูกค้าเรียบร้อยแล้ว');
    }

    // ==========================================
    // ส่วนนำเข้าแต้มลูกค้าเป็นชุดผ่านไฟล์ Excel
    // คอลัมน์ที่ 1 = เบอร์โทรลูกค้า, คอลัมน์ที่ 2 = จำนวนแต้มที่จะบวกเพิ่ม (แถวแรกเป็นหัวตาราง จะถูกข้ามอัตโนมัติ)
    // ==========================================

    // 4. หน้าอัปโหลดไฟล์ + ประวัติการนำเข้าที่ผ่านมา
    public function pointsImportForm()
    {
        $batches = DB::table('point_import_batches')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.customers.points-import', [
            'batches' => $batches,
            'first_level_active_index' => 'customers',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 5. รับไฟล์ Excel มาประมวลผลนำเข้าแต้ม
    public function pointsImportStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'ไม่สามารถอ่านไฟล์นี้ได้ กรุณาตรวจสอบว่าเป็นไฟล์ Excel (.xlsx/.xls) หรือ .csv ที่ถูกต้อง');
        }

        // อ่านทั้งชีตเป็น array (index เริ่มที่ 0), ตัดแถวหัวตาราง (แถวแรก) ทิ้ง
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows);

        // หา tier ที่ min_spending ต่ำสุดไว้ล่วงหน้า เผื่อต้องสร้าง wallet ใหม่ให้ลูกค้าที่ยังไม่เคยมี
        $defaultTierId = DB::table('loyalty_tiers')->orderBy('min_spending', 'asc')->value('id');

        $batchId = DB::table('point_import_batches')->insertGetId([
            'admin_id' => Auth::id(),
            'original_filename' => $originalName,
            'total_rows' => count($rows),
            'success_count' => 0,
            'fail_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $successCount = 0;
        $failDetails = [];

        foreach ($rows as $index => $row) {
            $lineNo = $index + 2; // +1 เพราะ index เริ่มที่ 0, +1 อีกทีเพราะตัดแถวหัวตารางออกไปแล้ว
            $rawPhone = trim((string) ($row[0] ?? ''));
            $rawPoints = trim((string) ($row[1] ?? ''));

            if ($rawPhone === '' && $rawPoints === '') {
                continue; // ข้ามแถวว่างเปล่า
            }

            $phone = $this->normalizePhone($rawPhone);
            $points = (int) preg_replace('/[^0-9\-]/', '', $rawPoints);

            if (!$phone || !preg_match('/^0[0-9]{8,9}$/', $phone)) {
                $failDetails[] = ['row' => $lineNo, 'phone' => $rawPhone, 'reason' => 'รูปแบบเบอร์โทรไม่ถูกต้อง'];
                continue;
            }
            if ($points === 0) {
                $failDetails[] = ['row' => $lineNo, 'phone' => $rawPhone, 'reason' => 'จำนวนแต้มไม่ถูกต้องหรือเป็น 0'];
                continue;
            }

            $user = DB::table('users')->where('phone', $phone)->where('role', 'customer')->first();
            if (!$user) {
                $failDetails[] = ['row' => $lineNo, 'phone' => $rawPhone, 'reason' => 'ไม่พบลูกค้าเบอร์นี้ในระบบ'];
                continue;
            }

            DB::beginTransaction();
            try {
                $wallet = DB::table('customer_wallets')->where('user_id', $user->id)->first();
                if ($wallet) {
                    DB::table('customer_wallets')->where('user_id', $user->id)->increment('current_points', $points);
                } else {
                    DB::table('customer_wallets')->insert([
                        'user_id' => $user->id,
                        'current_tier_id' => $defaultTierId,
                        'current_points' => $points,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('point_transactions')->insert([
                    'import_batch_id' => $batchId,
                    'user_id' => $user->id,
                    'amount' => $points,
                    'type' => 'adjust', // ยืมค่า enum เดิม (earn/redeem/adjust) ที่มีอยู่แล้ว ไม่ต้องแก้โครงสร้างตาราง
                    'reference_id' => 'IMPORT-' . $batchId,
                    'description' => 'นำเข้าแต้มโดยแอดมิน (ไฟล์: ' . $originalName . ', แถวที่ ' . $lineNo . ')',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();
                $successCount++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $failDetails[] = ['row' => $lineNo, 'phone' => $rawPhone, 'reason' => 'บันทึกไม่สำเร็จ: ' . $e->getMessage()];
            }
        }

        DB::table('point_import_batches')->where('id', $batchId)->update([
            'success_count' => $successCount,
            'fail_count' => count($failDetails),
            'fail_details' => json_encode($failDetails, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.customers.points-import.show', $batchId)
            ->with('success', "นำเข้าแต้มเสร็จสิ้น สำเร็จ {$successCount} รายการ ไม่สำเร็จ " . count($failDetails) . ' รายการ');
    }

    // 6. หน้ารายละเอียดผลลัพธ์การนำเข้าแต่ละครั้ง (ดูว่าแถวไหนพลาดเพราะอะไร)
    public function pointsImportShow($batchId)
    {
        $batch = DB::table('point_import_batches')->where('id', $batchId)->first();
        if (!$batch) abort(404);

        $failDetails = $batch->fail_details ? json_decode($batch->fail_details, true) : [];

        $successRows = DB::table('point_transactions')
            ->join('users', 'point_transactions.user_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->where('point_transactions.import_batch_id', $batchId)
            ->select(
                'users.id as user_id',
                'users.phone',
                'customer_profiles.first_name',
                'customer_profiles.last_name',
                'point_transactions.amount',
                'point_transactions.created_at'
            )
            ->orderBy('point_transactions.id', 'asc')
            ->get();

        return view('admin.customers.points-import-show', [
            'batch' => $batch,
            'failDetails' => $failDetails,
            'successRows' => $successRows,
            'first_level_active_index' => 'customers',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // ปรับรูปแบบเบอร์โทรให้เป็นมาตรฐานเดียวกับที่ระบบเก็บไว้ (เลขไทยขึ้นต้นด้วย 0 ไม่มีรหัสประเทศ)
    // ใช้ตรรกะเดียวกับ AuthController::login() เพื่อให้ match กับ users.phone ได้ถูกต้อง
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '+66')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '66') && strlen($phone) == 11) {
            $phone = '0' . substr($phone, 2);
        }

        if (strlen($phone) == 9 && in_array(substr($phone, 0, 1), ['6', '8', '9'])) {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    // 3. ฟังก์ชันบันทึกการเพิ่มสินค้าให้ลูกค้า
    public function storeProduct(Request $request, $id)
    {
        $request->validate([
            'master_product_id' => 'required',
            'serial_number' => 'nullable|string|unique:customer_products,serial_number',
            'purchase_date' => 'nullable|date',
            'warranty_expire_date' => 'nullable|date',
        ]);

        // ดึงชื่อสินค้าจาก Master มาบันทึก (ตามที่ออกแบบ DB ไว้ว่าเก็บแค่ชื่อ)
        $masterProduct = DB::table('products')->where('id', $request->master_product_id)->first();

        DB::table('customer_products')->insert([
            'customer_id' => $id,
            'reference_type' => 'product', // 🌟 เพิ่มบรรทัดนี้
            'reference_id' => $request->master_product_id, // 🌟 เพิ่มบรรทัดนี้
            'product_name' => $masterProduct->name_th . ' / ' . $masterProduct->name_en,
            'serial_number' => $request->serial_number,
            'purchase_date' => $request->purchase_date,
            'warranty_expire_date' => $request->warranty_expire_date,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มสินค้าให้ลูกค้าเรียบร้อยแล้ว');
    }

    // ฟังก์ชันบันทึกการเพิ่ม ประกันภัย ให้ลูกค้า
    public function storeInsurance(Request $request, $id)
    {
        $request->validate(['master_insurance_id' => 'required', 'purchase_date' => 'required|date']);

        $insurance = DB::table('insurances')->where('id', $request->master_insurance_id)->first();
        $name = $insurance->title ?? $insurance->name_th ?? 'ประกันภัย';

        DB::table('customer_products')->insert([
            'customer_id' => $id,
            'reference_type' => 'insurance', // 🌟 เพิ่มบรรทัดนี้
            'reference_id' => $request->master_insurance_id, // 🌟 เพิ่มบรรทัดนี้
            'product_name' => '[ประกันภัย] ' . $name, // 🌟 ใส่ Prefix
            'serial_number' => $request->policy_number, // ใช้ช่อง serial_number เก็บเลขกรมธรรม์
            'purchase_date' => $request->purchase_date,
            'warranty_expire_date' => $request->warranty_expire_date,
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มข้อมูลประกันภัยให้ลูกค้าเรียบร้อยแล้ว');
    }

    // ฟังก์ชันบันทึกการเพิ่ม ตู้เซฟนิรภัย ให้ลูกค้า
    public function storeLocker(Request $request, $id)
    {
        $request->validate(['master_locker_id' => 'required', 'purchase_date' => 'required|date']);

        $locker = DB::table('smart_lockers')->where('id', $request->master_locker_id)->first();
        $name = $locker->name ?? $locker->locker_name ?? 'ตู้เซฟนิรภัย';

        DB::table('customer_products')->insert([
            'customer_id' => $id,
            'reference_type' => 'locker', // 🌟 เพิ่มบรรทัดนี้
            'reference_id' => $request->master_locker_id, // 🌟 เพิ่มบรรทัดนี้
            'product_name' => '[ตู้เซฟเช่า] ' . $name, // 🌟 ใส่ Prefix
            'serial_number' => $request->locker_number, // ใช้ช่อง serial_number เก็บหมายเลขตู้/พิน
            'purchase_date' => $request->purchase_date,
            'warranty_expire_date' => $request->warranty_expire_date,
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มข้อมูลตู้เซฟนิรภัยให้ลูกค้าเรียบร้อยแล้ว');
    }
}
