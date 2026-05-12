<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;

class EcommerceController extends Controller
{
    use ApiResponseTrait;

    // ==========================================
    // 1. สินค้า (Products)
    // ==========================================
    public function getProducts(Request $request)
    {
        $lang = $request->header('Accept-Language', 'th');
        
        $query = DB::table('products')->where('is_active', true);

        // รองรับการกรองตามหมวดหมู่
        if ($request->has('category_id')) {
            $query->where('type', $request->category_id);
        }

        $products = $query->orderBy('created_at', 'desc')->get()->map(function ($p) use ($lang) {
            return [
                'id' => $p->id,
                'name' => ($lang == 'en' && !empty($p->name_en)) ? $p->name_en : $p->name_th,
                'description' => ($lang == 'en' && !empty($p->description_en)) ? $p->description_en : $p->description_th,
                'type' => $p->type,
                'price' => $p->price,
                'compare_at_price' => $p->compare_at_price,
                'image_url' => $p->image_url,
                'point_earn' => $p->point_earn,
            ];
        });

        return $this->successResponse($products, 'Products retrieved successfully');
    }

    public function getProductDetail($id)
    {
        $product = DB::table('products')->where('id', $id)->where('is_active', true)->first();
        if (!$product) return $this->errorResponse('Product not found', 404);
        return $this->successResponse($product, 'Product detail retrieved');
    }

    // ==========================================
    // 2. ตะกร้าสินค้า (Cart)
    // ==========================================
    public function getCart(Request $request)
    {
        $user = $request->user();
        $cart = DB::table('carts')->where('user_id', $user->id)->first();
        
        if (!$cart) {
            return $this->successResponse(['items' => [], 'summary' => ['total' => 0]], 'Cart is empty');
        }

        $items = DB::table('cart_items')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->where('cart_items.cart_id', $cart->id)
            ->select('cart_items.id as cart_item_id', 'products.id as product_id', 'products.name', 'products.price', 'cart_items.quantity', 'products.image_url')
            ->get();

        $total = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return $this->successResponse([
            'items' => $items,
            'summary' => ['total_amount' => $total]
        ], 'Cart retrieved');
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $request->user();
        $product = DB::table('products')->where('id', $request->product_id)->first();
        if (!$product) return $this->errorResponse('Product not found', 404);

        // 1. หาตะกร้าของ User นี้
        $cart = DB::table('carts')->where('user_id', $user->id)->first();

        // 2. ถ้ายังไม่มีตะกร้า ให้สร้างใหม่
        if (!$cart) {
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            // ดึงข้อมูลตะกร้าที่เพิ่งสร้างขึ้นมา
            $cart = DB::table('carts')->where('id', $cartId)->first();
        }

        // เช็คว่ามีสินค้านี้ในตะกร้าหรือยัง ถ้ามีให้บวกเพิ่ม
        $existingItem = DB::table('cart_items')
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingItem) {
            DB::table('cart_items')->where('id', $existingItem->id)
                ->increment('quantity', $request->quantity);
        } else {
            DB::table('cart_items')->insert([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $this->successResponse(null, 'Added to cart successfully');
    }

    public function removeFromCart($cartItemId)
    {
        DB::table('cart_items')->where('id', $cartItemId)->delete();
        return $this->successResponse(null, 'Item removed from cart');
    }

    // ==========================================
    // 3. ที่อยู่ (Addresses)
    // ==========================================
    public function getAddresses(Request $request)
    {
        $addresses = DB::table('customer_addresses')->where('user_id', $request->user()->id)->get();
        return $this->successResponse($addresses, 'Addresses retrieved');
    }

    public function createAddress(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'contact_name' => 'required|string',
            'contact_phone' => 'required|string',
            'address_line' => 'required|string',
            'province' => 'required|string',
            'district' => 'required|string',
            'subdistrict' => 'required|string',
            'zipcode' => 'required|string'
        ]);

        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('customer_addresses')->insert($data);
        return $this->successResponse(null, 'Address created successfully');
    }

    // ==========================================
    // 4. Checkout & Payment (สั่งซื้อ)
    // ==========================================
    public function checkout(Request $request)
    {
        $request->validate([
            'address_id' => 'required|integer',
            'payment_gateway' => 'required|string' // เช่น 'omise', 'promptpay'
        ]);

        $user = $request->user();
        $cart = DB::table('carts')->where('user_id', $user->id)->first();
        if (!$cart) return $this->errorResponse('Cart is empty', 400);

        $cartItems = DB::table('cart_items')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->where('cart_items.cart_id', $cart->id)
            ->select('products.id', 'products.name', 'products.price', 'cart_items.quantity')
            ->get();

        if ($cartItems->isEmpty()) return $this->errorResponse('Cart is empty', 400);

        $totalAmount = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        DB::beginTransaction();
        try {
            // 1. สร้าง Order
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => 'ORD-' . date('Ym') . '-' . strtoupper(Str::random(6)),
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'subtotal' => $totalAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending_payment',
                'payment_gateway' => $request->payment_gateway,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. ย้ายของจาก Cart ไป Order Items
            $orderItemsData = [];
            foreach ($cartItems as $item) {
                $orderItemsData[] = [
                    'order_id' => $orderId,
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            DB::table('order_items')->insert($orderItemsData);

            // 3. ล้างตะกร้า
            DB::table('cart_items')->where('cart_id', $cart->id)->delete();

            DB::commit();

            // TODO: เชื่อมต่อ API ของ Payment Gateway ตรงนี้ 
            // ตัวอย่าง: โยน $totalAmount และ $orderId ไปสร้าง Payment Link ของ Omise/GBPrime
            $paymentUrl = "https://placeholder-gateway.com/pay/" . $orderId;

            return $this->successResponse([
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'payment_url' => $paymentUrl // ส่ง Link ให้แอป Mobile เปิดหน้าจ่ายเงิน
            ], 'Order created successfully. Please proceed to payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Checkout failed: ' . $e->getMessage(), 500);
        }
    }

    public function getMyOrders(Request $request)
    {
        $orders = DB::table('orders')->where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->get();
        return $this->successResponse($orders, 'Orders retrieved');
    }

    // เส้น Webhook สำหรับรับ Callback จาก Gateway เมื่อลูกค้าจ่ายเงินเสร็จ
    public function paymentWebhook(Request $request)
    {
        // TODO: ตรวจสอบ Signature จาก Gateway ว่าเป็นของจริงหรือไม่
        
        $orderNumber = $request->input('ref_no'); // สมมติ Field ที่ Gateway ส่งกลับมา
        $status = $request->input('status'); // 'success' หรือ 'failed'

        if ($status === 'success') {
            DB::table('orders')
                ->where('order_number', $orderNumber)
                ->update([
                    'status' => 'paid',
                    'gateway_transaction_id' => $request->input('transaction_id'),
                    'gateway_response' => json_encode($request->all())
                ]);
            // TODO: แจกแต้ม (Point Earn) เข้า Wallet ของลูกค้าหลังจากจ่ายเงินสำเร็จ
        }

        return response()->json(['status' => 'ok']); // ตอบกลับ Gateway ว่ารับข้อมูลแล้ว
    }

    // 🌟 เพิ่มฟังก์ชันใหม่สำหรับการ "จอง/ซื้อเลย" (ข้ามตะกร้า)
    public function buyNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'address_id' => 'required|integer',
            'payment_gateway' => 'required|string'
        ]);

        $user = $request->user();
        $product = DB::table('products')->where('id', $request->product_id)->where('is_active', true)->first();
        
        if (!$product) {
            return $this->errorResponse('Product not found or inactive', 404);
        }

        $subtotal = $product->price * $request->quantity;

        DB::beginTransaction();
        try {
            // สร้างออเดอร์ทันที
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => 'ORD-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $subtotal,
                'status' => 'pending_payment',
                'payment_gateway' => $request->payment_gateway,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // บันทึกรายการสินค้าลงใน Order Items
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $product->id,
                'product_name' => $product->name_th, 
                'price' => $product->price,
                'quantity' => $request->quantity,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            // ดึง order_number ออกมาส่งกลับให้แอป
            $order = DB::table('orders')->where('id', $orderId)->first();
            $paymentUrl = "https://placeholder-gateway.com/pay/" . $order->order_number;

            return $this->successResponse([
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'total_amount' => $subtotal,
                'payment_url' => $paymentUrl
            ], 'Order created successfully. Please proceed to payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Checkout failed: ' . $e->getMessage(), 500);
        }
    }
}