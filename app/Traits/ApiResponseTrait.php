<?php
// app/Traits/ApiResponseTrait.php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * ส่ง HTTP Response แบบสำเร็จ
     */
    protected function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * ส่ง HTTP Response แบบมีข้อผิดพลาด
     */
    protected function errorResponse($message = 'Error occurred', $code = 400, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}