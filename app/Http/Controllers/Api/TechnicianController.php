<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;

class TechnicianController extends Controller
{
    use ApiResponseTrait;

    // ==========================================
    // 1. ดึงรายการงานทั้งหมดของช่าง
    // ==========================================
    public function getMyTasks(Request $request)
    {
        $techId = $request->user()->id;

        $tasks = DB::table('service_requests')
            ->leftJoin('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->leftJoin('customer_addresses', 'service_requests.address_id', '=', 'customer_addresses.id')
            ->where('service_requests.technician_id', $techId)
            ->select(
                'service_requests.id',
                'service_requests.ticket_number',
                'service_requests.status',
                'service_requests.preferred_date',
                'service_requests.time_slot',
                'service_requests.problem_description',
                'customer_products.product_name',
                'customer_addresses.province',
                'customer_addresses.district'
            )
            ->orderBy('service_requests.preferred_date', 'asc')
            ->get();

        $newTasks = [];
        $ongoingTasks = [];
        $historyTasks = [];

        foreach ($tasks as $task) {
            if ($task->status === 'assigned') {
                $newTasks[] = $task;
            } elseif (in_array($task->status, ['accepted', 'traveling', 'arrived', 'in_progress'])) {
                $ongoingTasks[] = $task;
            } else {
                $historyTasks[] = $task;
            }
        }

        return $this->successResponse([
            'new_tasks' => $newTasks,
            'ongoing_tasks' => $ongoingTasks,
            'history_tasks' => $historyTasks
        ], 'Tasks retrieved successfully');
    }

    // ==========================================
    // 2. ดึงรายละเอียดของงาน (สำหรับหน้า Job Detail) + เพิ่มประวัติและสถานะ
    // ==========================================
    public function getTaskDetail(Request $request, $id)
    {
        $techId = $request->user()->id;

        // 1. ดึงรายละเอียดของใบแจ้งซ่อม (Header)
        $task = DB::table('service_requests')
            ->leftJoin('users', 'service_requests.customer_id', '=', 'users.id')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->leftJoin('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->leftJoin('customer_addresses', 'service_requests.address_id', '=', 'customer_addresses.id')
            ->where('service_requests.id', $id)
            ->where('service_requests.technician_id', $techId)
            ->select(
                'service_requests.*',
                'customer_profiles.first_name as customer_name',
                'users.phone as customer_phone',
                'customer_products.product_name',
                'customer_products.serial_number',
                'customer_products.total_service_count',
                'customer_products.used_service_count',
                'customer_addresses.address_line',
                'customer_addresses.district',
                'customer_addresses.province',
                'customer_addresses.zipcode',
                'customer_addresses.latitude',
                'customer_addresses.longitude'
            )
            ->first();

        if (!$task) return $this->errorResponse('ไม่พบข้อมูลงานนี้', 404);

        // รวมโครงสร้างที่อยู่
        $task->full_address = $task->address_id
            ? "{$task->address_line} อ.{$task->district} จ.{$task->province} {$task->zipcode}"
            : $task->custom_address_text;

        // คำนวณจำนวนโควต้าบริการคงเหลือของลูกค้าท่านนี้
        $total = $task->total_service_count ?? 0;
        $used = $task->used_service_count ?? 0;
        $remainingServices = max(0, $total - $used);

        // 🌟 2. ดึงรูปภาพประกอบหน้างานที่ลูกค้าเคยส่งมาตอนแจ้งซ่อม
        $customerImages = DB::table('service_request_images')
            ->where('service_request_id', $id)
            ->get();

        // 🌟 3. ดึงประวัติ Log การเปลี่ยนสถานะงานทั้งหมด (Tracking Logs) ของใบงานนี้
        $trackingLogs = DB::table('service_request_tracking')
            ->where('service_request_id', $id)
            ->orderBy('created_at', 'asc') // เรียงจากสเตปแรกสุด (เก่าไปใหม่) เพื่อให้แอปทำไทม์ไลน์ง่าย
            ->get();

        // ประกอบก้อนโครงสร้าง Response ส่งกลับไปให้แอปพลิเคชัน
        $data = [
            'job_info' => [
                'id' => $task->id,
                'ticket_number' => $task->ticket_number,
                'status' => $task->status, // สถานะปัจจุบัน (assigned, accepted, traveling, arrived, in_progress, completed)
                'problem_description' => $task->problem_description,
                'preferred_date' => $task->preferred_date,
                'time_slot' => $task->time_slot,
                'remaining_services' => $remainingServices, // โควต้าคงเหลือโชว์ในแอปช่าง
                'created_at' => \Carbon\Carbon::parse($task->created_at)->format('Y-m-d H:i:s'),

                // ข้อมูลแสตมป์เวลาแต่ละสเตป
                'timestamps' => [
                    'accepted_at' => $task->accepted_at,
                    'traveling_at' => $task->traveling_at,
                    'arrived_at' => $task->arrived_at,
                    'started_at' => $task->started_at,
                    'completed_at' => $task->completed_at,
                ]
            ],
            'customer_info' => [
                'name' => $task->customer_name ?? $task->username ?? 'ไม่ระบุชื่อ',
                'phone' => $task->customer_phone,
                'product_name' => $task->product_name ?? 'บริการทั่วไป',
                'serial_number' => $task->serial_number ?? '-',
                'full_address' => $task->full_address,
                'latitude' => $task->latitude,
                'longitude' => $task->longitude,
            ],
            'customer_images' => $customerImages, // รูปแนบตอนเปิดบิล
            'tracking_logs' => $trackingLogs     // 🌟 บันทึกประวัติ Log การอัปเดตสถานะของงานนี้
        ];

        return $this->successResponse($data, 'Task detail with tracking logs retrieved successfully');
    }

    // ==========================================
    // 3. อัปเดตสถานะงาน (กดรับงาน, เดินทาง, ถึงแล้ว, เริ่มซ่อม)
    // ==========================================
    public function updateTaskStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,traveling,arrived,in_progress'
        ]);

        $techId = $request->user()->id;
        $status = $request->status;

        // จับคู่สถานะกับคอลัมน์เวลาที่จะถูกอัปเดต
        $timestampColumn = [
            'accepted' => 'accepted_at',
            'traveling' => 'traveling_at',
            'arrived' => 'arrived_at',
            'in_progress' => 'started_at',
        ][$status];

        $statusLabels = [
            'accepted' => 'ช่างรับงานแล้ว',
            'traveling' => 'ช่างกำลังเดินทาง',
            'arrived' => 'ช่างเดินทางถึงหน้างาน',
            'in_progress' => 'ช่างเริ่มดำเนินการซ่อม',
        ];

        DB::beginTransaction();
        try {
            DB::table('service_requests')
                ->where('id', $id)
                ->where('technician_id', $techId)
                ->update([
                    'status' => $status,
                    $timestampColumn => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

            DB::table('service_request_tracking')->insert([
                'service_request_id' => $id,
                'status' => $statusLabels[$status],
                'description' => $statusLabels[$status],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::commit();
            return $this->successResponse(null, "อัปเดตสถานะเป็น {$statusLabels[$status]} สำเร็จ");

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 4. ปิดจ๊อบส่งงาน (ส่งรูป, ลายเซ็น, ตัดโควต้า)
    // ==========================================
    public function submitCompletion(Request $request, $id)
    {
        $request->validate([
            'before_media.*' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:20480',
            'after_media.*' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:20480',
            'customer_signature' => 'required|file|mimes:jpeg,png,jpg|max:5120', // ลายเซ็นเป็นรูปภาพ
            'technician_note' => 'nullable|string'
        ]);

        $techId = $request->user()->id;

        $task = DB::table('service_requests')
            ->where('id', $id)
            ->where('technician_id', $techId)
            ->first();

        if (!$task) return $this->errorResponse('ไม่พบข้อมูลงานนี้', 404);
        if ($task->status === 'completed') return $this->errorResponse('งานนี้ถูกปิดไปแล้ว', 400);

        DB::beginTransaction();
        try {
            // อัปโหลดไฟล์รูปลง Server
            $beforePaths = [];
            $afterPaths = [];
            $signatureUrl = null;

            if ($request->hasFile('before_media')) {
                foreach ($request->file('before_media') as $file) {
                    $beforePaths[] = url('storage/' . $file->store('completions/before', 'public'));
                }
            }

            if ($request->hasFile('after_media')) {
                foreach ($request->file('after_media') as $file) {
                    $afterPaths[] = url('storage/' . $file->store('completions/after', 'public'));
                }
            }

            if ($request->hasFile('customer_signature')) {
                $signatureUrl = url('storage/' . $request->file('customer_signature')->store('completions/signatures', 'public'));
            }

            // บันทึกข้อมูลลงตาราง completions
            DB::table('service_request_completions')->insert([
                'service_request_id' => $id,
                'before_media_paths' => json_encode($beforePaths),
                'after_media_paths' => json_encode($afterPaths),
                'customer_signature_path' => $signatureUrl,
                'technician_note' => $request->technician_note,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // อัปเดตตารางหลักให้ส่งงาน และแสตมป์เวลาปิดงาน
            DB::table('service_requests')->where('id', $id)->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // ตัดโควต้าการเข้าบริการ (เพิ่มค่า used_service_count ขึ้น 1)
            DB::table('customer_products')->where('id', $task->customer_product_id)->increment('used_service_count', 1);

            // เก็บ Tracking Log
            DB::table('service_request_tracking')->insert([
                'service_request_id' => $id,
                'status' => 'งานซ่อมเสร็จสมบูรณ์',
                'description' => 'ช่างส่งงานและให้ลูกค้าเซ็นรับงานเรียบร้อยแล้ว',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::commit();
            return $this->successResponse(null, 'ปิดจ๊อบส่งงานสำเร็จ!');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 5. ดึงข้อมูลโปรไฟล์ของช่าง (สำหรับแสดงบนฟอร์มตอนแรก)
    // ==========================================
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $profile = DB::table('customer_profiles')->where('user_id', $user->id)->first();

        // รวมชื่อและนามสกุลให้ตรงกับ UI (ช่องเดียว)
        $fullName = trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''));
        if (empty($fullName)) {
            $fullName = $user->name ?? $user->username ?? '';
        }

        $data = [
            'full_name' => $fullName,
            'email' => $user->email ?? '-', // ส่งไปโชว์เฉยๆ ไม่ให้แก้
            'phone' => $user->phone ?? '-',
            'address' => $profile->address ?? '',
            'profile_image_url' => $profile->profile_image_url ?? null
        ];

        return $this->successResponse($data, 'Profile retrieved successfully');
    }

    // ==========================================
    // 6. บันทึกการแก้ไขข้อมูลโปรไฟล์ (อัปเดต)
    // ==========================================
    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120' // อัปโหลดรูปภาพได้สูงสุด 5MB
        ]);

        $user = $request->user();

        // แยกระบบชื่อกับนามสกุลออกจากช่อง full_name เพื่อบันทึกลง Database
        $nameParts = explode(' ', trim($request->full_name), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? ''; // ถ้าไม่มีนามสกุลก็ปล่อยว่าง

        DB::beginTransaction();
        try {
            // 1. อัปเดตเบอร์โทรในตารางหลัก (users)
            DB::table('users')->where('id', $user->id)->update([
                'phone' => $request->phone,
                'updated_at' => Carbon::now()
            ]);

            // 2. จัดการอัปโหลดรูปโปรไฟล์ใหม่ (ถ้ามีการส่งไฟล์มา)
            $profileImageUrl = null;
            if ($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('profiles', 'public');
                $profileImageUrl = url('storage/' . $path);
            }

            // 3. อัปเดตตารางข้อมูลส่วนตัว (customer_profiles)
            $existingProfile = DB::table('customer_profiles')->where('user_id', $user->id)->first();

            $profileData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $request->address,
                'updated_at' => Carbon::now()
            ];

            // ถ้ามีรูปใหม่ส่งมา ค่อยอัปเดตฟิลด์รูปภาพ
            if ($profileImageUrl) {
                $profileData['profile_image_url'] = $profileImageUrl;
            }

            if ($existingProfile) {
                DB::table('customer_profiles')->where('user_id', $user->id)->update($profileData);
            } else {
                $profileData['user_id'] = $user->id;
                $profileData['created_at'] = Carbon::now();
                DB::table('customer_profiles')->insert($profileData);
            }

            DB::commit();

            // ส่งข้อมูลล่าสุดกลับไปให้น้องโอมอัปเดต UI บนแอปได้เลย
            $updatedData = [
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'profile_image_url' => $profileImageUrl ?? ($existingProfile->profile_image_url ?? null)
            ];

            return $this->successResponse($updatedData, 'อัปเดตข้อมูลโปรไฟล์สำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }
}
