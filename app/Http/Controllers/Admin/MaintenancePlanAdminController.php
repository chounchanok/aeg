<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AddressService;
use App\Services\MaintenancePlanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 🌟 เมนู "แผนบำรุงรักษา" — ดู/ปรับแผนของลูกค้าแต่ละราย, ปฏิทินรอบที่ใกล้ถึง, เปิดใบงานตามรอบเอง
 * (สิทธิ์เดียวกับเมนูจัดการแจ้งซ่อม: service_requests.manage)
 */
class MaintenancePlanAdminController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $q = trim((string) $request->query('q'));
        $today = now()->toDateString();

        $openTicketSub = DB::table('service_requests')
            ->select('customer_product_id', DB::raw('MAX(id) as open_request_id'))
            ->where('source', 'scheduled')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->groupBy('customer_product_id');

        $query = DB::table('customer_products as cp')
            ->join('users as u', 'cp.customer_id', '=', 'u.id')
            ->leftJoin('customer_profiles as p', 'u.id', '=', 'p.user_id')
            ->leftJoin('users as t', 'cp.maintenance_technician_id', '=', 't.id')
            ->leftJoin('customer_profiles as tp', 't.id', '=', 'tp.user_id')
            ->leftJoinSub($openTicketSub, 'ot', 'ot.customer_product_id', '=', 'cp.id')
            ->leftJoin('service_requests as sr', 'sr.id', '=', 'ot.open_request_id')
            ->whereNotNull('cp.visit_interval_months')
            ->select('cp.*', 'u.phone', 'u.username', 'p.first_name', 'p.last_name',
                DB::raw('COALESCE(tp.first_name, t.username) as technician_name'),
                'sr.id as open_request_id', 'sr.ticket_number as open_ticket', 'sr.preferred_date as open_date', 'sr.status as open_status');

        if ($request->filled('customer_id')) $query->where('cp.customer_id', (int) $request->customer_id);
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('p.first_name', 'like', "%{$q}%")->orWhere('p.last_name', 'like', "%{$q}%")
                ->orWhere('u.phone', 'like', "%{$q}%")->orWhere('cp.product_name', 'like', "%{$q}%")->orWhere('cp.serial_number', 'like', "%{$q}%"));
        }
        if ($filter === 'due30') $query->where('cp.auto_schedule', true)->whereBetween('cp.next_service_date', [$today, now()->addDays(30)->toDateString()]);
        if ($filter === 'overdue') $query->whereNotNull('cp.next_service_date')->where('cp.next_service_date', '<', $today)->whereNull('sr.id');
        if ($filter === 'off') $query->where('cp.auto_schedule', false);
        if ($filter === 'open') $query->whereNotNull('sr.id');

        $plans = $query->orderByRaw('cp.next_service_date IS NULL, cp.next_service_date ASC')->get();
        foreach ($plans as $pl) {
            $pl->remaining = MaintenancePlanService::remainingVisits($pl);
            $pl->block_reason = MaintenancePlanService::blockReason($pl, true);
        }

        $base = DB::table('customer_products')->whereNotNull('visit_interval_months');
        $stats = [
            'plans' => (clone $base)->count(),
            'auto' => (clone $base)->where('auto_schedule', true)->where('status', 'active')->count(),
            'due30' => (clone $base)->where('auto_schedule', true)->whereBetween('next_service_date', [$today, now()->addDays(30)->toDateString()])->count(),
            'open' => DB::table('service_requests')->where('source', 'scheduled')->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'unassigned' => DB::table('service_requests')->where('source', 'scheduled')->where('status', 'pending')->count(),
        ];

        // แพ็กเกจที่ลูกค้าซื้อไว้ก่อนมีระบบนี้ แต่บริการต้นทางตั้งแผนไว้แล้ว → ปุ่ม "ใช้แผนจากบริการ"
        $withoutPlan = DB::table('customer_products as cp')
            ->join('products as pr', function ($j) {
                $j->on('cp.reference_id', '=', 'pr.id')->where('cp.reference_type', '=', 'product');
            })
            ->whereNull('cp.visit_interval_months')->whereNotNull('pr.visit_interval_months')
            ->where('cp.status', 'active')
            ->count();

        // ปฏิทินรอบที่ใกล้ถึง 60 วัน
        $upcoming = $plans->filter(fn ($p) => $p->next_service_date && $p->next_service_date <= now()->addDays(60)->toDateString())
            ->groupBy(fn ($p) => $p->next_service_date);

        $technicians = DB::table('users')->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->where('users.role', 'technician')->where('users.is_active', true)
            ->select('users.id', DB::raw('COALESCE(customer_profiles.first_name, users.username) as name'))->orderBy('name')->get();

        $addresses = DB::table('customer_addresses')->whereIn('user_id', $plans->pluck('customer_id')->unique()->all())
            ->whereNull('deleted_at')->get()->groupBy('user_id');

        return view('admin.maintenance-plans.index', [
            'plans' => $plans,
            'stats' => $stats,
            'withoutPlan' => $withoutPlan,
            'upcoming' => $upcoming,
            'technicians' => $technicians,
            'addresses' => $addresses,
            'filter' => $filter,
            'q' => $q,
            'first_level_active_index' => 'maintenance-plans',
            'second_level_active_index' => '',
            'third_level_active_index' => '',
        ]);
    }

    public function update(Request $request, $id)
    {
        $cp = DB::table('customer_products')->where('id', $id)->first();
        abort_unless($cp, 404);

        $data = $request->validate([
            'visit_interval_months' => 'required|integer|min:1|max:60',
            'next_service_date' => 'nullable|date',
            'preferred_day' => 'nullable|integer|min:1|max:28',
            'preferred_time_slot' => 'nullable|in:' . implode(',', MaintenancePlanService::TIME_SLOTS),
            'maintenance_technician_id' => 'nullable|exists:users,id',
            'maintenance_address_id' => 'nullable|exists:customer_addresses,id',
            'ticket_lead_days' => 'nullable|integer|min:0|max:90',
            'total_service_count' => 'nullable|integer|min:0|max:1000',
            'warranty_expire_date' => 'nullable|date',
            'maintenance_note' => 'nullable|string|max:500',
        ], [
            'visit_interval_months.required' => 'กรุณากรอกความถี่การเข้าบริการ (เดือน)',
        ]);

        if (!empty($data['maintenance_address_id'])
            && !DB::table('customer_addresses')->where('id', $data['maintenance_address_id'])->where('user_id', $cp->customer_id)->exists()) {
            return back()->with('error', 'ที่อยู่ที่เลือกไม่ใช่ของลูกค้ารายนี้');
        }

        DB::table('customer_products')->where('id', $id)->update([
            'visit_interval_months' => (int) $data['visit_interval_months'],
            'next_service_date' => $data['next_service_date'] ?? null,
            'preferred_day' => $data['preferred_day'] ?? null,
            'preferred_time_slot' => $data['preferred_time_slot'] ?? null,
            'maintenance_technician_id' => $data['maintenance_technician_id'] ?? null,
            'maintenance_address_id' => $data['maintenance_address_id'] ?? null,
            'ticket_lead_days' => $data['ticket_lead_days'] ?? 10,
            'total_service_count' => $data['total_service_count'] ?? ($cp->total_service_count ?? 0),
            'warranty_expire_date' => $data['warranty_expire_date'] ?? $cp->warranty_expire_date,
            'maintenance_note' => $data['maintenance_note'] ?? null,
            'auto_schedule' => $request->boolean('auto_schedule'),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'บันทึกแผนบำรุงรักษาของ ' . $cp->product_name . ' แล้ว');
    }

    /** ตั้งแผนให้แพ็กเกจของลูกค้าที่ยังไม่มีแผน (ครั้งแรก) */
    public function store(Request $request, $id)
    {
        $cp = DB::table('customer_products')->where('id', $id)->first();
        abort_unless($cp, 404);
        $request->validate(['visit_interval_months' => 'required|integer|min:1|max:60']);

        $start = $cp->purchase_date ?: now();
        DB::table('customer_products')->where('id', $id)->update([
            'visit_interval_months' => (int) $request->visit_interval_months,
            'next_service_date' => MaintenancePlanService::nextDate($start, (int) $request->visit_interval_months)->max(now()->startOfDay())->toDateString(),
            'ticket_lead_days' => 10,
            'auto_schedule' => true,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.maintenance-plans.index', ['customer_id' => $cp->customer_id])
            ->with('success', 'สร้างแผนบำรุงรักษาให้ ' . $cp->product_name . ' แล้ว — ตรวจสอบวันครบรอบและช่างประจำได้ที่ปุ่ม "แก้ไขแผน"');
    }

    /** เปิดใบงานรอบนี้ทันที (ไม่ต้องรอถึงช่วงเปิดล่วงหน้า) */
    public function run($id)
    {
        $r = MaintenancePlanService::createTicket((int) $id, (int) Auth::id(), true);
        if ($r['ok']) {
            return redirect()->route('admin.service-requests.show', $r['request_id'])->with('success', $r['message']);
        }
        return back()->with('error', 'เปิดใบงานไม่ได้: ' . $r['message']);
    }

    /** รันรอบอัตโนมัติตอนนี้ (เหมือน cron รายวัน) */
    public function runDue()
    {
        $r = MaintenancePlanService::generateDue();
        return back()->with('success', "เปิดใบงานใหม่ {$r['created']} ใบ" . ($r['errors'] ? ' (ผิดพลาด ' . count($r['errors']) . ' รายการ ดูใน log)' : ''));
    }

    /** คัดลอกแผนจากบริการต้นทาง ให้แพ็กเกจเดิมที่ซื้อก่อนมีระบบแผนบำรุงรักษา */
    public function applyDefaults()
    {
        $ids = DB::table('customer_products as cp')
            ->join('products as pr', function ($j) {
                $j->on('cp.reference_id', '=', 'pr.id')->where('cp.reference_type', '=', 'product');
            })
            ->whereNull('cp.visit_interval_months')->whereNotNull('pr.visit_interval_months')
            ->where('cp.status', 'active')
            ->pluck('cp.id');

        foreach ($ids as $cpId) {
            MaintenancePlanService::applyFromProduct((int) $cpId);
            // รอบแรกที่คำนวณได้อยู่ในอดีต (ซื้อนานแล้ว) → เลื่อนเป็นรอบถัดไปที่ยังไม่ถึง
            $cp = DB::table('customer_products')->where('id', $cpId)->first();
            $next = Carbon::parse($cp->next_service_date);
            while ($next->lt(now()->startOfDay())) {
                $next = MaintenancePlanService::nextDate($next, (int) $cp->visit_interval_months);
            }
            DB::table('customer_products')->where('id', $cpId)->update(['next_service_date' => $next->toDateString()]);
        }

        return back()->with('success', 'ตั้งแผนบำรุงรักษาจากบริการต้นทางให้ ' . $ids->count() . ' แพ็กเกจแล้ว');
    }
}
