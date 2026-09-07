<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * รายการติดต่อจากลูกค้า (ฟอร์มติดต่อหน้าเว็บ/แอป) — /admin/contacts/{type}
 *
 * รวมทุกช่องทางไว้เมนูเดียว แต่แยกสิทธิ์ตาม {type} ให้แต่ละแผนกเห็นเฉพาะของตัวเอง (RBAC):
 *   insurance → insurance_contacts      → Insurance     (contacts.insurance)
 *   safe      → safe_contacts           → Smart Locker  (contacts.safe)
 *   product   → product_contacts        → Sales Admin   (contacts.product)
 *   sales     → contact_admin_requests  → Sales Admin   (contacts.sales)   ← ฟอร์ม "ติดต่อฝ่ายขาย" จากแอป
 *
 * ทุกการดำเนินการ (เปลี่ยนสถานะ / มอบหมาย / โทร / อีเมล / นัดหมาย / บันทึก) ถูกเก็บลง contact_activities
 * เพื่อให้ย้อนดูได้ว่ารายการนี้ "ทำอะไรไปแล้วบ้าง ใครทำ เมื่อไหร่"
 */
class ContactAdminController extends Controller
{
    public const TYPES = [
        'insurance' => [
            'table' => 'insurance_contacts',
            'title' => 'รายการติดต่อเรื่องประกันภัย',
            'short' => 'ประกันภัย',
            'permission' => 'contacts.insurance',
            'role' => 'insurance_admin',
            'source' => 'หน้าเว็บ — ฟอร์มติดต่อผู้เชี่ยวชาญประกันภัย (/insurance/{id}/contact)',
            'ref' => ['table' => 'insurances', 'column' => 'insurance_id', 'label' => 'title_th', 'caption' => 'ประกันภัยที่สนใจ'],
        ],
        'safe' => [
            'table' => 'safe_contacts',
            'title' => 'รายการติดต่อเรื่องตู้เซฟนิรภัย',
            'short' => 'ตู้เซฟนิรภัย',
            'permission' => 'contacts.safe',
            'role' => 'smart_locker',
            'source' => 'หน้าเว็บ — ฟอร์มติดต่อเช่าตู้เซฟ (/safe-contact/{id}/contact)',
            'ref' => ['table' => 'smart_lockers', 'column' => 'smart_locker_id', 'label' => 'title_th', 'caption' => 'ตู้เซฟที่สนใจ'],
        ],
        'product' => [
            'table' => 'product_contacts',
            'title' => 'รายการติดต่อเรื่องสินค้า/บริการ (เว็บ)',
            'short' => 'สินค้า/บริการ (เว็บ)',
            'permission' => 'contacts.product',
            'role' => 'sales_admin',
            'source' => 'หน้าเว็บ — ฟอร์มติดต่อฝ่ายขายจากหน้าสินค้า (/product-contact/{id}/contact)',
            'ref' => ['table' => 'products', 'column' => 'product_id', 'label' => 'name_th', 'caption' => 'สินค้า/บริการที่สนใจ'],
        ],
        'sales' => [
            'table' => 'contact_admin_requests',
            'title' => 'คำขอติดต่อฝ่ายขาย (แอปมือถือ)',
            'short' => 'ติดต่อฝ่ายขาย (แอป)',
            'permission' => 'contacts.sales',
            'role' => 'sales_admin',
            'source' => 'แอปมือถือ — ฟอร์มติดต่อฝ่ายขาย / ขอรับคำปรึกษา (POST /api/user/contact-admin)',
            'ref' => ['table' => 'products', 'column' => 'product_id', 'label' => 'name_th', 'caption' => 'สินค้า/บริการที่สนใจ'],
        ],
    ];

    public const STATUSES = [
        'pending' => ['label' => 'รอดำเนินการ', 'class' => 'text-warning bg-warning/20'],
        'contacted' => ['label' => 'ติดต่อแล้ว', 'class' => 'text-primary bg-primary/20'],
        'closed' => ['label' => 'ปิดงาน', 'class' => 'text-success bg-success/20'],
    ];

    // ประเภทการดำเนินการที่พนักงานบันทึกเองได้ (status_changed / assigned ระบบบันทึกให้อัตโนมัติ)
    public const ACTIONS = [
        'call' => ['label' => 'โทรติดต่อลูกค้า', 'icon' => 'phone'],
        'email' => ['label' => 'ส่งอีเมล', 'icon' => 'mail'],
        'chat' => ['label' => 'ติดต่อผ่านแชท / LINE', 'icon' => 'message-circle'],
        'appointment' => ['label' => 'นัดหมายลูกค้า', 'icon' => 'calendar'],
        'quotation' => ['label' => 'ส่งใบเสนอราคา / ข้อมูลเพิ่มเติม', 'icon' => 'file-text'],
        'note' => ['label' => 'บันทึกทั่วไป', 'icon' => 'edit-3'],
    ];

    // ใช้แสดง timeline สำหรับ action ที่ระบบสร้างเอง
    public const SYSTEM_ACTIONS = [
        'status_changed' => ['label' => 'เปลี่ยนสถานะ', 'icon' => 'refresh-cw'],
        'assigned' => ['label' => 'มอบหมายผู้รับผิดชอบ', 'icon' => 'user-check'],
    ];

    /**
     * คืน config ของ type นี้ พร้อมเช็คสิทธิ์ (403 ถ้าแผนกนี้ไม่มีสิทธิ์ดูช่องทางนี้)
     */
    protected function resolveType(string $type): array
    {
        $config = self::TYPES[$type] ?? abort(404);

        if (!Gate::allows($config['permission'])) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายการติดต่อช่องทางนี้ กรุณาติดต่อฝ่าย IT เพื่อขอสิทธิ์การใช้งาน');
        }

        return $config;
    }

    /**
     * query พื้นฐานของตารางติดต่อ + join ชื่อสิ่งที่ลูกค้าสนใจ + ชื่อพนักงานที่รับผิดชอบ
     */
    protected function baseQuery(array $config)
    {
        $t = $config['table'];
        $ref = $config['ref'];

        return DB::table("{$t} as c")
            ->leftJoin("{$ref['table']} as r", "c.{$ref['column']}", '=', 'r.id')
            ->leftJoin('users as a', 'c.assigned_to', '=', 'a.id')
            ->leftJoin('customer_profiles as ap', 'a.id', '=', 'ap.user_id')
            ->select(
                'c.*',
                "r.{$ref['label']} as interest_label",
                'a.username as assigned_username',
                'ap.first_name as assigned_first_name'
            );
    }

    /**
     * แปลงแถวจากแต่ละตาราง (คอลัมน์ไม่เหมือนกัน) ให้เป็นรูปแบบเดียวกันสำหรับ view
     */
    protected function normalize(string $type, object $row): object
    {
        $isSales = $type === 'sales';

        $row->display_name = $isSales
            ? trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''))
            : ($row->name ?? '-');
        $row->display_ref = $isSales ? ($row->request_number ?? ('#' . $row->id)) : ('#' . $row->id);
        $row->display_contact_time = $isSales ? ($row->preferred_contact_time ?? null) : ($row->contact_time ?? null);
        $row->display_message = $isSales ? ($row->detail ?? null) : ($row->message ?? null);
        $row->assigned_name = $row->assigned_to ? ($row->assigned_first_name ?: $row->assigned_username) : null;

        if (!isset(self::STATUSES[$row->status])) {
            $row->status = 'pending';
        }

        return $row;
    }

    public function index(Request $request, string $type)
    {
        $config = $this->resolveType($type);
        $statusFilter = $request->query('status', 'pending');

        $query = $this->baseQuery($config);
        if ($statusFilter !== 'all' && isset(self::STATUSES[$statusFilter])) {
            $query->where('c.status', $statusFilter);
        }

        $contacts = $query->orderBy('c.created_at', 'desc')->get()
            ->map(fn ($row) => $this->normalize($type, $row));

        // จำนวนต่อสถานะ สำหรับแท็บด้านบน
        $counts = DB::table($config['table'])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.contacts.index', [
            'type' => $type,
            'config' => $config,
            'contacts' => $contacts,
            'counts' => $counts,
            'statusFilter' => $statusFilter,
            'statuses' => self::STATUSES,
            'first_level_active_index' => 'contacts',
            'second_level_active_index' => $type,
            'third_level_active_index' => ''
        ]);
    }

    public function show(string $type, $id)
    {
        $config = $this->resolveType($type);

        $contact = $this->baseQuery($config)->where('c.id', $id)->first();
        if (!$contact) {
            abort(404, 'ไม่พบรายการติดต่อนี้');
        }
        $contact = $this->normalize($type, $contact);

        $activities = DB::table('contact_activities as ca')
            ->leftJoin('users as u', 'ca.staff_id', '=', 'u.id')
            ->leftJoin('customer_profiles as p', 'u.id', '=', 'p.user_id')
            ->where('ca.contact_type', $type)
            ->where('ca.contact_id', $id)
            ->select('ca.*', 'u.username as staff_username', 'p.first_name as staff_first_name')
            ->orderBy('ca.created_at', 'desc')
            ->get();

        return view('admin.contacts.show', [
            'type' => $type,
            'config' => $config,
            'contact' => $contact,
            'activities' => $activities,
            'assignableStaff' => $this->assignableStaff($config['role']),
            'statuses' => self::STATUSES,
            'actions' => self::ACTIONS,
            'actionLabels' => array_map(fn ($a) => $a['label'], self::ACTIONS + self::SYSTEM_ACTIONS),
            'actionIcons' => array_map(fn ($a) => $a['icon'], self::ACTIONS + self::SYSTEM_ACTIONS),
            'first_level_active_index' => 'contacts',
            'second_level_active_index' => $type,
            'third_level_active_index' => ''
        ]);
    }

    /**
     * บันทึกการดำเนินการ + เปลี่ยนสถานะ + มอบหมายผู้รับผิดชอบ (ฟอร์มเดียว) — ทุกอย่างลง timeline
     */
    public function update(Request $request, string $type, $id)
    {
        $config = $this->resolveType($type);

        $contact = DB::table($config['table'])->where('id', $id)->first();
        if (!$contact) {
            abort(404, 'ไม่พบรายการติดต่อนี้');
        }

        $validated = $request->validate([
            'action' => 'nullable|in:' . implode(',', array_keys(self::ACTIONS)),
            'note' => 'nullable|string|max:2000',
            'status' => 'required|in:' . implode(',', array_keys(self::STATUSES)),
            'assigned_to' => 'nullable|integer|exists:users,id',
        ], [
            'status.required' => 'กรุณาเลือกสถานะ',
            'assigned_to.exists' => 'ไม่พบพนักงานที่เลือก',
        ]);

        $staffId = Auth::id();
        $note = trim((string) ($validated['note'] ?? ''));
        $newStatus = $validated['status'];
        $newAssignee = $validated['assigned_to'] ?? null;
        $currentAssignee = $contact->assigned_to ?? null;
        $logged = 0;

        DB::transaction(function () use ($config, $type, $id, $contact, $staffId, $note, $newStatus, $newAssignee, $currentAssignee, $validated, &$logged) {
            $updates = ['updated_at' => now()];

            // 1) การดำเนินการที่พนักงานบันทึกเอง (โทร/อีเมล/นัดหมาย/บันทึก)
            if (!empty($validated['action'])) {
                if ($note === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'note' => 'กรุณาระบุรายละเอียดการดำเนินการ',
                    ]);
                }
                $this->logActivity($type, $id, $staffId, $validated['action'], null, null, $note);
                $logged++;
            }

            // 2) เปลี่ยนสถานะ
            if ($newStatus !== $contact->status) {
                $updates['status'] = $newStatus;
                $this->logActivity($type, $id, $staffId, 'status_changed', $contact->status, $newStatus,
                    empty($validated['action']) && $note !== '' ? $note : null);
                $logged++;
            }

            // 3) มอบหมายผู้รับผิดชอบ
            if ((int) $newAssignee !== (int) $currentAssignee) {
                $updates['assigned_to'] = $newAssignee;
                $assigneeName = $newAssignee ? $this->staffName($newAssignee) : null;
                $this->logActivity($type, $id, $staffId, 'assigned', null, null,
                    $assigneeName ? "มอบหมายให้: {$assigneeName}" : 'ยกเลิกการมอบหมายผู้รับผิดชอบ');
                $logged++;
            }

            // 4) กรอกแค่โน้ตอย่างเดียว (ไม่เลือกประเภท ไม่เปลี่ยนสถานะ) → เก็บเป็นบันทึกทั่วไป
            if ($logged === 0 && $note !== '') {
                $this->logActivity($type, $id, $staffId, 'note', null, null, $note);
                $logged++;
            }

            if (count($updates) > 1) {
                DB::table($config['table'])->where('id', $id)->update($updates);
            }
        });

        $message = $logged > 0 ? 'บันทึกการดำเนินการเรียบร้อยแล้ว' : 'ไม่มีการเปลี่ยนแปลง';

        return redirect()->route('admin.contacts.show', ['type' => $type, 'id' => $id])->with('success', $message);
    }

    protected function logActivity(string $type, $contactId, ?int $staffId, string $action, ?string $from, ?string $to, ?string $note): void
    {
        DB::table('contact_activities')->insert([
            'contact_type' => $type,
            'contact_id' => $contactId,
            'staff_id' => $staffId,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function staffName(int $userId): ?string
    {
        $row = DB::table('users as u')
            ->leftJoin('customer_profiles as p', 'u.id', '=', 'p.user_id')
            ->where('u.id', $userId)
            ->select('u.username', 'p.first_name')
            ->first();

        return $row ? ($row->first_name ?: $row->username) : null;
    }

    /**
     * พนักงานที่มอบหมายรายการช่องทางนี้ให้ได้ = คนในแผนกเจ้าของช่องทาง + แผนกที่ full access (IT)
     * + super_admin เดิม (backward-compat) — เฉพาะบัญชีที่ยังเปิดใช้งาน
     */
    protected function assignableStaff(string $roleKey)
    {
        return DB::table('users as u')
            ->leftJoin('customer_profiles as p', 'u.id', '=', 'p.user_id')
            ->where('u.role', '!=', 'customer')
            ->where(function ($q) {
                $q->whereNull('u.is_active')->orWhere('u.is_active', true);
            })
            ->where(function ($q) use ($roleKey) {
                $q->where('u.role', 'super_admin')
                    ->orWhereExists(function ($sub) use ($roleKey) {
                        $sub->select(DB::raw(1))
                            ->from('user_roles as ur')
                            ->join('roles as r', 'ur.role_id', '=', 'r.id')
                            ->whereColumn('ur.user_id', 'u.id')
                            ->where(function ($w) use ($roleKey) {
                                $w->where('r.key', $roleKey)->orWhere('r.is_full_access', true);
                            });
                    });
            })
            ->select('u.id', 'u.username', 'p.first_name')
            ->orderBy('p.first_name')
            ->get()
            ->map(function ($s) {
                $s->display_name = $s->first_name ?: $s->username;
                return $s;
            });
    }
}
