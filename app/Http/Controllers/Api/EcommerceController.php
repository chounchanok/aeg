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

        // ตัวอย่างความไหลลื่นใน Api/EcommerceController.php ในส่วนแมตช์ข้อมูล
        $products = $rawProducts->map(function ($p) use ($lang, $allImages) {
            // ดึงรูปภาพ Array จากตารางย่อย product_images
            $images = isset($allImages[$p->id]) ? $allImages[$p->id]->pluck('image_url')->toArray() : [];

            // เผื่อกรณีสินค้าเก่าไม่มีในตาราง product_images ให้หยิบจากตารางหลักมาห่อเป็น Array แก้อาการภาพว่าง
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
                'image_url' => count($images) > 0 ? $images[0] : null, // ส่งรูปแรกสุดไปเป็นรูปหน้าปก
                'images' => $images,                                   // ส่งก้อน Array ทั้งหมดไปทำ Slider
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
            'review_text' => 'nullable|string', // รีวิวงานติดตั้ง
            'sales_rating' => 'required|integer|min:1|max:5',
            'sales_review_text' => 'nullable|string', // รีวิวฝ่ายขาย
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:20480' // อัปโหลดรูป/วิดีโอ (สูงสุด 20MB)
        ]);

        $userId = $request->user()->id;

        // เช็คว่าเคยรีวิวหรือยัง
        $exists = DB::table('package_reviews')->where('order_item_id', $itemId)->where('user_id', $userId)->exists();
        if ($exists) return $this->errorResponse('You already reviewed this item', 400);

        // จัดการไฟล์อัปโหลด
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('reviews/media', 'public');
                $mediaPaths[] = '/storage/' . $path;
            }
        }

        DB::table('package_reviews')->insert([
            'order_item_id' => $itemId,
            'user_id' => $userId,
            'install_rating' => $request->install_rating,
            'review_text' => $request->review_text,
            'sales_rating' => $request->sales_rating,
            'sales_review_text' => $request->sales_review_text,
            'media_paths' => json_encode($mediaPaths),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // แจก 1 Point
        DB::table('customer_wallets')->where('user_id', $userId)->increment('current_points', 1);

        return $this->successResponse(null, 'Review submitted successfully');
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

        // 🌟 โครงสร้าง Summary มาตรฐาน
        $defaultSummary = [
            'subtotal' => 0,
            'discount_amount' => 0,
            'reward_title' => null,
            'net_total' => 0
        ];

        if (!$cart) {
            return $this->successResponse(['items' => [], 'summary' => $defaultSummary], 'Cart is empty');
        }

        $items = DB::table('cart_items')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->where('cart_items.cart_id', $cart->id)
            ->select('cart_items.id as cart_item_id', 'products.id as product_id', 'products.name_th', 'products.price', 'cart_items.quantity', 'products.image_url', 'cart_items.duration_months')
            ->get();

        // 1. คำนวณยอดรวมปกติ (Subtotal)
        $subtotal = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // 🌟 2. ตรวจสอบการใช้ส่วนลดจาก Reward
        $discountAmount = 0;
        $rewardTitle = null;

        // ถ้าแอปมีการแนบ reward_id มาให้ด้วย (ผ่าน Query Parameter)
        if ($request->has('reward_id') && !empty($request->reward_id)) {
            $reward = DB::table('rewards')->where('id', $request->reward_id)->where('is_active', true)->first();
            
            if ($reward) {
                // เช็คว่าลูกค้าแต้มพอที่จะใช้ Reward นี้หรือไม่
                $wallet = DB::table('customer_wallets')->where('user_id', $user->id)->first();
                
                if ($wallet && $wallet->current_points >= $reward->points_required) {
                    $discountAmount = (float) $reward->discount_amount;
                    $rewardTitle = $reward->title_th ?? 'ส่วนลดจากของรางวัล';
                } else {
                    return $this->errorResponse('คะแนน EASE Coins ของคุณไม่เพียงพอสำหรับแลกส่วนลดนี้', 400);
                }
            } else {
                return $this->errorResponse('ไม่พบของรางวัล หรือของรางวัลนี้หมดอายุแล้ว', 404);
            }
        }

        // 3. คำนวณยอดสุทธิ (Net Total) ป้องกันยอดติดลบ
        $netTotal = max(0, $subtotal - $discountAmount);

        return $this->successResponse([
            'items' => $items,
            'summary' => [
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'reward_title' => $rewardTitle,
                'net_total' => $netTotal
            ]
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
        $duration = $request->duration_months ?? 0; // ถ้าไม่ได้ส่งมาให้ถือว่าเป็น 1

        if($duration > 0) {
            $unitPrice = $product->price * $duration;
        } else {
            $unitPrice = $product->price;
        }

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
                'duration_months' => $duration, // 🌟 เก็บรายละเอียดเดือน
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
    // 4. Checkout & Payment (สั่งซื้อจากตะกร้าแบบระบุชิ้น)
    // ==========================================
    public function checkout(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|array|min:1',
            'cart_id.*' => 'integer',
            'address_id' => 'required|integer',
            'payment_gateway' => 'required|string',
            'preferred_date' => 'nullable|date',
            'note' => 'nullable|string',
            'reward_code' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:10240'
        ]);

        $user = $request->user();
        $selectedCartItemIds = $request->cart_id;

        $cart = DB::table('carts')->where('user_id', $user->id)->first();
        if (!$cart) return $this->errorResponse('Cart not found', 404);

        $cartItems = DB::table('cart_items')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->where('cart_items.cart_id', $cart->id)
            ->whereIn('cart_items.id', $selectedCartItemIds)
            ->select(
                'cart_items.id as cart_item_id', 'products.id as product_id',
                'products.name_th as product_name', 'cart_items.price',
                'cart_items.quantity', 'cart_items.duration_months'
            )->get();

        if ($cartItems->isEmpty()) {
            return $this->errorResponse('Selected cart items are invalid or empty', 400);
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // ==========================================
        // ระบบคำนวณส่วนลดจาก โค้ดรางวัล (Reward Code)
        // ==========================================
        $discount = 0;
        $usedRewardCode = null;

        if ($request->reward_code) {
            // เช็คว่าโค้ดนี้เป็นของลูกค้าคนนี้จริง และยังไม่ถูกใช้งาน
            $usedRewardCode = DB::table('customer_reward_codes')
                ->where('code', $request->reward_code)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$usedRewardCode) {
                return $this->errorResponse('โค้ดส่วนลดนี้ไม่ถูกต้อง หรือถูกใช้งานไปแล้ว', 400);
            }

            // ได้ส่วนลดตามที่แลกมา (ไม่ต้องเช็คแต้มแล้วเพราะถูกหักแต้มไปตอนแลกแล้ว)
            $discount = (float)$usedRewardCode->discount_amount;
        }
        // ==========================================

        // คำนวณยอดสุทธิ (ป้องกันยอดติดลบ)
        $totalAmount = max(0, $subtotal - $discount);
        // ==========================================

        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('order_attachments', 'public');
            $attachmentUrl = url('storage/' . $path);
        }

        DB::beginTransaction();
        try {
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => 'ORD-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'subtotal' => $subtotal,
                'discount' => $discount, // 🌟 บันทึกส่วนลดลงบิล
                'total_amount' => $totalAmount, // 🌟 บันทึกยอดที่หักส่วนลดแล้ว
                'status' => 'pending_payment',
                'payment_gateway' => $request->payment_gateway,
                'preferred_date' => $request->preferred_date,
                'note' => $request->note,
                'coupon_code' => $request->coupon_code,
                'attachment_url' => $attachmentUrl,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($cartItems as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'duration_months' => $item->duration_months,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::table('cart_items')->whereIn('id', $cartItems->pluck('cart_item_id'))->delete();

            // 🌟 ใส่ของใหม่: เปลี่ยนสถานะโค้ดเป็นใช้งานแล้ว
            if ($usedRewardCode) {
                DB::table('customer_reward_codes')->where('id', $usedRewardCode->id)->update([
                    'status' => 'used',
                    'used_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            $order = DB::table('orders')->where('id', $orderId)->first();
            $paymentUrl = "https://placeholder-gateway.com/pay/" . $order->order_number;

            return $this->successResponse([
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'payment_url' => $paymentUrl
            ], 'สร้างคำสั่งซื้อและหักส่วนลดสำเร็จ กรุณาชำระเงิน');

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
            'reward_code' => 'nullable|string',
            'duration_months' => 'nullable|integer|in:1,3,6,12',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:10240'
        ]);

        $user = $request->user();
        $product = DB::table('products')->where('id', $request->product_id)->where('is_active', true)->first();

        if (!$product) {
            return $this->errorResponse('Product not found or inactive', 404);
        }

        $duration = $request->duration_months ?? 1;
        $subtotal = $product->price * $request->quantity * $duration;

        // ==========================================
        // ระบบคำนวณส่วนลดจาก โค้ดรางวัล (Reward Code)
        // ==========================================
        $discount = 0;
        $usedRewardCode = null;

        if ($request->reward_code) {
            // เช็คว่าโค้ดนี้เป็นของลูกค้าคนนี้จริง และยังไม่ถูกใช้งาน
            $usedRewardCode = DB::table('customer_reward_codes')
                ->where('code', $request->reward_code)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$usedRewardCode) {
                return $this->errorResponse('โค้ดส่วนลดนี้ไม่ถูกต้อง หรือถูกใช้งานไปแล้ว', 400);
            }

            // ได้ส่วนลดตามที่แลกมา (ไม่ต้องเช็คแต้มแล้วเพราะถูกหักแต้มไปตอนแลกแล้ว)
            $discount = (float)$usedRewardCode->discount_amount;
        }
        // ==========================================

        // คำนวณยอดสุทธิ (ป้องกันยอดติดลบ)
        $totalAmount = max(0, $subtotal - $discount);
        // ==========================================

        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('order_attachments', 'public');
            $attachmentUrl = url('storage/' . $path);
        }

        DB::beginTransaction();
        try {
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => 'ORD-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'subtotal' => $subtotal,
                'discount' => $discount, // 🌟
                'total_amount' => $totalAmount, // 🌟
                'status' => 'pending_payment',
                'payment_gateway' => $request->payment_gateway,
                'preferred_date' => $request->preferred_date,
                'note' => $request->note,
                'coupon_code' => $request->coupon_code,
                'attachment_url' => $attachmentUrl,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $product->id,
                'product_name' => $product->name_th,
                'price' => $product->price,
                'duration_months' => $request->duration_months,
                'quantity' => $request->quantity,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 🌟 ใส่ของใหม่: เปลี่ยนสถานะโค้ดเป็นใช้งานแล้ว
            if ($usedRewardCode) {
                DB::table('customer_reward_codes')->where('id', $usedRewardCode->id)->update([
                    'status' => 'used',
                    'used_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            $order = DB::table('orders')->where('id', $orderId)->first();
            $paymentUrl = "https://placeholder-gateway.com/pay/" . $order->order_number;

            return $this->successResponse([
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
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
                        'reference_type' => 'product',
                        'reference_id' => $item->product_id,
                        'purchase_date' => \Carbon\Carbon::now(),
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

    // ==========================================
    // ระบบ Reward (กดแลกแต้มเป็นโค้ด และ ดูคลังโค้ด)
    // ==========================================

    // 1. ฟังก์ชันกดแลกของรางวัล (หักแต้มแล้วได้โค้ด)
    public function redeemReward(Request $request)
    {
        $request->validate([
            'reward_id' => 'required|integer'
        ]);

        $user = $request->user();
        $reward = DB::table('rewards')->where('id', $request->reward_id)->where('is_active', true)->first();

        if (!$reward) return $this->errorResponse('ไม่พบของรางวัลนี้', 404);

        $wallet = DB::table('customer_wallets')->where('user_id', $user->id)->first();
        if (!$wallet || $wallet->current_points < $reward->points_required) {
            return $this->errorResponse('คะแนน EASE Coins ของคุณไม่เพียงพอ', 400);
        }

        DB::beginTransaction();
        try {
            // 1. หักแต้มลูกค้าออกจากกระเป๋าหลัก
            DB::table('customer_wallets')->where('user_id', $user->id)->decrement('current_points', $reward->points_required);

            // 🌟 2. เพิ่มการบันทึกประวัติลง point_transactions
            DB::table('point_transactions')->insert([
                'user_id' => $user->id,
                'amount' => ($reward->points_required*-1), // จำนวนแต้มที่ใช้ไป
                'type' => 'redeem', // สถานะการใช้แต้ม (เช่น earn=ได้แต้ม, redeem=ใช้แต้ม)
                'description' => 'แลกรับส่วนลด: ' . $reward->title_th, // คำอธิบายที่จะโชว์ในแอป
                // ถ้าในตารางพี่แชมเปญมีเก็บ reference ไว้โยงข้อมูล ก็เปิดคอมเมนต์ด้านล่างนี้ได้ครับ
                // 'reference_type' => 'reward', 
                // 'reference_id' => $reward->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. สุ่มโค้ด RWD- ตามด้วยอักษรภาษาอังกฤษและตัวเลข 8 หลัก
            $code = 'RWD-' . strtoupper(\Illuminate\Support\Str::random(8));

            // 3. บันทึกโค้ดเข้ากระเป๋าลูกค้า
            DB::table('customer_reward_codes')->insert([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'code' => $code,
                'discount_amount' => $reward->discount_amount,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return $this->successResponse([
                'code' => $code,
                'discount_amount' => $reward->discount_amount,
                'reward_title' => $reward->title_th,
                'reward_point' => $wallet->current_points - $reward->points_required,
            ], 'แลกของรางวัลสำเร็จ โค้ดส่วนลดถูกเก็บไว้ในกระเป๋าของคุณแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return dd($e->getMessage());
            // return $this->errorResponse('เกิดข้อผิดพลาดในการแลกของรางวัล', 500);
        }
    }

    // 2. ฟังก์ชันดูโค้ดที่ยังไม่ได้ใช้งาน
    public function getMyRewardCodes(Request $request)
    {
        $user = $request->user();
        
        $codes = DB::table('customer_reward_codes')
            ->join('rewards', 'customer_reward_codes.reward_id', '=', 'rewards.id')
            ->where('customer_reward_codes.user_id', $user->id)
            ->where('customer_reward_codes.status', 'active') // 🌟 ดึงเฉพาะที่ยังไม่ได้ใช้
            ->select(
                'customer_reward_codes.id',
                'customer_reward_codes.code',
                'customer_reward_codes.discount_amount',
                'customer_reward_codes.created_at as redeemed_date',
                'rewards.title_th as reward_title',
                'rewards.image_url'
            )
            ->orderBy('customer_reward_codes.created_at', 'desc')
            ->get();

        return $this->successResponse($codes, 'ดึงรายการโค้ดส่วนลดที่ใช้งานได้สำเร็จ');
    }

    // ==========================================
    // ระบบค้นหาแบบครอบจักรวาล (Global Search)
    // ==========================================
    public function globalSearch(Request $request)
    {
        // รับคำค้นหาจาก query string เช่น ?q=แอร์
        $keyword = $request->query('q');
        $lang = $request->header('Accept-Language', 'th');

        // ถ้าไม่ได้พิมพ์อะไรมา ให้ส่ง Array ว่างกลับไป
        if (!$keyword) {
            return $this->successResponse([
                'products_and_services' => [],
                'lockers' => [],
                'rewards' => [],
                'insurances' => []
            ], 'ไม่พบคำค้นหา');
        }

        // 1. ค้นหา สินค้า และ บริการ
        $products = DB::table('products')
            ->where('is_active', true)
            ->where(function($q) use ($keyword) {
                $q->where('name_th', 'like', '%' . $keyword . '%')
                  ->orWhere('name_en', 'like', '%' . $keyword . '%')
                  ->orWhere('description_th', 'like', '%' . $keyword . '%');
            })
            ->select('id', 'name_th', 'name_en', 'price', 'image_url', 'type')
            ->limit(10)
            ->get()
            ->map(function($item) use ($lang) {
                return [
                    'id' => $item->id,
                    'title' => ($lang == 'en' && !empty($item->name_en)) ? $item->name_en : $item->name_th,
                    'price' => (float)$item->price,
                    'image_url' => $item->image_url,
                    'module' => 'product',
                    'product_type' => $item->type
                ];
            });

        // 2. ค้นหา Smart Lockers
        $lockers = DB::table('smart_lockers')
            ->where('is_active', true)
            ->where(function($q) use ($keyword) {
                $q->where('title_th', 'like', '%' . $keyword . '%')
                  ->orWhere('title_en', 'like', '%' . $keyword . '%')
                  ->orWhere('description_th', 'like', '%' . $keyword . '%')
                  ->orWhere('locker_number', 'like', '%' . $keyword . '%');
            })
            ->select('id', 'title_th', 'title_en', 'price', 'image_url')
            ->limit(10)
            ->get()
            ->map(function($item) use ($lang) {
                return [
                    'id' => $item->id,
                    'title' => ($lang == 'en' && !empty($item->title_en)) ? $item->title_en : $item->title_th,
                    'price' => (float)$item->price,
                    'image_url' => $item->image_url,
                    'module' => 'locker'
                ];
            });

        // 3. ค้นหา ของรางวัล (Rewards)
        $rewards = DB::table('rewards')
            ->where('is_active', true)
            ->where(function($q) use ($keyword) {
                // อิงจากโครงสร้างที่มี title_th, title_en
                $q->where('title_th', 'like', '%' . $keyword . '%')
                  ->orWhere('title_en', 'like', '%' . $keyword . '%')
                  ->orWhere('description_th', 'like', '%' . $keyword . '%');
            })
            ->select('id', 'title_th', 'title_en', 'points_required', 'image_url')
            ->limit(10)
            ->get()
            ->map(function($item) use ($lang) {
                return [
                    'id' => $item->id,
                    'title' => ($lang == 'en' && !empty($item->title_en)) ? $item->title_en : $item->title_th,
                    'price' => null, // Rewards ใช้แต้มแลก ไม่มีราคา
                    'points_required' => $item->points_required,
                    'image_url' => $item->image_url,
                    'module' => 'reward'
                ];
            });

        // 4. ค้นหา ประกัน (Insurances)
        $insurances = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('insurances')) {
            $insurances = DB::table('insurances')
                ->where('is_active', true)
                ->where(function($q) use ($keyword) {
                    $q->where('title_th', 'like', '%' . $keyword . '%')
                      ->orWhere('title_en', 'like', '%' . $keyword . '%')
                      ->orWhere('description_th', 'like', '%' . $keyword . '%');
                })
                // 🌟 แก้ไขตรงนี้: ลบ 'price' ออกจากการ select แล้วครับ
                ->select('id', 'title_th', 'title_en', 'image_url') 
                ->limit(10)
                ->get()
                ->map(function($item) use ($lang) {
                    return [
                        'id' => $item->id,
                        'title' => ($lang == 'en' && !empty($item->title_en)) ? $item->title_en : $item->title_th,
                        'price' => null, // 🌟 ส่ง null ไปแทนเพื่อให้โครงสร้าง JSON เหมือนตัวอื่นๆ
                        'image_url' => $item->image_url,
                        'module' => 'insurance'
                    ];
                });
        }

        return $this->successResponse([
            'products_and_services' => $products,
            'lockers' => $lockers,
            'rewards' => $rewards,
            'insurances' => $insurances
        ], 'ดึงข้อมูลค้นหาสำเร็จ');
    }
}
