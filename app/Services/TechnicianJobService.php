<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * 🌟 งานของช่างซ่อม — ใช้ร่วมกันทั้งแอปมือถือ (Api\TechnicianController) และหลังบ้านบนเว็บ (Admin\TechnicianPortalController)
 * ตรรกะย้ายมาจาก API เดิมทั้งหมด จึงทำงานเหมือนกันทุกจุด (สถานะ, Tracking log, ปิดงาน, ตัดโควต้า, KPI)
 *
 * ทุกเมธอดคืน ['ok' => bool, 'status' => httpCode, 'message' => string, 'data' => mixed]
 */
class TechnicianJobService
{
    /** ลำดับสถานะงานของช่าง (ปุ่มถัดไปบนเว็บ) */
    public const FLOW = ['assigned', 'accepted', 'traveling', 'arrived', 'in_progress', 'completed'];

    public const STATUS_LABELS = [
        'pending' => 'รอมอบหมาย',
        'assigned' => 'งานใหม่ (รอรับงาน)',
        'accepted' => 'ช่างรับงานแล้ว',
        'traveling' => 'ช่างกำลังเดินทาง',
        'arrived' => 'ช่างเดินทางถึงหน้างาน',
        'in_progress' => 'ช่างเริ่มดำเนินการซ่อม',
        'completed' => 'งานซ่อมเสร็จสมบูรณ์',
        'cancelled' => 'ยกเลิก',
    ];

    /** ปุ่มขั้นถัดไปของแต่ละสถานะ */
    public const NEXT_ACTION = [
        'assigned' => ['status' => 'accepted', 'label' => 'รับงาน'],
        'accepted' => ['status' => 'traveling', 'label' => 'เริ่มเดินทาง'],
        'traveling' => ['status' => 'arrived', 'label' => 'ถึงหน้างานแล้ว'],
        'arrived' => ['status' => 'in_progress', 'label' => 'เริ่มซ่อม'],
    ];

    private static function ok($data, string $message): array
    {
        return ['ok' => true, 'status' => 200, 'message' => $message, 'data' => $data];
    }

    private static function fail(string $message, int $status): array
    {
        return ['ok' => false, 'status' => $status, 'message' => $message, 'data' => null];
    }

    // ==========================================
    // 1. รายการงานของช่าง (กรองตามวันนัดได้)
    // ==========================================
    public static function tasks(int $techId, ?string $date = null): array
    {
        $query = DB::table('service_requests')
            ->leftJoin('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->leftJoin('customer_addresses', 'service_requests.address_id', '=', 'customer_addresses.id')
            ->where('service_requests.technician_id', $techId);

        if (!empty($date)) {
            $query->whereDate('service_requests.preferred_date', $date);
        }

        $tasks = $query->select(
                'service_requests.id',
                'service_requests.ticket_number',
                'service_requests.status',
                'service_requests.preferred_date',
                'service_requests.time_slot',
                'service_requests.problem_description',
                'customer_products.product_name',
                'customer_addresses.province',
                'customer_addresses.district',
                'customer_addresses.latitude',
                'customer_addresses.longitude'
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

        return self::ok([
            'new_tasks' => $newTasks,
            'ongoing_tasks' => $ongoingTasks,
            'history_tasks' => $historyTasks,
        ], 'Tasks retrieved successfully');
    }

    // ==========================================
    // 2. รายละเอียดงาน + รูปจากลูกค้า + Tracking logs
    // ==========================================
    public static function detail(int $techId, $id): array
    {
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

        if (!$task) return self::fail('ไม่พบข้อมูลงานนี้', 404);

        $task->full_address = $task->address_id
            ? "{$task->address_line} อ.{$task->district} จ.{$task->province} {$task->zipcode}"
            : $task->custom_address_text;

        $total = $task->total_service_count ?? 0;
        $used = $task->used_service_count ?? 0;
        $remainingServices = max(0, $total - $used);

        $customerImages = DB::table('service_request_images')->where('service_request_id', $id)->get();
        $trackingLogs = DB::table('service_request_tracking')->where('service_request_id', $id)->orderBy('created_at', 'asc')->get();

        return self::ok([
            'job_info' => [
                'id' => $task->id,
                'ticket_number' => $task->ticket_number,
                'status' => $task->status,
                'problem_description' => $task->problem_description,
                'preferred_date' => $task->preferred_date,
                'time_slot' => $task->time_slot,
                'remaining_services' => $remainingServices,
                'created_at' => Carbon::parse($task->created_at)->format('Y-m-d H:i:s'),
                'timestamps' => [
                    'accepted_at' => $task->accepted_at,
                    'traveling_at' => $task->traveling_at,
                    'arrived_at' => $task->arrived_at,
                    'started_at' => $task->started_at,
                    'completed_at' => $task->completed_at,
                ],
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
            'customer_images' => $customerImages,
            'tracking_logs' => $trackingLogs,
        ], 'Task detail with tracking logs retrieved successfully');
    }

    // ==========================================
    // 3. อัปเดตสถานะ (รับงาน → เดินทาง → ถึงหน้างาน → เริ่มซ่อม)
    // ==========================================
    public static function updateStatus(int $techId, $id, string $status): array
    {
        $timestampColumn = [
            'accepted' => 'accepted_at',
            'traveling' => 'traveling_at',
            'arrived' => 'arrived_at',
            'in_progress' => 'started_at',
        ][$status] ?? null;
        if (!$timestampColumn) return self::fail('สถานะไม่ถูกต้อง', 422);

        // 🌟 ต้องเป็นงานของช่างคนนี้ และยังไม่ปิด/ยกเลิก (เดิมไม่เช็ค ทำให้เขียน log ลงงานของคนอื่นได้)
        $task = DB::table('service_requests')->where('id', $id)->where('technician_id', $techId)->first();
        if (!$task) return self::fail('ไม่พบข้อมูลงานนี้', 404);
        if (in_array($task->status, ['completed', 'cancelled'], true)) {
            return self::fail('งานนี้ปิด/ยกเลิกไปแล้ว เปลี่ยนสถานะไม่ได้', 400);
        }

        $label = self::STATUS_LABELS[$status];

        DB::beginTransaction();
        try {
            DB::table('service_requests')->where('id', $id)->where('technician_id', $techId)->update([
                'status' => $status,
                $timestampColumn => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::table('service_request_tracking')->insert([
                'service_request_id' => $id,
                'status' => $label,
                'description' => $label,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return self::ok(null, "อัปเดตสถานะเป็น {$label} สำเร็จ");
        } catch (\Exception $e) {
            DB::rollBack();
            return self::fail('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 4. ปิดงาน (รูปก่อน/หลัง, ลายเซ็นลูกค้า, คะแนน, พิกัด) + ตัดโควต้า
    // ==========================================
    /**
     * @param UploadedFile[] $beforeMedia
     * @param UploadedFile[] $afterMedia
     */
    public static function complete(int $techId, $id, array $beforeMedia, array $afterMedia, UploadedFile $signature,
                                    int $rating, $latitude, $longitude, ?string $note): array
    {
        $task = DB::table('service_requests')->where('id', $id)->where('technician_id', $techId)->first();

        if (!$task) return self::fail('ไม่พบข้อมูลงานนี้', 404);
        if ($task->status === 'completed') return self::fail('งานนี้ถูกปิดไปแล้ว', 400);

        DB::beginTransaction();
        try {
            $beforePaths = [];
            $afterPaths = [];
            foreach ($beforeMedia as $file) {
                $beforePaths[] = url('storage/' . $file->store('completions/before', 'public'));
            }
            foreach ($afterMedia as $file) {
                $afterPaths[] = url('storage/' . $file->store('completions/after', 'public'));
            }
            $signatureUrl = url('storage/' . $signature->store('completions/signatures', 'public'));

            DB::table('service_request_completions')->insert([
                'service_request_id' => $id,
                'before_media_paths' => json_encode($beforePaths),
                'after_media_paths' => json_encode($afterPaths),
                'customer_signature_path' => $signatureUrl,
                'rating' => $rating,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'technician_note' => $note,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::table('service_requests')->where('id', $id)->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::table('customer_products')->where('id', $task->customer_product_id)->increment('used_service_count', 1);

            DB::table('service_request_tracking')->insert([
                'service_request_id' => $id,
                'status' => 'งานซ่อมเสร็จสมบูรณ์',
                'description' => 'ช่างส่งงานและให้ลูกค้าเซ็นรับงานเรียบร้อยแล้ว',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();

            $updatedTask = DB::table('service_requests')->where('id', $id)->first();
            $completionData = DB::table('service_request_completions')->where('service_request_id', $id)->first();
            $trackingHistory = DB::table('service_request_tracking')->where('service_request_id', $id)->orderBy('created_at', 'asc')->get();

            return self::ok([
                'task_detail' => [
                    'id' => $updatedTask->id,
                    'service_request_number' => $updatedTask->service_request_number ?? 'SR-' . str_pad($updatedTask->id, 6, '0', STR_PAD_LEFT),
                    'status' => $updatedTask->status,
                    'preferred_date' => $updatedTask->preferred_date,
                    'completed_at' => Carbon::parse($updatedTask->completed_at)->format('d M Y - H:i น.'),
                ],
                'completion_summary' => [
                    'rating' => (int) $completionData->rating,
                    'latitude' => (float) $completionData->latitude,
                    'longitude' => (float) $completionData->longitude,
                    'technician_note' => $completionData->technician_note,
                    'customer_signature' => $completionData->customer_signature_path,
                    'before_images' => json_decode($completionData->before_media_paths),
                    'after_images' => json_decode($completionData->after_media_paths),
                ],
                'tracking_timeline' => $trackingHistory->map(fn ($log) => [
                    'status' => $log->status,
                    'description' => $log->description,
                    'time' => Carbon::parse($log->created_at)->format('d M Y - H:i น.'),
                ]),
            ], 'ปิดจ๊อบส่งงานและบันทึกผลประเมินเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return self::fail('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    /** ผลการปิดงาน (หน้าเว็บแสดงรูปก่อน/หลัง ลายเซ็น คะแนน) */
    public static function completion($id)
    {
        $c = DB::table('service_request_completions')->where('service_request_id', $id)->orderByDesc('id')->first();
        if ($c) {
            $c->before_images = json_decode($c->before_media_paths ?? '[]') ?: [];
            $c->after_images = json_decode($c->after_media_paths ?? '[]') ?: [];
        }
        return $c;
    }

    // ==========================================
    // 5-6. โปรไฟล์ช่าง
    // ==========================================
    public static function profile($user): array
    {
        $profile = DB::table('customer_profiles')->where('user_id', $user->id)->first();

        $fullName = trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''));
        if (empty($fullName)) {
            $fullName = $user->name ?? $user->username ?? '';
        }

        return self::ok([
            'full_name' => $fullName,
            'email' => $user->email ?? '-',
            'phone' => $user->phone ?? '-',
            'address' => $profile->address ?? '',
            'profile_image_url' => $profile->profile_image_url ?? null,
        ], 'Profile retrieved successfully');
    }

    public static function updateProfile($user, string $fullName, string $phone, ?string $address, ?UploadedFile $image): array
    {
        $nameParts = explode(' ', trim($fullName), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        DB::beginTransaction();
        try {
            DB::table('users')->where('id', $user->id)->update([
                'phone' => $phone,
                'updated_at' => Carbon::now(),
            ]);

            $profileImageUrl = null;
            if ($image) {
                $profileImageUrl = url('storage/' . $image->store('profiles', 'public'));
            }

            $existingProfile = DB::table('customer_profiles')->where('user_id', $user->id)->first();
            $profileData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $address,
                'updated_at' => Carbon::now(),
            ];
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

            return self::ok([
                'full_name' => $fullName,
                'phone' => $phone,
                'address' => $address,
                'profile_image_url' => $profileImageUrl ?? ($existingProfile->profile_image_url ?? null),
            ], 'อัปเดตข้อมูลโปรไฟล์สำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return self::fail('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 7. สถิติ KPI ของช่าง
    // ==========================================
    public static function kpi(int $techId): array
    {
        $completedJobs = DB::table('service_requests')
            ->where('technician_id', $techId)
            ->where('status', 'completed')
            ->get();

        $totalCompleted = $completedJobs->count();

        $averageRating = 0;
        if ($totalCompleted > 0) {
            $averageRating = DB::table('service_request_completions')
                ->join('service_requests', 'service_request_completions.service_request_id', '=', 'service_requests.id')
                ->where('service_requests.technician_id', $techId)
                ->where('service_requests.status', 'completed')
                ->avg('service_request_completions.rating');
        }
        $averageRating = round((float) $averageRating, 1);

        $onTimeCount = $completedJobs->filter(function ($job) {
            if (!$job->completed_at || !$job->preferred_date) return false;
            $appointment = Carbon::parse($job->preferred_date)->startOfDay();
            $completed = Carbon::parse($job->completed_at)->startOfDay();
            return $completed->lte($appointment);
        })->count();

        $onTimePercentage = $totalCompleted > 0 ? round(($onTimeCount / $totalCompleted) * 100) : 100;

        $satisfactionPercent = ($averageRating / 5) * 100;
        $responseTimePercent = $onTimePercentage;

        return self::ok([
            'summary' => [
                'average_rating' => $averageRating,
                'on_time_percentage' => $onTimePercentage,
                'total_completed' => $totalCompleted,
            ],
            'kpis' => [
                [
                    'title' => 'เวลาการตอบสนอง (Response Time)',
                    'status_text' => self::kpiLabel($responseTimePercent),
                    'percentage' => $responseTimePercent,
                    'color_theme' => $responseTimePercent >= 80 ? 'green' : ($responseTimePercent >= 60 ? 'yellow' : 'red'),
                ],
                [
                    'title' => 'ความพึงพอใจลูกค้า',
                    'status_text' => self::kpiLabel($satisfactionPercent),
                    'percentage' => $satisfactionPercent,
                    'color_theme' => $satisfactionPercent >= 80 ? 'green' : ($satisfactionPercent >= 60 ? 'yellow' : 'red'),
                ],
            ],
        ], 'ดึงข้อมูลสถิติช่างสำเร็จ');
    }

    private static function kpiLabel($percent): string
    {
        if ($percent >= 90) return 'ดีเยี่ยม';
        if ($percent >= 75) return 'ดีมาก';
        if ($percent >= 60) return 'ดี';
        if ($percent >= 50) return 'พอใช้';
        return 'ต้องปรับปรุง';
    }
}
