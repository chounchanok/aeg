<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // จำเป็นต้องมี
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // จำเป็นต้องมีเพื่อใช้ DB::table
use App\Models\OrderItem;
use App\Models\PackageReview;

class PackageController extends Controller
{
    // เพิ่ม Request $request เข้ามาในพารามิเตอร์
    public function index(Request $request, $type)
    {
        // 1. ตรวจสอบก่อนว่าตาราง products ของคุณมีคอลัมน์ is_active หรือไม่
        // ถ้าไม่มีให้เอา ->where('is_active', true) ออก
        $query = DB::table('products')->where('is_active', true);

        // 2. รองรับการกรองตามหมวดหมู่
        // *หมายเหตุ: ถ้าในตาราง products ไม่มีคอลัมน์ 'type' ให้คอมเมนต์ 3 บรรทัดนี้ไว้ก่อน ไม่งั้นจะ Error*
        // if ($type) {
        //     $query->where('type', $type);
        // }

        $products = $query->orderBy('created_at', 'desc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                // ปรับให้ดึงจากคอลัมน์ที่มีอยู่จริงในฐานข้อมูล
                'name' => $p->name ?? '',
                'description' => $p->description ?? '',
                'price' => $p->price ?? 0,
                'image_url' => $p->image_url ?? null,
            ];
        });

        return view('frontend.packages', compact('products'));
    }

    // ==========================================
    // แสดงหน้า "แพ็กเกจ/สินค้าของฉัน" (ดึงจาก customer_products)
    // ==========================================
    public function myPackages()
    {
        $userId = Auth::id();

        // ดึงข้อมูลทั้งหมดของ User นี้จาก customer_products
        $rawPackages = DB::table('customer_products')
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $activeItems = [];
        $historyItems = [];

        foreach ($rawPackages as $pkg) {
            // ดึงประวัติการแจ้งซ่อมของชิ้นนี้มาเตรียมไว้
            $repairHistory = DB::table('service_requests')
                ->where('customer_product_id', $pkg->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // จัดโครงสร้างส่งให้หน้า Blade ทำงานง่ายๆ
            $formattedData = (object)[
                'id' => $pkg->id,
                'product_name' => $pkg->product_name ?? 'ไม่ระบุชื่อ',
                'serial_number' => $pkg->serial_number ?? '-',
                'image_url' => $pkg->image_url ?? asset('assets/image/img-zo1.webp'),
                'warranty_expire_date' => $pkg->warranty_expire_date,
                'created_at' => \Carbon\Carbon::parse($pkg->created_at),
                'reference_type' => $pkg->reference_type ?? 'product', // 🌟 ชนิด: product, insurance, locker
                'total_service_count' => $pkg->total_service_count ?? 0,
                'used_service_count' => $pkg->used_service_count ?? 0,
                'remaining_services' => max(0, ($pkg->total_service_count ?? 0) - ($pkg->used_service_count ?? 0)),
                'repair_history' => $repairHistory // ประวัติงานซ่อม
            ];

            // ตรวจเช็คว่าหมดอายุหรือยัง (เช็คสถานะพ่วงกับวันหมดอายุ)
            $isExpired = false;
            if (!empty($pkg->warranty_expire_date)) {
                $isExpired = \Carbon\Carbon::parse($pkg->warranty_expire_date)->isPast();
            }

            if ($pkg->status === 'active' && !$isExpired) {
                $activeItems[] = $formattedData;
            } else {
                $historyItems[] = $formattedData;
            }
        }

        // แปลงเป็น Collection เพื่อให้หน้า Blade ใช้คำสั่ง ->count() ได้ไม่มีบัค
        $activeItems = collect($activeItems);
        $historyItems = collect($historyItems);

        return view('frontend.packages', compact('activeItems', 'historyItems'));
    }

    // แสดงหน้า "เขียนรีวิว"
    public function feedback($id)
    {
        $item = OrderItem::with('product')->findOrFail($id);
        return view('frontend.package-feedback', compact('item'));
    }

    public function packagesServices()
    {
        $products = DB::table('products')->where('type', 5)->get();
        return view('frontend.service-package', compact('products'));
    }

    // รับค่าจากฟอร์มเพื่อบันทึกลง Database
    public function submitFeedback(Request $request, $id)
    {
        $request->validate([
            'install_rating' => 'required|integer|min:1|max:5',
            'sales_rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string'
        ]);

        $user = Auth::user();

        // เช็คว่าเคยรีวิวรายการนี้ไปแล้วหรือยัง
        $existingReview = PackageReview::where('order_item_id', $id)->where('user_id', $user->id)->first();
        if ($existingReview) {
            return back()->with('error', 'คุณได้ให้คะแนนแพ็กเกจนี้ไปแล้ว');
        }

        DB::beginTransaction();
        try {
            // บันทึกรีวิว
            PackageReview::create([
                'order_item_id' => $id,
                'user_id' => $user->id,
                'install_rating' => $request->install_rating,
                'sales_rating' => $request->sales_rating,
                'review_text' => $request->review_text
            ]);

            // แจก 1 EASE Coin ตามแบนเนอร์ที่คุณทำไว้
            DB::table('customer_wallets')->where('user_id', $user->id)->increment('current_points', 1);

            DB::table('point_transactions')->insert([
                'user_id' => $user->id,
                'amount' => 1,
                'type' => 'earn',
                'description' => 'ได้รับพอยท์จากการรีวิวแพ็กเกจ',
                'created_at' => now()
            ]);

            DB::commit();
            return redirect()->route('packages.mine')->with('success', 'ขอบคุณสำหรับคำติชม คุณได้รับ 1 EASE Coin แล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาดในการส่งข้อมูล: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 2. หน้าแสดงรายละเอียดประวัติและความคุ้มครอง (Package Detail)
    // ==========================================
    public function showDetail($id)
    {
        $userId = Auth::id();

        // ดึงข้อมูลไอเทม
        $package = DB::table('customer_products')
            ->where('id', $id)
            ->where('customer_id', $userId)
            ->first();

        if (!$package) abort(404);

        // ดึงประวัติงานซ่อมทั้งหมดของชิ้นนี้
        $repairs = DB::table('service_requests')
            ->where('customer_product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // ดึงข้อมูลรายละเอียดข้อความคุ้มครอง/คำอธิบายสินค้าจากตารางหลัก Master
        $description = 'ไม่มีข้อมูลรายละเอียดอุปกรณ์';
        $coverage = '';

        if (($package->reference_type ?? 'product') === 'product') {
            $master = DB::table('products')->where('id', $package->reference_id)->first();
            $description = $master->description_th ?? '';
        } elseif ($package->reference_type === 'insurance') {
            $master = DB::table('insurances')->where('id', $package->reference_id)->first();
            $description = $master->description_th ?? '';
            $coverage = $master->insurance_coverage ?? '';
        }

        return view('frontend.package-detail', compact('package', 'repairs', 'description', 'coverage'));
    }

    // ==========================================
    // API สำหรับดึงรายละเอียดการปิดงานของช่าง (ส่งให้ Modal หน้าบ้าน)
    // ==========================================
    public function getRepairCompletionDetail($repairId)
    {
        $userId = Auth::id();

        // 1. ดึงข้อมูลใบแจ้งซ่อม พร้อมตรวจสอบสิทธิ์ (ต้องเป็นของ User คนนี้)
        $repair = DB::table('service_requests')
            ->where('id', $repairId)
            ->where('customer_id', $userId)
            ->first();

        if (!$repair) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลรายการนี้'], 404);
        }

        // 2. ดึงข้อมูลบันทึกการส่งงานของช่างจากตารางย่อย
        $completion = DB::table('service_request_completions')
            ->where('service_request_id', $repairId)
            ->first();

        // จัดการฟอร์แมตข้อมูลรูปภาพ (แปลงจาก JSON String เป็น Array)
        $beforeMedia = $completion && $completion->before_media_paths ? json_decode($completion->before_media_paths) : [];
        $afterMedia = $completion && $completion->after_media_paths ? json_decode($completion->after_media_paths) : [];

        // 3. เตรียมข้อมูลเวลาสเตปการทำงานของช่าง
        $timestamps = [
            'assigned'  => $repair->updated_at ? \Carbon\Carbon::parse($repair->updated_at)->format('d/m/Y H:i') . ' น.' : '-',
            'accepted'  => $repair->accepted_at ? \Carbon\Carbon::parse($repair->accepted_at)->format('d/m/Y H:i') . ' น.' : 'ไม่มีข้อมูล',
            'traveling' => $repair->traveling_at ? \Carbon\Carbon::parse($repair->traveling_at)->format('d/m/Y H:i') . ' น.' : 'ไม่มีข้อมูล',
            'arrived'   => $repair->arrived_at ? \Carbon\Carbon::parse($repair->arrived_at)->format('d/m/Y H:i') . ' น.' : 'ไม่มีข้อมูล',
            'started'   => $repair->started_at ? \Carbon\Carbon::parse($repair->started_at)->format('d/m/Y H:i') . ' น.' : 'ไม่มีข้อมูล',
            'completed' => $repair->completed_at ? \Carbon\Carbon::parse($repair->completed_at)->format('d/m/Y H:i') . ' น.' : '-',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'ticket_number' => $repair->ticket_number,
                'status_text' => $repair->status === 'completed' ? 'ซ่อมเสร็จสิ้น' : 'อยู่ระหว่างดำเนินการ',
                'technician_note' => $completion->technician_note ?? 'ช่างไม่ได้ระบุหมายเหตุไว้',
                'customer_signature' => $completion->customer_signature_path ?? null,
                'before_media' => $beforeMedia,
                'after_media' => $afterMedia,
                'timestamps' => $timestamps
            ]
        ]);
    }
}
