<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;
use App\Services\StaffNotificationService;

class ServiceRequestController extends Controller
{
    use ApiResponseTrait;

    public function createRequest(Request $request)
    {
        $request->validate([
            'customer_product_id' => 'required|integer',
            'problem_description' => 'required|string',
            'address_id' => 'nullable|integer',
            'custom_address_text' => 'nullable|string|required_without:address_id',
            'preferred_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|in:09:00-12:00,13:00-18:00',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = $request->user();

        $product = DB::table('customer_products')->where('id', $request->customer_product_id)->where('customer_id', $user->id)->first();
        if (!$product) return $this->errorResponse('ไม่พบแพ็กเกจหรือบริการที่เลือกระบุ', 404);

        DB::beginTransaction();
        try {
            $requestId = DB::table('service_requests')->insertGetId([
                'ticket_number' => 'SR-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                'customer_id' => $user->id, // แก้ไขให้ตรงกับเฟส 1
                'service_type' => 'Repair', // เพิ่มค่า Default ให้ตรงกับกฎเฟส 1
                'customer_product_id' => $product->id,
                'problem_description' => $request->problem_description,
                'address_id' => $request->address_id,
                'custom_address_text' => $request->custom_address_text,
                'preferred_date' => $request->preferred_date,
                'time_slot' => $request->time_slot,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($request->hasFile('images')) {
                $imageData = [];
                foreach ($request->file('images') as $file) {
                    $path = $file->store('service_requests', 'public');
                    $imageData[] = [
                        'service_request_id' => $requestId,
                        'image_url' => url('storage/' . $path),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                DB::table('service_request_images')->insert($imageData);
            }
            DB::commit();

            $newRequest = DB::table('service_requests')->where('id', $requestId)->first();

            // 🌟 แจ้งเตือนแผนก Security ที่ดูแลงานแจ้งซ่อมโดยอัตโนมัติ (เมล QA ข้อ 5)
            StaffNotificationService::notifyRole(
                'security_admin',
                'มีรายการแจ้งซ่อมใหม่',
                "เลขที่ {$newRequest->ticket_number} — " . \Illuminate\Support\Str::limit($request->problem_description, 100),
                '/admin/service-requests/' . $requestId,
                'service_request'
            );

            return $this->successResponse($newRequest, 'บันทึกรายการแจ้งซ่อมสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage(), 500);
        }
    }

    public function getMyRequests(Request $request)
    {
        $requests = DB::table('service_requests')
            ->join('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->where('service_requests.customer_id', $request->user()->id) // แก้ไขให้ตรงกับเฟส 1
            ->select('service_requests.id', 'service_requests.ticket_number', 'customer_products.product_name', 'service_requests.preferred_date', 'service_requests.time_slot', 'service_requests.status', 'service_requests.created_at')
            ->orderBy('service_requests.created_at', 'desc')
            ->get();
        return $this->successResponse($requests, 'ดึงประวัติการแจ้งซ่อมสำเร็จ');
    }

    public function getRequestDetail(Request $request, $id)
    {
        $serviceRequest = DB::table('service_requests')
            ->join('customer_products', 'service_requests.customer_product_id', '=', 'customer_products.id')
            ->leftJoin('customer_addresses', 'service_requests.address_id', '=', 'customer_addresses.id')
            ->where('service_requests.id', $id)
            ->where('service_requests.customer_id', $request->user()->id) // แก้ไขให้ตรงกับเฟส 1
            ->select('service_requests.*', 'customer_products.product_name', 'customer_products.serial_number', 'customer_addresses.title as address_title', 'customer_addresses.address_line as saved_address_line', 'customer_addresses.province as saved_province')
            ->first();

        if (!$serviceRequest) return $this->errorResponse('ไม่พบข้อมูลการแจ้งซ่อม', 404);

        $serviceRequest->images = DB::table('service_request_images')->where('service_request_id', $id)->pluck('image_url');
        $serviceRequest->display_address = $serviceRequest->address_id ? "{$serviceRequest->saved_address_line} จ.{$serviceRequest->saved_province}" : $serviceRequest->custom_address_text;

        return $this->successResponse($serviceRequest, 'ดึงรายละเอียดการแจ้งซ่อมสำเร็จ');
    }
}