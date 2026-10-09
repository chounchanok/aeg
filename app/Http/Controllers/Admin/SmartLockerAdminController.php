<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmartLockerAdminController extends Controller
{
    public function index()
    {
        $lockers = DB::table('smart_lockers')->orderBy('type')->orderBy('locker_number')->get();

        return view('admin.smart-lockers.index', [
            'lockers' => $lockers,
            'first_level_active_index' => 'smart-lockers',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    /**
     * ดึงรายการจองตู้เซฟ (พร้อมชื่อลูกค้า/เบอร์/เลขที่ออเดอร์) ที่ทับช่วงวันที่ [from, to] — ใช้ทั้งปฏิทินรายตู้และปฏิทินรวม
     * รวมทั้งตู้ที่แอดมินผูกให้ลูกค้าเองจากหน้า "ลูกค้าและแพ็กเกจ" (customer_products.reference_type = locker)
     */
    private function bookingsBetween(Carbon $from, Carbon $to, $lockerId = null)
    {
        $bookings = DB::table('locker_bookings as b')
            ->leftJoin('users as u', 'b.user_id', '=', 'u.id')
            ->leftJoin('customer_profiles as cp', 'b.user_id', '=', 'cp.user_id')
            ->when($lockerId, fn ($q) => $q->where('b.smart_locker_id', $lockerId))
            ->whereIn('b.status', ['pending_payment', 'paid', 'active', 'completed'])
            ->where('b.start_date', '<', $to->copy()->addDay()->toDateString())
            ->where('b.end_date', '>', $from->toDateString())
            ->orderBy('b.start_date')
            ->get([
                'b.id', 'b.smart_locker_id', 'b.booking_number', 'b.start_date', 'b.end_date', 'b.status',
                'b.total_amount', 'b.renewal_of_booking_id', 'b.user_id',
                'u.username', 'u.phone', 'cp.first_name', 'cp.last_name',
            ])
            ->map(function ($b) {
                $b->source = 'booking';
                $b->customer_name = trim(($b->first_name ?? '') . ' ' . ($b->last_name ?? '')) ?: ($b->username ?? '-');
                return $b;
            });

        // ตู้ที่แอดมินเพิ่มให้ลูกค้าโดยตรง (ไม่ได้ผ่านการจองในแอป) — วันสิ้นสุด = warranty_expire_date (นับรวมวันนั้น)
        $assigned = DB::table('customer_products as p')
            ->leftJoin('users as u', 'p.customer_id', '=', 'u.id')
            ->leftJoin('customer_profiles as cp', 'p.customer_id', '=', 'cp.user_id')
            ->where('p.reference_type', 'locker')
            ->where('p.status', 'active')
            ->when($lockerId, fn ($q) => $q->where('p.reference_id', $lockerId))
            ->whereNotNull('p.purchase_date')
            ->where('p.purchase_date', '<=', $to->toDateString())
            ->where(function ($q) use ($from) {
                $q->whereNull('p.warranty_expire_date')->orWhere('p.warranty_expire_date', '>=', $from->toDateString());
            })
            ->get(['p.id', 'p.reference_id as smart_locker_id', 'p.purchase_date', 'p.warranty_expire_date', 'p.customer_id as user_id', 'u.username', 'u.phone', 'cp.first_name', 'cp.last_name'])
            ->map(function ($p) use ($to) {
                return (object) [
                    'id' => 'cp-' . $p->id,
                    'smart_locker_id' => (int) $p->smart_locker_id,
                    'booking_number' => 'ADMIN-' . $p->id,
                    'start_date' => Carbon::parse($p->purchase_date)->toDateString(),
                    'end_date' => $p->warranty_expire_date
                        ? Carbon::parse($p->warranty_expire_date)->addDay()->toDateString()
                        : $to->copy()->addDay()->toDateString(),
                    'status' => 'active',
                    'total_amount' => null,
                    'renewal_of_booking_id' => null,
                    'user_id' => $p->user_id,
                    'phone' => $p->phone,
                    'source' => 'admin_assigned',
                    'customer_name' => trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: ($p->username ?? '-'),
                ];
            });

        return $bookings->concat($assigned)->values();
    }

    private function resolveMonth(Request $request): Carbon
    {
        $month = $request->query('month');
        try {
            return $month ? Carbon::createFromFormat('!Y-m', $month)->startOfMonth() : Carbon::today()->startOfMonth();
        } catch (\Throwable $e) {
            return Carbon::today()->startOfMonth();
        }
    }

    // 🌟 ปฏิทินรายตู้ (รายเดือน) — แต่ละวันแสดงว่าว่าง/ไม่ว่าง และถ้าไม่ว่างเพราะมีจอง จะแสดงชื่อลูกค้า + เลขที่ออเดอร์
    public function availability(Request $request, $id)
    {
        $locker = DB::table('smart_lockers')->where('id', $id)->first();
        abort_unless($locker, 404);

        $monthStart = $this->resolveMonth($request);
        $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY)->startOfDay();

        $bookings = $this->bookingsBetween($gridStart, $gridEnd, $id);
        $manualBlocks = DB::table('smart_locker_unavailable_dates')
            ->where('smart_locker_id', $id)
            ->whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        $weeks = [];
        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $date = $day->toDateString();
            $dayBookings = $bookings->filter(fn ($b) => $b->start_date <= $date && $b->end_date > $date)->values();
            $block = $manualBlocks->get($date);
            $weeks[intdiv((int) round($gridStart->diffInDays($day)), 7)][] = (object) [
                'date' => $date,
                'day' => $day->day,
                'in_month' => $day->month === $monthStart->month,
                'is_today' => $day->isToday(),
                'is_available' => $dayBookings->isEmpty() && !$block && $locker->status !== 'maintenance',
                'bookings' => $dayBookings,
                'block' => $block,
                'maintenance' => $locker->status === 'maintenance',
            ];
        }

        // รายการจองทั้งหมดของตู้นี้ในเดือนที่เลือก (ตารางด้านล่างปฏิทิน)
        $monthBookings = $bookings->filter(fn ($b) => $b->start_date <= $monthEnd->toDateString() && $b->end_date > $monthStart->toDateString())->values();

        return view('admin.smart-lockers.availability', [
            'locker' => $locker,
            'weeks' => $weeks,
            'monthStart' => $monthStart,
            'monthBookings' => $monthBookings,
            'first_level_active_index' => 'smart-lockers',
            'second_level_active_index' => '',
            'third_level_active_index' => '',
        ]);
    }

    // 🌟 ปฏิทินรวมทุกตู้ (Timeline รายเดือน) — แถว = ตู้, คอลัมน์ = วันที่ ดูภาพรวมว่าใครจองตู้ไหน ออเดอร์ไหน
    public function calendar(Request $request)
    {
        $monthStart = $this->resolveMonth($request);
        $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();

        $lockers = DB::table('smart_lockers')->orderBy('type')->orderBy('locker_number')->get();
        $bookings = $this->bookingsBetween($monthStart, $monthEnd)->groupBy('smart_locker_id');
        $blocks = DB::table('smart_locker_unavailable_dates')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()->groupBy('smart_locker_id');

        $days = [];
        for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
            $days[] = $day->copy();
        }

        $rows = $lockers->map(function ($locker) use ($days, $bookings, $blocks) {
            $lockerBookings = $bookings->get($locker->id, collect());
            $lockerBlocks = ($blocks->get($locker->id) ?? collect())->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());
            $cells = [];
            foreach ($days as $day) {
                $date = $day->toDateString();
                $booking = $lockerBookings->first(fn ($b) => $b->start_date <= $date && $b->end_date > $date);
                $cells[] = (object) [
                    'date' => $date,
                    'booking' => $booking,
                    'block' => $lockerBlocks->get($date),
                    'maintenance' => $locker->status === 'maintenance',
                ];
            }
            return (object) ['locker' => $locker, 'cells' => $cells, 'bookings' => $lockerBookings];
        });

        return view('admin.smart-lockers.calendar', [
            'rows' => $rows,
            'days' => $days,
            'monthStart' => $monthStart,
            'first_level_active_index' => 'smart-lockers',
            'second_level_active_index' => '',
            'third_level_active_index' => '',
        ]);
    }

    public function updateAvailability(Request $request, $id)
    {
        $locker = DB::table('smart_lockers')->where('id', $id)->first();
        abort_unless($locker, 404);

        $data = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'availability' => 'required|in:available,unavailable',
            'note' => 'nullable|string|max:255',
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();
        if ($start->diffInDays($end) > 365) {
            return back()->withErrors(['end_date' => 'กำหนดช่วงเวลาได้ไม่เกิน 366 วัน'])->withInput();
        }

        DB::transaction(function () use ($data, $id, $start, $end) {
            for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                $date = $day->toDateString();
                if ($data['availability'] === 'unavailable') {
                    DB::table('smart_locker_unavailable_dates')->updateOrInsert(
                        ['smart_locker_id' => $id, 'date' => $date],
                        ['note' => $data['note'] ?? null, 'created_by' => auth()->id(), 'updated_at' => now(), 'created_at' => now()]
                    );
                } else {
                    DB::table('smart_locker_unavailable_dates')->where('smart_locker_id', $id)->where('date', $date)->delete();
                }
            }
        });

        return redirect()->route('admin.smart-lockers.availability', $id)->with('success', 'อัปเดตวันว่างของตู้เซฟเรียบร้อยแล้ว');
    }

    public function store(Request $request)
    {
        $request->validate([
            'locker_number' => 'required|unique:smart_lockers',
            'type' => 'required|in:PRIME,PRIVILEGE',
            'title_th' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|in:available,rented,maintenance',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('smart-lockers', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('smart_lockers')->insert([
            'locker_number' => $request->locker_number,
            'type' => $request->type,
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'price' => $request->price,
            'image_url' => $imageUrl,
            'status' => $request->status ?? 'available',
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มตู้เซฟเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'locker_number' => 'required|unique:smart_lockers,locker_number,' . $id,
            'title_th' => 'required|string',
            'price' => 'required|numeric',
            'status' => 'nullable|in:available,rented,maintenance',
        ]);

        $locker = DB::table('smart_lockers')->where('id', $id)->first();
        $imageUrl = $locker->image_url;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('smart-lockers', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('smart_lockers')->where('id', $id)->update([
            'locker_number' => $request->locker_number,
            'type' => $request->type,
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'price' => $request->price,
            'image_url' => $imageUrl,
            'status' => $request->status ?? 'available',
            'is_active' => $request->has('is_active'),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'อัปเดตตู้เซฟเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        DB::table('smart_lockers')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'ลบตู้เซฟเรียบร้อยแล้ว');
    }
}
