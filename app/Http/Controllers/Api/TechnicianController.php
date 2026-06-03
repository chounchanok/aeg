<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TechnicianController extends Controller
{
    // 1. ดึงงานที่มอบหมายให้ช่างคนนี้
    public function getMyTasks(Request $request)
    {
        $techId = $request->user()->id;

        $tasks = DB::table('service_requests')
            ->join('users', 'service_requests.customer_id', '=', 'users.id')
            ->join('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->where('service_requests.technician_id', $techId)
            ->select(
                'service_requests.id',
                'service_requests.ticket_number',
                'service_requests.status',
                'service_requests.problem_description',
                'service_requests.preferred_date',
                'customer_products.product_name',
                'users.phone as customer_phone'
            )
            ->orderBy('service_requests.created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $tasks]);
    }

    // 2. อัปเดตสถานะงาน (ช่างกดรับงาน, ซ่อมเสร็จ)
    public function updateTaskStatus(Request $request, $id)
    {
        $techId = $request->user()->id;
        $request->validate([
            'status' => 'required|in:in_progress,completed' // ช่างอัปเดตได้แค่กำลังซ่อม กับ เสร็จแล้ว
        ]);

        $task = DB::table('service_requests')->where('id', $id)->where('technician_id', $techId)->first();
        if (!$task) {
            return response()->json(['status' => 'error', 'message' => 'Not found or Unauthorized'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('service_requests')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            $statusLabels = [
                'in_progress' => 'ช่างกำลังดำเนินการซ่อม',
                'completed' => 'ซ่อมเสร็จสิ้น'
            ];

            // บันทึก Tracking Log
            DB::table('service_request_tracking')->insert([
                'service_request_id' => $id,
                'status' => $statusLabels[$request->status],
                'description' => 'อัปเดตสถานะโดยช่างผู้รับผิดชอบ',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ถ้าระบุว่าซ่อมเสร็จ ให้ไปตัดโควต้าใน customer_products (used_service_count + 1)
            if ($request->status === 'completed') {
                DB::table('customer_products')->where('id', $task->customer_product_id)->increment('used_service_count');
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Task status updated']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
