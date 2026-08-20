<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\Request;

class InsuranceApiController extends Controller
{
    // โครงสร้าง Response มาตรฐาน (ถ้าพี่มี Trait ใช้เรียกต่างหาก สามารถเปลี่ยนได้นะครับ)
    protected function successResponse($data, $message = 'Success')
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    protected function errorResponse($message, $statusCode = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null
        ], $statusCode);
    }

    // 1. ดึงรายการประกันภัยทั้งหมดที่เปิดใช้งาน
    public function index()
    {
        $insurances = Insurance::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title_th' => $item->title_th,
                    'title_en' => $item->title_en,
                    'image_url' => $item->image_url,
                    // ตัด description ให้สั้นลงสำหรับแสดงหน้าลิสต์ (ถ้ามีแท็ก HTML จะถูก strip ออก)
                    'short_description' => \Illuminate\Support\Str::limit(strip_tags($item->description_th), 100),
                ];
            });

        return $this->successResponse($insurances, 'Insurance list retrieved successfully');
    }

    // 2. ดึงรายละเอียดประกันภัยรายตัว
    public function show($id)
    {
        $insurance = Insurance::where('id', $id)->where('is_active', true)->first();

        if (!$insurance) {
            return $this->errorResponse('ไม่พบข้อมูลประกันภัยที่ระบุ', 404);
        }

        $data = [
            'id' => $insurance->id,
            'title_th' => $insurance->title_th,
            'title_en' => $insurance->title_en,
            'description_th' => $insurance->description_th,
            'description_en' => $insurance->description_en,
            'image_url' => $insurance->image_inside_url,
        ];

        return $this->successResponse($data, 'Insurance details retrieved successfully');
    }
}
