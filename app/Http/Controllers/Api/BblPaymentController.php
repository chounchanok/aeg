<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;
use App\Traits\ApiResponseTrait;

class BblPaymentController extends Controller
{
    use ApiResponseTrait;

    private $baseUrl;

    public function __construct()
    {
        // สลับ URL ตาม Environment
        $this->baseUrl = env('BBL_ENV') === 'production' 
            ? 'https://api.bangkokbank.com' 
            : 'https://api-sandbox.bangkokbank.com';
    }

    // ==========================================
    // STEP 1: Get Access Token via OAuth
    // ==========================================
    private function getAccessToken()
    {
        $consumerKey = env('BBL_CONSUMER_KEY');
        $consumerSecret = env('BBL_CONSUMER_SECRET');
        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->asForm()->post($this->baseUrl . '/oauth/accesstoken', [
            'grant_type' => 'client_credentials',
            'scope' => 'READ CREATE'
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error('BBL Token Error: ', $response->json());
        throw new \Exception('ไม่สามารถดึง Access Token จาก BBL ได้');
    }

    // ==========================================
    // STEP 2: Payment Initiation (ถูกเรียกใช้จาก EcommerceController)
    // ==========================================
    public function initiatePayment($order)
    {
        $token = $this->getAccessToken();
        
        $billerId = env('BBL_BILLER_ID');
        $serviceCode = env('BBL_SERVICE_CODE');
        
        // 1. เตรียม Payload สำหรับขอลิงก์ชำระเงิน
        $payload = [
            'serviceCode' => $serviceCode,
            'billerId' => $billerId,
            'reference1' => $order->order_number, // ใช้เลข Order เป็น Ref 1
            'totalAmount' => number_format($order->total_amount, 2, '.', ''), // ต้องเป็นทศนิยม 2 ตำแหน่ง
            'currencyCode' => 'THB'
        ];

        // 2. สร้าง Signature (JWT RSA256)
        $privateKeyPath = storage_path('app/' . env('BBL_PRIVATE_KEY_PATH', 'keys/private.pem'));
        if (!file_exists($privateKeyPath)) {
            throw new \Exception('ไม่พบไฟล์ Private Key สำหรับทำ Signature');
        }
        $privateKey = file_get_contents($privateKeyPath);
        $signature = JWT::encode($payload, $privateKey, 'RS256');

        // 3. ยิง API ขอลิงก์ชำระเงิน
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Signature' => $signature,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/v1/billpayment/apptoapp/payment', $payload);

        if ($response->successful()) {
            // สมมติว่า BBL คืนค่าลิงก์แอปพลิเคชันมาในฟิลด์ paymentUrl (อาจต้องปรับชื่อฟิลด์ตามโครงสร้างจริงของ BBL)
            return $response->json('paymentUrl'); 
        }

        Log::error('BBL Initiate Error: ', $response->json());
        throw new \Exception('เกิดข้อผิดพลาดในการสร้างรายการชำระเงิน BBL');
    }

    // ==========================================
    // STEP 3: Payment Notification (Webhook)
    // ==========================================
    public function webhook(Request $request)
    {
        $data = $request->all();
        Log::info('BBL Webhook Received: ', $data);

        // TODO: ในระบบจริงต้องนำ Header 'Signature' ที่ BBL ส่งมา ถอดรหัสด้วย Public Key ของ BBL เพื่อยืนยันความถูกต้องด้วย

        $orderNumber = $data['reference1'] ?? null;
        $status = $data['paymentStatus'] ?? null;

        if ($orderNumber && $status === 'success') {
            DB::table('orders')->where('order_number', $orderNumber)->update([
                'status' => 'paid',
                'gateway_transaction_id' => $data['paymentReferenceId'] ?? null,
                'gateway_response' => json_encode($data),
                'updated_at' => now()
            ]);
        }

        // BBL บังคับให้ตอบกลับ ResponseCode 000 เมื่อรับข้อมูลสำเร็จ
        return response()->json([
            'responseCode' => '000',
            'responseMsg' => 'success'
        ]);
    }

    // ==========================================
    // STEP 4: Get Payment Result (สำหรับการเช็คสถานะซ้ำ กรณี Webhook ไม่ทำงาน)
    // ==========================================
    public function checkPaymentStatus($orderNumber)
    {
        $token = $this->getAccessToken();
        
        $payload = [
            'serviceCode' => env('BBL_SERVICE_CODE'),
            'billerId' => env('BBL_BILLER_ID'),
            'reference1' => $orderNumber
        ];

        // สร้าง Signature
        $privateKeyPath = storage_path('app/' . env('BBL_PRIVATE_KEY_PATH', 'keys/private.pem'));
        $privateKey = file_get_contents($privateKeyPath);
        $signature = JWT::encode($payload, $privateKey, 'RS256');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Signature' => $signature,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/v1/billpayment/apptoapp/inquiry', $payload);

        return $response->json();
    }
}