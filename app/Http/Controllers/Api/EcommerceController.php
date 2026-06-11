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

        // รองรับการกรองตามประเภท (service, package, equipment)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // รองรับการกรองตามหมวดหมู่
        if ($request->has('category_id')) {
            $query->where('service_category_id', $request->category_id);
        }

        // ดึงข้อมูลสินค้าออกมาก่อน
        $rawProducts = $query->orderBy('created_at', 'desc')->get();

        // 🌟 ดึงรูปภาพทั้งหมดของสินค้าที่อยู่ใน list (เทคนิคแก้ปัญหาเว็บช้า N+1 Query)
        $productIds = $rawProducts->pluck('id');
        $allImages = DB::table('product_images')
            ->whereIn('product_id', $productIds)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->groupBy('product_id');

        $products = $rawProducts->map(function ($p) use ($lang, $allImages) {
            // จัดการรูปภาพให้เป็น Array
            $images = isset($allImages[$p->id]) ? $allImages[$p->id]->pluck('image_url')->toArray() : [];

            // Fallback: ถ้าตารางใหม่ไม่มีรูป ให้ดึงจากคอลัมน์เก่ามาโชว์แก้ขัด
            if (empty($images) && !empty($p->image_url)) {
                $images = [$p->image_url];
            }

            return [
                'id' => $p->id,
                'name' => ($lang == 'en' && !empty($p->name_en)) ? $p->name_en : $p->name_th,
                'description' => ($lang == 'en' && !empty($p->description_en)) ? $p->description_en : $p->description_th,
                'type' => $p->type,
                'price' => $p->price,
                'compare_at_price' => $p->compare_at_price,
                'image_url' => count($images) > 0 ? $images[0] : null, // รูปหน้าปก
                'images' => $images, // 🌟 เพิ่มรูปสไลด์ทั้งหมด (Array)
                'point_earn' => $p->point_earn,
                'is_contact_only' => (bool) $p->is_contact_only,
            ];
        });

        return $this->successResponse($products, 'Products retrieved successfully');
    }

    // ดึงรายละเอียดคำสั่งซื้อ
    public function getOrderDetail(Request $request, $id)
    {
        $userId = $request->user()->id;
        
        $order = \App\Models\Order::with('items')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        // จัด Format ให้ตรงกับหน้า UI
        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status, // เช่น 'completed', 'pending'
            'payment_method' => $order->payment_gateway ?? 'QR พร้อมเพย์',
            'transaction_date' => \Carbon\Carbon::parse($order->updated_at)->format('d M Y - H:i น.'),
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'total_amount' => $order->total_amount,
            'items' => $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    // ถ้าระบบเก็บ duration_months ไว้ ให้ส่งกลับไปด้วย
                    'duration_months' => $item->duration_months ?? null
                ];
            })
        ];

        return $this->successResponse($data, 'Order detail retrieved successfully');
    }

    public function getCartCount(Request $request)
    {
        $userId = $request->user()->id;
        $cart = DB::table('carts')->where('user_id', $userId)->first();

        if (!$cart) {
            return $this->successResponse(['total_items' => 0, 'total_quantity' => 0, 'items' => []], 'Cart is empty');
        }

        $items = DB::table('cart_items')->where('cart_id', $cart->id)->get();
        
        return $this->successResponse([
            'total_items' => $items->count(), // จำนวนรายการ (เช่น มีสินค้า 2 แบบ)
            'total_quantity' => $items->sum('quantity'), // จำนวนชิ้นรวม (เช่น ซื้อแบบละ 5 ชิ้น = 10)
            'items' => $items
        ], 'Cart count retrieved');
    }

    public function submitReview(Request $request, $itemId)
    {
        $request->validate([
            'install_rating' => 'required|integer|min:1|max:5',
            'sales_rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string'
        ]);

        $userId = $request->user()->id;

        // ตรวจสอบว่ามี item นี้จริงและเป็นของ user คนนี้
        $orderItem = \App\Models\OrderItem::whereHas('order', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('id', $itemId)->first();

        if (!$orderItem) return $this->errorResponse('Item not found', 404);

        // ตรวจสอบว่ารีวิวไปแล้วหรือยัง
        $exists = DB::table('package_reviews')->where('order_item_id', $itemId)->exists();
        if ($exists) return $this->errorResponse('You already reviewed this item', 400);

        DB::table('package_reviews')->insert([
            'order_item_id' => $itemId,
            'user_id' => $userId,
            'install_rating' => $request->install_rating,
            'sales_rating' => $request->sales_rating,
            'review_text' => $request->review_text,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // แจก 1 Point
        DB::table('customer_wallets')->where('user_id', $userId)->increment('current_points', 1);

        return $this->successResponse(null, 'Review submitted and 1 point rewarded');
    }

    public function getProductDetail($id)
    {
        $lang = request()->header('Accept-Language', 'th');

        $product = DB::table('products')->where('id', $id)->where('is_active', true)->first();
        if (!$product) return $this->errorResponse('Product not found', 404);

        // 🌟 ดึงรูปภาพทั้งหมดของสินค้านี้
        $images = DB::table('product_images')
            ->where('product_id', $id)
            ->orderBy('sort_order', 'asc')
            ->pluck('image_url')
            ->toArray();

        // Fallback รูปภาพ
        if (empty($images) && !empty($product->image_url)) {
            $images = [$product->image_url];
        }

        // จัด Format ข้อมูลที่จะส่งกลับ
        $data = [
            'id' => $product->id,
            'name' => ($lang == 'en' && !empty($product->name_en)) ? $product->name_en : $product->name_th,
            'description' => ($lang == 'en' && !empty($product->description_en)) ? $product->description_en : $product->description_th,
            'type' => $product->type,
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
            'image_url' => count($images) > 0 ? $images[0] : null,
            'images' => $images, // 🌟 ส่ง Array กลับไป
            'point_earn' => $product->point_earn,
            'is_contact_only' => (bool) $product->is_contact_only,
        ];

        return $this->successResponse($data, 'Product detail retrieved');
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
            ->select('cart_items.id as cart_item_id', 'products.id as product_id', 'products.name_th', 'products.price', 'cart_items.quantity', 'products.image_url')
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
        // 1. รับค่าที่แอปส่งมา (รวมถึง duration_months สำหรับแพ็กเกจ)
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'duration_months' => 'nullable|integer|in:1,3,6,12' // 🌟 รับระยะเวลาแพ็กเกจ (ถ้ามี)
        ]);

        $user = $request->user();
        $product = DB::table('products')->where('id', $request->product_id)->first();

        if (!$product) return $this->errorResponse('Product not found', 404);

        // 2. 🌟 คำนวณราคา: ถ้าระบุเดือนมา เอาไปคูณราคาตั้งต้น (สำหรับ type = 5)
        $duration = $request->duration_months ?? 1; // ถ้าไม่ได้ส่งมาให้ถือว่าเป็น 1
        $unitPrice = $product->price * $duration;

        // 3. หาตะกร้าของ User (ถ้ายังไม่มีให้สร้าง)
        $cart = DB::table('carts')->where('user_id', $user->id)->first();
        if (!$cart) {
            $cartId = DB::table('carts')->insertGetId(['user_id' => $user->id, 'created_at' => now()]);
        } else {
            $cartId = $cart->id;
        }

        // 4. เช็คว่ามีสินค้านี้ในตะกร้า "ด้วยระยะเวลาเดียวกัน" หรือไม่
        $cartItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $product->id)
            ->where('duration_months', $request->duration_months) // แยกรายการตามเดือน
            ->first();

        if ($cartItem) {
            // มีอยู่แล้ว อัปเดตแค่จำนวน
            DB::table('cart_items')->where('id', $cartItem->id)->update([
                'quantity' => $cartItem->quantity + $request->quantity,
                'updated_at' => now()
            ]);
        } else {
            // ยังไม่มี เพิ่มเข้าไปใหม่
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $unitPrice, // 🌟 เก็บราคาต่อหน่วยที่คูณจำนวนเดือนแล้ว
                'duration_months' => $request->duration_months, // 🌟 เก็บรายละเอียดเดือน
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $this->successResponse(null, 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว');
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
        $addresses = DB::table('customer_addresses')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->successResponse($addresses, 'Addresses retrieved');
    }

    // 🌟 1. ดึงรายละเอียดที่อยู่แบบเจาะจง (เพื่อเอาไปโชว์ในหน้าแก้ไข)
    public function getAddressDetail(Request $request, $id)
    {
        $address = DB::table('customer_addresses')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) return $this->errorResponse('ไม่พบข้อมูลที่อยู่นี้', 404);

        return $this->successResponse($address, 'Address detail retrieved');
    }

    // 🌟 2. อัปเดตฟังก์ชันสร้างที่อยู่ให้รับ พิกัด (Lat/Long) ได้
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
            'zipcode' => 'required|string',
            'latitude' => 'nullable|numeric',  // เพิ่มพิกัด
            'longitude' => 'nullable|numeric'  // เพิ่มพิกัด
        ]);

        $data = $request->only([
            'title', 'contact_name', 'contact_phone', 'address_line',
            'province', 'district', 'subdistrict', 'zipcode', 'latitude', 'longitude'
        ]);

        $data['user_id'] = $request->user()->id;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $addressId = DB::table('customer_addresses')->insertGetId($data);

        $newAddress = DB::table('customer_addresses')->where('id', $addressId)->first();

        return $this->successResponse($newAddress, 'บันทึกที่อยู่ใหม่สำเร็จ');
    }

    // 🌟 3. ฟังก์ชันอัปเดตที่อยู่เดิม
    public function updateAddress(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'contact_name' => 'required|string',
            'contact_phone' => 'required|string',
            'address_line' => 'required|string',
            'province' => 'required|string',
            'district' => 'required|string',
            'subdistrict' => 'required|string',
            'zipcode' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        $address = DB::table('customer_addresses')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) return $this->errorResponse('ไม่พบข้อมูลที่อยู่นี้', 404);

        $updateData = $request->only([
            'title', 'contact_name', 'contact_phone', 'address_line',
            'province', 'district', 'subdistrict', 'zipcode', 'latitude', 'longitude'
        ]);
        $updateData['updated_at'] = now();

        DB::table('customer_addresses')->where('id', $id)->update($updateData);

        $updatedAddress = DB::table('customer_addresses')->where('id', $id)->first();

        return $this->successResponse($updatedAddress, 'อัปเดตข้อมูลที่อยู่สำเร็จ');
    }

    // ==========================================
    // 4. Checkout & Payment (สั่งซื้อจากตะกร้า)
    // ==========================================
    public function checkout(Request $request)
    {
        $request->validate([
            'address_id' => 'required|integer',
            'payment_gateway' => 'required|string',
            'preferred_date' => 'nullable|date',
            'note' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:10240' // รับรูปหรือวิดีโอ (สูงสุด 10MB)
        ]);

        $user = $request->user();
        $cart = DB::table('carts')->where('user_id', $user->id)->first();
        if (!$cart) return $this->errorResponse('Cart is empty', 400);

        $cartItems = DB::table('cart_items')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->where('cart_items.cart_id', $cart->id)
            ->select('products.id', 'products.name_th as name', 'products.price', 'cart_items.quantity')
            ->get();

        if ($cartItems->isEmpty()) return $this->errorResponse('Cart is empty', 400);

        $totalAmount = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // จัดการอัปโหลดไฟล์ (ถ้ามีส่งมา)
        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('order_attachments', 'public');
            $attachmentUrl = url('storage/' . $path);
        }

        DB::beginTransaction();
        try {
            // 1. สร้าง Order พร้อมข้อมูลใหม่
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => 'ORD-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'subtotal' => $totalAmount,
                'discount' => 0, // TODO: ถ้าระบบคูปองเสร็จ สามารถคำนวณส่วนลดตรงนี้ได้
                'total_amount' => $totalAmount,
                'status' => 'pending_payment',
                'payment_gateway' => $request->payment_gateway,
                'preferred_date' => $request->preferred_date,
                'note' => $request->note,
                'coupon_code' => $request->coupon_code,
                'attachment_url' => $attachmentUrl,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. ย้ายของจาก Cart ไป Order Items
            $orderItemsData = [];
            foreach ($cartItems as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name, // ดึงจาก Join ที่คุณมี
                    'quantity' => $item->quantity,
                    'price' => $item->price, // ราคานี้คูณเดือนมาจากตะกร้าแล้ว
                    'duration_months' => $item->duration_months, // 🌟 ย้ายข้อมูลเดือนมาเก็บในบิล
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 3. ล้างตะกร้า
            DB::table('cart_items')->where('cart_id', $cart->id)->delete();

            DB::commit();

            $order = DB::table('orders')->where('id', $orderId)->first();
            $paymentUrl = "https://placeholder-gateway.com/pay/" . $order->order_number;

            return $this->successResponse([
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount,
                'payment_url' => $paymentUrl
            ], 'Order created successfully. Please proceed to payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Checkout failed: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // 5. Buy Now (ซื้อเลยข้ามตะกร้า)
    // ==========================================
    public function buyNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'address_id' => 'required|integer',
            'payment_gateway' => 'required|string',
            'preferred_date' => 'nullable|date',
            'note' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:10240'
        ]);

        $user = $request->user();
        $product = DB::table('products')->where('id', $request->product_id)->where('is_active', true)->first();

        if (!$product) {
            return $this->errorResponse('Product not found or inactive', 404);
        }

        $subtotal = $product->price * $request->quantity;

        // จัดการอัปโหลดไฟล์ (ถ้ามีส่งมา)
        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('order_attachments', 'public');
            $attachmentUrl = url('storage/' . $path);
        }

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
                'preferred_date' => $request->preferred_date,
                'note' => $request->note,
                'coupon_code' => $request->coupon_code,
                'attachment_url' => $attachmentUrl,
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

    // ==========================================
    // 6. ยืนยันการชำระเงินสำเร็จ (Payment Success Callback)
    // ==========================================
    public function paymentSuccess(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            // สามารถรับ transaction_id หรือค่าอื่นๆ จาก Payment Gateway เพิ่มได้
        ]);

        $order = DB::table('orders')->where('order_number', $request->order_number)->first();

        if (!$order) {
            return $this->errorResponse('ไม่พบข้อมูลคำสั่งซื้อนี้', 404);
        }

        // 1. ดึงรายการสินค้าทั้งหมดในบิลนี้ (ย้ายมาไว้ด้านบนเพื่อดึงไปแสดงผล Response ได้ทันที)
        $orderItems = DB::table('order_items')->where('order_id', $order->id)->get();

        // 2. เตรียมข้อมูล Items ให้เป็น Array สำหรับแสดงบนหน้าจอ
        $itemsList = $orderItems->map(function($item) {
            return [
                'name' => $item->product_name,
                'quantity' => $item->quantity
            ];
        });

        // 3. เตรียมชุดข้อมูล Response ให้ตรงกับภาพ UI ที่ต้องการ
        $responseData = [
            'order_number' => $order->order_number,
            'status_text' => 'สำเร็จ',
            'status_message' => 'คุณชำระเงินสำเร็จแล้ว',
            // แปลงรูปแบบเวลาให้เป็นแบบ 25 Jul 2025 - 10:23 น
            'payment_date' => \Carbon\Carbon::parse($order->updated_at ?? now())->locale('en')->isoFormat('DD MMM YYYY - HH:mm น'),
            'items' => $itemsList,
            // 💡 หมายเหตุ: สมมติว่าตาราง orders มีคอลัมน์ total_amount และ payment_method
            'total_amount' => number_format($order->total_amount ?? 0, 0),
            'payment_method' => $order->payment_method ?? 'QR พร้อมเพย์'
        ];

        // 4. เช็คว่าถ้าออเดอร์นี้เคยเปลี่ยนสถานะและแจกของไปแล้ว ให้ return ข้อมูลบิลกลับไปเลย ป้องกันแจกเบิ้ล
        if ($order->status === 'completed') {
            return $this->successResponse($responseData, 'คำสั่งซื้อนี้ได้รับการยืนยันและแจกแพ็กเกจเรียบร้อยแล้ว');
        }

        DB::beginTransaction();
        try {
            // อัปเดตสถานะ Order เป็น "ชำระเงินแล้ว (completed)"
            DB::table('orders')->where('id', $order->id)->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);

            // อัปเดตเวลาชำระเงินใน Payload ให้เป็นเวลาปัจจุบันที่เพิ่งบันทึกสำเร็จ
            $responseData['payment_date'] = \Carbon\Carbon::now()->locale('en')->isoFormat('DD MMM YYYY - HH:mm น');

            // 5. 🌟 นำ Logic การแจกของมาไว้ตรงนี้! (นำเข้าตาราง customer_products)
            foreach ($orderItems as $item) {
                // หารูปภาพหน้าปก
                $coverImage = DB::table('product_images')
                                ->where('product_id', $item->product_id)
                                ->orderBy('sort_order', 'asc')
                                ->value('image_url');

                // แจกแพ็กเกจตามจำนวนชิ้นที่ลูกค้าซื้อ
                for ($i = 0; $i < $item->quantity; $i++) {
                    DB::table('customer_products')->insert([
                        'customer_id' => $order->user_id,
                        'product_name' => $item->product_name,
                        'serial_number' => 'PKG-' . strtoupper(\Illuminate\Support\Str::random(8)),
                        'warranty_expire_date' => \Carbon\Carbon::now()->addYear(), // หมดอายุใน 1 ปี
                        'status' => 'active',
                        'total_service_count' => 4, // โควต้าเรียกช่าง
                        'used_service_count' => 0,
                        'image_url' => $coverImage,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            // 🌟 ส่ง $responseData กลับไปให้แอปพลิเคชันเพื่อนำไปวาดหน้า UI ตามภาพ
            return $this->successResponse($responseData, 'ยืนยันการชำระเงินสำเร็จ และเพิ่มแพ็กเกจให้ลูกค้าเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('เกิดข้อผิดพลาดในการยืนยันชำระเงิน: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // ดึงข้อมูลแพ็กเกจดูแลอุปกรณ์ (Products Type = 5)
    // ==========================================
    public function getPackages()
    {
        // 1. ดึงรายการประเภทอุปกรณ์ (Products Type = 5)
        $packages = DB::table('products')
            ->where('type', 5)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name_th,
                    'description' => $product->description_th,
                    // ตัดแยกบรรทัดรายละเอียดด้วย \n เพื่อให้ App นำไปทำ List Bullet ได้ง่าย
                    'details_list' => array_filter(array_map('trim', explode("\n", $product->description_th))),
                    'base_price' => (float)$product->price, // ราคาต่อ 1 เดือน ต่อ 1 อุปกรณ์
                    'image_url' => $product->image_url,
                ];
            });

        // 2. กำหนดประเภทการดูแล (ระยะเวลาที่ Fix ไว้)
        $durations = [
            [
                'id' => 1,
                'label' => '1 ครั้ง (1 เดือน)',
                'multiplier' => 1 // ตัวคูณเดือน
            ],
            [
                'id' => 3,
                'label' => '3 เดือน',
                'multiplier' => 3
            ],
            [
                'id' => 6,
                'label' => '6 เดือน',
                'multiplier' => 6
            ],
            [
                'id' => 12,
                'label' => 'รายปี (12 เดือน)',
                'multiplier' => 12
            ]
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Packages retrieved successfully',
            'data' => [
                'devices' => $packages,
                'care_durations' => $durations
            ]
        ]);
    }
}
