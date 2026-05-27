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
}
