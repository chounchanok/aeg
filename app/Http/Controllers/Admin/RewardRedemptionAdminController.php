<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerNotificationService;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 🌟 เมนู "คูปอง/ของรางวัลที่ลูกค้าแลก" (คอมเมนต์ข้อ 2) — แยกออกมาจากหน้า "ลูกค้าและแพ็กเกจ"
 *
 * - สินค้า   : ลูกค้ากดใช้คูปอง → "ยืนยันการจัดส่ง" → แอดมินตรวจที่อยู่แล้วกดยืนยัน → กำลังดำเนินการ → กำลังจัดส่ง → จัดส่งสำเร็จ
 *              (ตั้งแต่ "กำลังดำเนินการ" เปลี่ยนได้จากหลังบ้านเท่านั้น)
 * - วอยเชอร์/ส่วนลด : แอดมินกรอกรหัสส่งกลับไปแสดงบนแอป — สถานะไม่เปลี่ยนจนกว่าลูกค้าจะกดใช้งาน
 */
class RewardRedemptionAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('customer_reward_codes as c')
            ->join('rewards as r', 'c.reward_id', '=', 'r.id')
            ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
            ->leftJoin('customer_profiles as cp', 'c.user_id', '=', 'cp.user_id')
            ->select(
                'c.*', 'r.title_th as reward_title', 'r.image_url', 'r.reward_type', 'r.points_required',
                'u.username', 'u.phone', 'cp.first_name', 'cp.last_name'
            );

        if ($request->filled('reward_type')) {
            $query->where('r.reward_type', $request->reward_type);
        }
        if ($request->filled('status')) {
            $query->whereIn('c.status', explode(',', (string) $request->status));
        }
        if ($request->filled('q')) {
            $q = '%' . trim($request->q) . '%';
            $query->where(function ($w) use ($q) {
                $w->where('c.code', 'like', $q)->orWhere('c.voucher_code', 'like', $q)
                  ->orWhere('r.title_th', 'like', $q)->orWhere('u.username', 'like', $q)->orWhere('u.phone', 'like', $q)
                  ->orWhere('cp.first_name', 'like', $q)->orWhere('cp.last_name', 'like', $q)
                  ->orWhere('c.customer_name', 'like', $q)->orWhere('c.customer_phone', 'like', $q);
            });
        }

        // งานที่ต้องทำก่อน: รอยืนยันการจัดส่ง / วอยเชอร์ที่ยังไม่ส่งรหัส ขึ้นบนสุด
        $codes = $query
            ->orderByRaw("CASE WHEN c.status = 'shipping_confirm' THEN 0 WHEN r.reward_type = 'voucher' AND c.status = 'active' AND (c.voucher_code IS NULL OR c.voucher_code = '') THEN 1 WHEN c.status IN ('processing','shipping') THEN 2 ELSE 3 END")
            ->orderByDesc('c.created_at')
            ->paginate(50)
            ->withQueryString();

        $counts = [
            'shipping_confirm' => DB::table('customer_reward_codes')->where('status', 'shipping_confirm')->count(),
            'waiting_voucher' => DB::table('customer_reward_codes as c')->join('rewards as r', 'c.reward_id', '=', 'r.id')
                ->where('r.reward_type', 'voucher')->where('c.status', 'active')
                ->where(fn ($w) => $w->whereNull('c.voucher_code')->orWhere('c.voucher_code', ''))->count(),
            'in_progress' => DB::table('customer_reward_codes')->whereIn('status', ['processing', 'shipping'])->count(),
        ];

        return view('admin.reward-redemptions.index', [
            'codes' => $codes,
            'counts' => $counts,
            'first_level_active_index' => 'reward-redemptions',
            'second_level_active_index' => '',
            'third_level_active_index' => '',
        ]);
    }

    public function show($id)
    {
        $code = DB::table('customer_reward_codes as c')
            ->join('rewards as r', 'c.reward_id', '=', 'r.id')
            ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
            ->leftJoin('customer_profiles as cp', 'c.user_id', '=', 'cp.user_id')
            ->where('c.id', $id)
            ->select(
                'c.*', 'r.title_th as reward_title', 'r.image_url', 'r.reward_type', 'r.points_required',
                'r.discount_amount as reward_discount_amount', 'r.delivery_estimate',
                'u.username', 'u.phone', 'u.email', 'cp.first_name', 'cp.last_name'
            )
            ->first();
        abort_unless($code, 404);

        $address = $code->address_id ? DB::table('customer_addresses')->where('id', $code->address_id)->first() : null;
        $logs = DB::table('customer_reward_code_logs as l')
            ->leftJoin('users as u', 'l.changed_by', '=', 'u.id')
            ->where('l.customer_reward_code_id', $id)
            ->orderBy('l.created_at')
            ->get(['l.*', 'u.username as changed_by_name']);

        return view('admin.reward-redemptions.show', [
            'code' => $code,
            'address' => $address,
            'logs' => $logs,
            'type' => $code->reward_type ?: RewardService::TYPE_PRODUCT,
            'nextStatuses' => RewardService::PRODUCT_ADMIN_TRANSITIONS[$code->status] ?? [],
            'first_level_active_index' => 'reward-redemptions',
            'second_level_active_index' => '',
            'third_level_active_index' => '',
        ]);
    }

    /** สินค้า: เปลี่ยนสถานะการจัดส่ง (ยืนยันการจัดส่ง → กำลังดำเนินการ → กำลังจัดส่ง → จัดส่งสำเร็จ) */
    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:processing,shipping,delivered',
            'shipping_carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:1000',
            'address_checked' => 'nullable|boolean',
        ]);

        $code = DB::table('customer_reward_codes as c')->join('rewards as r', 'c.reward_id', '=', 'r.id')
            ->where('c.id', $id)->select('c.*', 'r.reward_type', 'r.title_th')->first();
        abort_unless($code, 404);

        if (($code->reward_type ?: 'product') !== RewardService::TYPE_PRODUCT) {
            return back()->with('error', 'เปลี่ยนสถานะการจัดส่งได้เฉพาะของรางวัลประเภทสินค้า');
        }
        if (!in_array($data['status'], RewardService::PRODUCT_ADMIN_TRANSITIONS[$code->status] ?? [], true)) {
            return back()->with('error', 'ไม่สามารถเปลี่ยนจากสถานะ "' . (RewardService::STATUS_LABELS[$code->status] ?? $code->status) . '" เป็น "' . RewardService::STATUS_LABELS[$data['status']] . '" ได้');
        }
        if ($code->status === 'shipping_confirm' && !$request->boolean('address_checked')) {
            return back()->with('error', 'กรุณาตรวจสอบที่อยู่จัดส่ง แล้วติ๊ก "ตรวจสอบที่อยู่แล้ว" ก่อนยืนยัน');
        }
        if ($data['status'] === 'shipping' && empty($data['tracking_number']) && empty($code->tracking_number)) {
            return back()->with('error', 'กรุณากรอกเลขพัสดุ (Tracking) ก่อนเปลี่ยนเป็น "กำลังจัดส่ง"');
        }

        $update = ['status' => $data['status'], 'updated_at' => now()];
        if (!empty($data['shipping_carrier'])) $update['shipping_carrier'] = $data['shipping_carrier'];
        if (!empty($data['tracking_number'])) $update['tracking_number'] = $data['tracking_number'];
        if ($data['status'] === 'delivered') {
            $update['delivered_at'] = now();
            $update['used_at'] = $code->used_at ?? now();
        }

        DB::transaction(function () use ($id, $update, $data) {
            DB::table('customer_reward_codes')->where('id', $id)->update($update);
            RewardService::log((int) $id, $data['status'], $data['note'] ?? null, 'admin', auth()->id());
        });

        $label = RewardService::STATUS_LABELS[$data['status']];
        $body = 'ของรางวัล "' . $code->title_th . '" สถานะ: ' . $label;
        if ($data['status'] === 'shipping') {
            $body .= ' · ' . trim(($update['shipping_carrier'] ?? $code->shipping_carrier ?? '') . ' ' . ($update['tracking_number'] ?? $code->tracking_number ?? ''));
        }
        CustomerNotificationService::notify((int) $code->user_id, 'อัปเดตสถานะการจัดส่งของรางวัล', $body, 'privilege', 'reward_code', (int) $id);

        return back()->with('success', 'เปลี่ยนสถานะเป็น "' . $label . '" เรียบร้อยแล้ว');
    }

    /** วอยเชอร์/ส่วนลด: กรอกรหัสส่งกลับไปแสดงบนแอป (สถานะยังเป็น "ยังไม่ได้ใช้" จนกว่าลูกค้าจะกดใช้) */
    public function sendCode(Request $request, $id)
    {
        $data = $request->validate([
            'voucher_code' => 'required|string|max:255',
            'voucher_note' => 'nullable|string|max:2000',
        ]);

        $code = DB::table('customer_reward_codes as c')->join('rewards as r', 'c.reward_id', '=', 'r.id')
            ->where('c.id', $id)->select('c.*', 'r.reward_type', 'r.title_th')->first();
        abort_unless($code, 404);

        if (($code->reward_type ?: 'product') === RewardService::TYPE_PRODUCT) {
            return back()->with('error', 'ของรางวัลประเภทสินค้าใช้การจัดส่ง ไม่ต้องส่งรหัส');
        }
        if ($code->status !== 'active') {
            return back()->with('error', 'คูปองนี้ถูกใช้งานไปแล้ว ไม่สามารถแก้ไขรหัสได้');
        }

        $voucherCode = trim($data['voucher_code']);
        if (DB::table('customer_reward_codes')->where('id', '!=', $id)
            ->where(fn ($w) => $w->where('voucher_code', $voucherCode)->orWhere('code', $voucherCode))->exists()) {
            return back()->with('error', 'รหัสนี้ถูกใช้กับคูปองอื่นแล้ว');
        }

        $isResend = !empty($code->voucher_code);
        DB::transaction(function () use ($id, $voucherCode, $data, $isResend) {
            DB::table('customer_reward_codes')->where('id', $id)->update([
                'voucher_code' => $voucherCode,
                'voucher_note' => $data['voucher_note'] ?? null,
                'voucher_sent_at' => now(),
                'updated_at' => now(),
            ]);
            RewardService::log((int) $id, 'active', ($isResend ? 'แก้ไขรหัสคูปอง' : 'ส่งรหัสคูปองให้ลูกค้า') . ': ' . $voucherCode, 'admin', auth()->id());
        });

        CustomerNotificationService::notify(
            (int) $code->user_id,
            'ได้รับรหัสคูปองแล้ว',
            'รหัสสำหรับ "' . $code->title_th . '" พร้อมใช้งานแล้ว กดเพื่อดูรหัส',
            'privilege',
            'reward_code',
            (int) $id
        );

        return back()->with('success', 'ส่งรหัสคูปองให้ลูกค้าเรียบร้อยแล้ว');
    }

    /** แก้ไขที่อยู่จัดส่ง (กรณีลูกค้าโทรมาแจ้งเปลี่ยน) — ทำได้ก่อนเริ่มจัดส่ง */
    public function updateAddress(Request $request, $id)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'address_text' => 'required|string|max:2000',
        ]);

        $code = DB::table('customer_reward_codes')->where('id', $id)->first();
        abort_unless($code, 404);
        if (!in_array($code->status, ['active', 'shipping_confirm', 'processing'], true)) {
            return back()->with('error', 'แก้ไขที่อยู่ได้เฉพาะก่อนเริ่มจัดส่ง');
        }

        DB::table('customer_reward_codes')->where('id', $id)->update($data + ['address_id' => null, 'updated_at' => now()]);
        RewardService::log((int) $id, $code->status, 'แอดมินแก้ไขที่อยู่จัดส่ง', 'admin', auth()->id());

        return back()->with('success', 'แก้ไขที่อยู่จัดส่งเรียบร้อยแล้ว');
    }
}
