<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TechnicianJobService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * API ฝั่งแอปช่างซ่อม (/api/technician/*) — ตรรกะทั้งหมดอยู่ที่ TechnicianJobService
 * (หน้า "งานของฉัน" บนเว็บหลังบ้านใช้ service ตัวเดียวกัน จึงทำงานเหมือนกัน)
 * รูปแบบ request / response เหมือนเดิมทุกเส้น
 */
class TechnicianController extends Controller
{
    use ApiResponseTrait;

    /** รับไฟล์ได้ทั้งแบบ before_media[] (หลายไฟล์) และ before_media (ไฟล์เดียว) */
    private function files(Request $request, string $key): array
    {
        $f = $request->file($key);
        return $f ? (is_array($f) ? array_values($f) : [$f]) : [];
    }

    private function respond(array $r)
    {
        return $r['ok'] ? $this->successResponse($r['data'], $r['message']) : $this->errorResponse($r['message'], $r['status']);
    }

    // 1. รายการงานของช่าง (?date=YYYY-MM-DD)
    public function getMyTasks(Request $request)
    {
        if ($request->has('date') && !empty($request->date)) {
            $request->validate(['date' => 'date_format:Y-m-d']);
        }
        return $this->respond(TechnicianJobService::tasks((int) $request->user()->id, $request->date));
    }

    // 2. รายละเอียดงาน + Tracking logs
    public function getTaskDetail(Request $request, $id)
    {
        return $this->respond(TechnicianJobService::detail((int) $request->user()->id, $id));
    }

    // 3. อัปเดตสถานะ (accepted, traveling, arrived, in_progress)
    public function updateTaskStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:accepted,traveling,arrived,in_progress']);
        return $this->respond(TechnicianJobService::updateStatus((int) $request->user()->id, $id, $request->status));
    }

    // 4. ปิดงาน
    public function submitCompletion(Request $request, $id)
    {
        $request->validate([
            'before_media.*' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:20480',
            'after_media.*' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:20480',
            'customer_signature' => 'required|file|mimes:jpeg,png,jpg|max:5120',
            'technician_note' => 'nullable|string',
            'rating' => 'required|integer|min:0|max:5',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        return $this->respond(TechnicianJobService::complete(
            (int) $request->user()->id,
            $id,
            $this->files($request, 'before_media'),
            $this->files($request, 'after_media'),
            $request->file('customer_signature'),
            (int) $request->rating,
            $request->latitude,
            $request->longitude,
            $request->technician_note
        ));
    }

    // 5. โปรไฟล์ช่าง
    public function getProfile(Request $request)
    {
        return $this->respond(TechnicianJobService::profile($request->user()));
    }

    // 6. แก้ไขโปรไฟล์
    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $request->user()->id, // เบอร์ใช้ login ห้ามซ้ำ
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        return $this->respond(TechnicianJobService::updateProfile(
            $request->user(), $request->full_name, $request->phone, $request->address, $request->file('profile_image')
        ));
    }

    // 7. KPI
    public function getTechnicianKPI(Request $request)
    {
        return $this->respond(TechnicianJobService::kpi((int) $request->user()->id));
    }
}
