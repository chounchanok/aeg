<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TechnicianJobService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

/**
 * 🌟 เมนูช่างซ่อมบนเว็บหลังบ้าน ("งานของฉัน") — ทำงานเหมือนแอปช่างทุกขั้นตอน
 * ใช้ TechnicianJobService ตัวเดียวกับ API (/api/technician/*) และเห็นเฉพาะงานที่ตัวเองได้รับมอบหมาย
 */
class TechnicianPortalController extends Controller
{
    private function view(string $name, array $data, string $active)
    {
        return view($name, $data + [
            'first_level_active_index' => $active,
            'second_level_active_index' => '',
            'third_level_active_index' => '',
        ]);
    }

    public function jobs(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $techId = (int) Auth::id();
        $tasks = TechnicianJobService::tasks($techId, $request->date)['data'];
        $kpi = TechnicianJobService::kpi($techId)['data'];

        $tab = $request->query('tab');
        if (!in_array($tab, ['new', 'ongoing', 'history'], true)) {
            // เปิดแท็บที่มีงานค้างก่อน
            $tab = count($tasks['ongoing_tasks']) ? 'ongoing' : (count($tasks['new_tasks']) ? 'new' : 'history');
        }

        return $this->view('admin.technician.jobs', [
            'tasks' => $tasks,
            'kpi' => $kpi,
            'tab' => $tab,
            'date' => $request->date,
            'labels' => TechnicianJobService::STATUS_LABELS,
        ], 'tech-jobs');
    }

    public function show($id)
    {
        $r = TechnicianJobService::detail((int) Auth::id(), $id);
        abort_unless($r['ok'], 404);

        $job = $r['data'];
        $status = $job['job_info']['status'];

        return $this->view('admin.technician.show', [
            'job' => $job,
            'status' => $status,
            'next' => TechnicianJobService::NEXT_ACTION[$status] ?? null,
            'flow' => TechnicianJobService::FLOW,
            'labels' => TechnicianJobService::STATUS_LABELS,
            'completion' => $status === 'completed' ? TechnicianJobService::completion($id) : null,
        ], 'tech-jobs');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:accepted,traveling,arrived,in_progress']);
        $r = TechnicianJobService::updateStatus((int) Auth::id(), $id, $request->status);

        return back()->with($r['ok'] ? 'success' : 'error', $r['message']);
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'before_media' => 'nullable|array',
            'before_media.*' => 'file|mimes:jpeg,png,jpg,mp4,mov|max:20480',
            'after_media' => 'nullable|array',
            'after_media.*' => 'file|mimes:jpeg,png,jpg,mp4,mov|max:20480',
            'customer_signature_data' => 'required|string',
            'technician_note' => 'nullable|string|max:2000',
            'rating' => 'required|integer|min:0|max:5',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ], [
            'customer_signature_data.required' => 'กรุณาให้ลูกค้าเซ็นรับงาน',
            'rating.required' => 'กรุณาเลือกคะแนนความพึงพอใจของลูกค้า',
            'latitude.required' => 'ไม่พบตำแหน่งปัจจุบัน — กรุณากด "อนุญาต" ให้เบราว์เซอร์ใช้ตำแหน่ง แล้วลองอีกครั้ง',
            'longitude.required' => 'ไม่พบตำแหน่งปัจจุบัน — กรุณากด "อนุญาต" ให้เบราว์เซอร์ใช้ตำแหน่ง แล้วลองอีกครั้ง',
            'before_media.*.max' => 'ไฟล์รูป/วิดีโอแต่ละไฟล์ต้องไม่เกิน 20MB',
            'after_media.*.max' => 'ไฟล์รูป/วิดีโอแต่ละไฟล์ต้องไม่เกิน 20MB',
        ]);

        $signature = $this->signatureFile($request->customer_signature_data);
        if (!$signature) {
            return back()->withErrors(['customer_signature_data' => 'ลายเซ็นไม่ถูกต้อง กรุณาเซ็นใหม่อีกครั้ง'])->withInput();
        }

        $r = TechnicianJobService::complete(
            (int) Auth::id(),
            $id,
            $request->file('before_media', []),
            $request->file('after_media', []),
            $signature,
            (int) $request->rating,
            $request->latitude,
            $request->longitude,
            $request->technician_note
        );

        @unlink($signature->getPathname());

        return $r['ok']
            ? redirect()->route('admin.tech.show', $id)->with('success', $r['message'])
            : back()->with('error', $r['message'])->withInput();
    }

    /** ลายเซ็นจาก canvas (data:image/png;base64,...) → ไฟล์ชั่วคราว ให้ service บันทึกเหมือนไฟล์ที่แอปอัปโหลด */
    private function signatureFile(string $dataUrl): ?UploadedFile
    {
        if (!preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#', $dataUrl, $m)) return null;
        $binary = base64_decode($m[1], true);
        if ($binary === false || strlen($binary) < 100 || strlen($binary) > 5 * 1024 * 1024) return null;
        if (substr($binary, 0, 8) !== "\x89PNG\r\n\x1a\n") return null; // ต้องเป็นไฟล์ PNG จริง

        $path = tempnam(sys_get_temp_dir(), 'sig');
        file_put_contents($path, $binary);
        return new UploadedFile($path, 'signature.png', 'image/png', null, true);
    }

    public function profile()
    {
        $user = Auth::user();
        return $this->view('admin.technician.profile', [
            'profile' => TechnicianJobService::profile($user)['data'],
            'kpi' => TechnicianJobService::kpi((int) $user->id)['data'],
        ], 'tech-profile');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            // เบอร์ใช้เข้าสู่ระบบ → ห้ามซ้ำกับบัญชีอื่น
            'phone' => 'required|string|max:20|unique:users,phone,' . Auth::id(),
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'full_name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'phone.unique' => 'เบอร์โทรศัพท์นี้มีบัญชีอื่นใช้อยู่แล้ว',
        ]);

        $r = TechnicianJobService::updateProfile(Auth::user(), $request->full_name, $request->phone, $request->address, $request->file('profile_image'));
        return back()->with($r['ok'] ? 'success' : 'error', $r['message']);
    }
}
