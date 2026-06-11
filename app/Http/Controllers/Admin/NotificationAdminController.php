<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class NotificationAdminController extends Controller
{
    // 1. หน้าแสดงประวัติการส่งแจ้งเตือน
    public function index()
    {
        // ดึงประวัติแจ้งเตือน (จำกัด 1000 รายการล่าสุด ป้องกันโหลดช้าถ้าข้อมูลเยอะ)
        $notifications = DB::table('notifications')
            ->leftJoin('users', 'notifications.user_id', '=', 'users.id')
            ->select('notifications.*', 'users.username', 'users.phone')
            ->orderBy('notifications.created_at', 'desc')
            ->limit(1000)
            ->get();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'first_level_active_index' => 'notifications',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 2. หน้าฟอร์มสร้างแจ้งเตือนใหม่
    public function create()
    {
        // ดึงรายชื่อลูกค้าทั้งหมดมาให้เลือก
        $customers = User::where('role', 'customer')->orderBy('created_at', 'desc')->get();

        return view('admin.notifications.form', [
            'customers' => $customers,
            'first_level_active_index' => 'notifications',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 3. บันทึกและส่งแจ้งเตือน
    public function store(Request $request)
    {
        $request->validate([
            'send_to' => 'required|string', // 'all' หรือระบุ ID ลูกค้า
            'type' => 'required|in:general,promotion,privilege',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $insertData = [];
            $now = now();

            if ($request->send_to === 'all') {
                // ส่งให้ลูกค้าทุกคน
                $userIds = User::where('role', 'customer')->pluck('id');
                foreach ($userIds as $uid) {
                    $insertData[] = [
                        'user_id' => $uid,
                        'title' => $request->title,
                        'body' => $request->body,
                        'type' => $request->type,
                        'is_read' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            } else {
                // ส่งให้ลูกค้ารายบุคคล
                $insertData[] = [
                    'user_id' => $request->send_to,
                    'title' => $request->title,
                    'body' => $request->body,
                    'type' => $request->type,
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // ใช้ insert batch เพื่อความรวดเร็ว (ถ้าข้อมูลเป็นหมื่นคน แนะนำให้ใช้ Job Queue ในอนาคตครับ)
            foreach (array_chunk($insertData, 500) as $chunk) {
                DB::table('notifications')->insert($chunk);
            }

            DB::commit();
            return redirect()->route('admin.notifications.index')->with('success', 'ส่งแจ้งเตือนเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    // 4. ลบแจ้งเตือน
    public function destroy($id)
    {
        DB::table('notifications')->where('id', $id)->delete();
        return back()->with('success', 'ลบแจ้งเตือนเรียบร้อยแล้ว');
    }
}