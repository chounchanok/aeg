<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * แจ้งเตือนภายในระบบของแผนกตัวเอง (ดู StaffNotificationService สำหรับที่มา)
 * ไม่ผูก middleware permission เพิ่ม — staff ทุกคนดูแจ้งเตือนของแผนกตัวเองได้เสมอ
 * (super_admin/it เห็นของทุกแผนกรวมกัน)
 */
class StaffNotificationAdminController extends Controller
{
    protected function myRoleIds()
    {
        return Auth::user()->roles()->pluck('roles.id');
    }

    public function index()
    {
        $user = Auth::user();
        $roleIds = $this->myRoleIds();

        $query = DB::table('staff_notifications')
            ->leftJoin('roles', 'staff_notifications.role_id', '=', 'roles.id')
            ->select('staff_notifications.*', 'roles.name as role_name')
            ->orderBy('staff_notifications.created_at', 'desc');

        // super_admin (เดิม) เห็นแจ้งเตือนของทุกแผนกรวมกัน ส่วนคนอื่นเห็นเฉพาะแผนกตัวเอง
        if ($user->role !== 'super_admin') {
            $query->where(function ($q) use ($roleIds, $user) {
                $q->whereIn('staff_notifications.role_id', $roleIds)
                  ->orWhere('staff_notifications.user_id', $user->id);
            });
        }

        $notifications = $query->limit(100)->get();

        return view('admin.staff-notifications.index', [
            'notifications' => $notifications,
            'first_level_active_index' => 'dashboard',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        $roleIds = $this->myRoleIds();

        $query = DB::table('staff_notifications')->where('is_read', false);

        if ($user->role !== 'super_admin') {
            $query->where(function ($q) use ($roleIds, $user) {
                $q->whereIn('role_id', $roleIds)
                  ->orWhere('user_id', $user->id);
            });
        }

        return response()->json(['count' => $query->count()]);
    }

    public function markRead($id)
    {
        DB::table('staff_notifications')->where('id', $id)->update(['is_read' => true, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }
}
