<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;
use App\Services\StaffNotificationService;

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

        // 🌟 ดึงข้อมูล User ก่อน เพื่อเอาไปใช้เช็ค Favorite
        $user = $request->user('sanctum');

        // 🌟 แทรกเงื่อนไขการเรียงลำดับ (Order By)
        if ($user) {
            // ถ้าล็อกอินอยู่ ให้เช็คว่าสินค้านี้อยู่ในตาราง favorites ของ User คนนี้ไหม (ถ้ามีให้นับเป็น 1 แล้วเรียงจากมากไปน้อย)
            $query->orderByRaw("(SELECT COUNT(*) FROM favorites WHERE favorites.product_id = products.id AND favorites.user_id = ?) DESC", [$user->id]);
        }
        
        // ให้เรียงตามวันที่สร้างใหม่ล่าสุด เป็นลำดับที่ 2 เสมอ
        $query->orderBy('created_at', 'desc');

        // ดึงข้อมูลสินค้าออกมา
        $rawProducts = $query->get();

        // 🌟 1. ดึงรูปภาพทั้งหมดของสินค้าที่อยู่ใน list
        $productIds = $rawProducts->pluck('id');
        $allImages = DB::table('product_images')
            ->whereIn('product_id', $productIds)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->groupBy('product_id');

        // 🌟 2. ดึงข้อมูล Favorite ของ User ปัจจุบัน (รองรับทั้งตอนล็อกอินและเป็น Guest)
        $user = $request->user('sanctum');
        $favoriteProductIds = [];
        
        if ($user) {
            // ถ้า User ล็อกอินอยู่ ให้ดึงรายการ product_id ที่เคยกดใจไว้
            $favoriteProductIds = DB::table('favorites')
                ->where('user_id', $user->id)
                ->whereIn('product_id', $productIds) // ดึงเฉพาะรายการที่ตรงกับสินค้าหน้าปัจจุบัน
                ->where('item_type', 'product') // (เผื่อในอนาคตมีคอลัมน์ type แยกสินค้ากับรางวัล สามารถเปิดใช้บรรทัดนี้ได้ครับ)
                ->pluck('product_id')
                ->toArray();
        }

        // แมตช์ข้อมูลเพื่อส่งกลับ
        $products = $rawProducts->map(function ($p) use ($lang, $allImages, $favoriteProductIds) {
            // ดึงรูปภาพ Array จากตารางย่อย product_images
            $images = isset($allImages[$p->id]) ? $allImages[$p->id]->pluck('image_url')->toArray() : [];

            // เผื่อกรณีสินค้าเก่าไม่มีในตาราง product_images
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
                'image_url' => count($images) > 0 ? $images[0] : null,
                'images' => $images,
                'point_earn' => $p->point_earn,
                'is_contact_only' => (bool) $p->is_contact_only,
                // 🌟 3. ส่งสถานะ Favorite กลับไป (คืนค่า true/false)
                'is_favorite' => in_array($p->id, $favoriteProductIds),
                // 🌟 ข้อมูลเพิ่มเติมสำหรับตัดสินใจซื้อ (เมลข้อ 3) — แสดงย่อในหน้ารายการได้
                'brand' => $p->brand ?? null,
                'model' => $p->model ?? null,
                'stock_quantity' => $p->stock_quantity ?? null,
                'in_stock' => is_null($p->stock_quantity) ? true : $p->stock_quantity > 0,
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
            'quotation_url' => $order->quotation_url, // 🌟 ลิงก์ไฟล์ใบเสนอราคา (ดาวน์โหลดได้ตรงจาก URL นี้เลย)
            'receipt_url' => $order->receipt_url, // 🌟 ลิงก์ไฟล์ใบเสร็จรับเงิน (ดาวน์โหลดได้ตรงจาก URL นี้เลย)
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
        $setting = DB::table('settings')->where('id', 1)->first();
        DB::table('customer_wallets')->where('user_id', $userId)->increment('current_points', $setting->review_point);

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
            // 🌟 is_contact_only = true หมายถึงสินค้ากลุ่ม "ต้องสำรวจหน้างาน/ขอใบเสนอราคา" (เมลข้อ 3.2)
            // mobile ควรใช้ค่านี้สลับปุ่ม "ซื้อเลย/ใส่ตะกร้า" (false) กับ "ขอใบเสนอราคา" (true)
            // แทนการโชว์ปุ่ม "ติดต่อฝ่ายขาย" เหมือนกันทุกสินค้า
            'is_contact_only' => (bool) $product->is_contact_only,
            // 🌟 ข้อมูลที่ QA ระบุว่า "จำเป็นต่อการตัดสินใจซื้อ" (เมลข้อ 3)
            'brand' => $product->brand ?? null,
            'model' => $product->model ?? null,
            'stock_quantity' => $product->stock_quantity ?? null,
            'in_stock' => is_null($product->stock_quantity) ? true : $product->stock_quantity > 0,
            'warranty_months' => $product->warranty_months ?? null,
            'return_policy' => $product->return_policy_th ?? null,
            'shipping_fee' => $product->shipping_fee ?? null,
            'install_fee' => $product->install_fee ?? null,
            'compatible_with' => $product->compatible_with ?? null,
        ];

        return $this->successResponse($data, 'Product detail retrieved');
    }

    // ==========================================
    // 3. ขอใบเสนอราคา (Request For Quote) — สำหรับสินค้าที่ is_contact_only = true
    // ตามเมลข้อ 3.2: "สินค้าที่ต้องสำรวจหน้างาน" — ให้ลูกค้าระบุสถานที่ อัปโหลดภาพหน้างาน
    // และเลือกวันนัดหมายสำรวจได้ พร้อมออกหมายเลขคำขอให้ติดตามสถานะ
    // ==========================================
    public function submitQuoteRequest(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'site_address' => 'required|string',
            'site_image' => 'nullable|file|image|max:10240', // สูงสุด 10MB
            'preferred_survey_date' => 'nullable|date|after_or_equal:today',
            'detail' => 'nullable|string',
        ]);

        $user = $request->user();

        $siteImageUrl = null;
        if ($request->hasFile('site_image')) {
            $path = $request->file('site_image')->store('quote-requests', 'public');
            $siteImageUrl = '/storage/' . $path;
        }

        // 🌟 สร้างหมายเลขคำขอ เช่น RFQ-20260904-0001 (นับตามจำนวนคำขอของวันนั้น)
        $todayPrefix = 'RFQ-' . now()->format('Ymd');
        $countToday = DB::table('quote_requests')->where('request_number', 'like', $todayPrefix . '%')->count();
        $requestNumber = $todayPrefix . '-' . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);

        $id = DB::table('quote_requests')->insertGetId([
            'request_number' => $requestNumber,
            'user_id' => $user?->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity ?? 1,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'site_address' => $request->site_address,
            'site_image_url' => $siteImageUrl,
            'preferred_survey_date' => $request->preferred_survey_date,
            'detail' => $request->detail,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 🌟 แจ้งเตือนแผนก Marketing ที่ดูแลสินค้า/บริการ โดยอัตโนมัติ (เมล QA ข้อ 5)
        StaffNotificationService::notifyRole(
            'marketing',
            'มีคำขอใบเสนอราคาใหม่',
            "เลขที่ {$requestNumber} จาก {$request->customer_name}",
            '/admin/quote-requests/' . $id,
            'quote_request'
        );

        return $this->successResponse([
            'id' => $id,
            'request_number' => $requestNumber,
            'status' => 'pending',
        ], 'ส่งคำขอใบเสนอราคาเรียบร้อยแล้ว ทีมงานจะติดต่อกลับเพื่อนัดสำรวจหน้างาน');
    }

    public function getMyQuoteRequests(Request $request)
    {
        $userId = $request->user()->id;

        $requests = DB::table('quote_requests')
            ->leftJoin('products', 'quote_requests.product_id', '=', 'products.id')
            ->where('quote_requests.user_id', $userId)
            ->select('quote_requests.*', 'products.name_th as product_name')
            ->orderBy('quote_requests.created_at', 'desc')
            ->get();

        return $this->successResponse($requests, 'Quote requests retrieved successfully');
    }

    public function getQuoteRequestDetail(Request $request, $id)
    {
        $userId = $request->user()->id;

        $quote = DB::table('quote_requests')
            ->leftJoin('products', 'quote_requests.product_id', '=', 'products.id')
            ->where('quote_requests.id', $id)
            ->where('quote_requests.user_id', $userId)
            ->select('quote_requests.*', 'products.name_th as product_name')
            ->first();

        if (!$quote) {
            return $this->errorResponse('Quote request not found', 404);
        }

        return $this->successResponse($quote, 'Quote request detail retrieved');
    }

    // ==========================================
    // 2. ตะกร้าสินค้า (Cart)
    // ==========================================
    // ==========================================
    // 🌟 สินค้าจับกลุ่ม (Bundle) — ใช้ร่วมกันทั้ง getCart() (แสดง preview + คำแนะนำ)
    // และ checkout() (คำนวณส่วนลดจริงฝั่ง server ก่อนตัดเงิน ห้ามเชื่อราคาจาก client)
    // ==========================================

    /**
     * ดึงบันเดิลที่ยัง is_active พร้อมรายการสินค้าในแต่ละชุด + คำนวณราคาปกติรวม/ส่วนที่ประหยัดได้
     */
    private function getActiveBundlesWithItems()
    {
        $bundles = DB::table('product_bundles')->where('is_active', true)->get();

        foreach ($bundles as $bundle) {
            $bundle->items = DB::table('product_bundle_items')
                ->join('products', 'product_bundle_items.product_id', '=', 'products.id')
                ->where('product_bundle_items.product_bundle_id', $bundle->id)
                ->select('products.id as product_id', 'products.name_th', 'products.name_en', 'products.price', 'products.image_url', 'product_bundle_items.quantity as required_qty')
                ->get();

            $bundle->original_total = $bundle->items->sum(fn ($i) => $i->price * $i->required_qty);
            $bundle->savings = max(0, $bundle->original_total - $bundle->bundle_price);
        }

        return $bundles->sortByDesc('savings')->values();
    }

    /**
     * เช็คว่าตะกร้า (product_id => quantity) ตรงกับบันเดิลไหนครบชุดบ้าง แล้วหักสต็อกที่ใช้ไปแล้วออก
     * (กันไม่ให้สินค้าชุดเดียวกันถูกนับซ้ำ 2 บันเดิลพร้อมกัน) เลือกบันเดิลที่ประหยัดมากสุดก่อนเสมอ
     *
     * @param array<int,int> $cartQtyByProduct [product_id => quantity]
     * @return array{applied: array, total_discount: float, remaining_qty: array<int,int>, all_bundles: \Illuminate\Support\Collection}
     */
    private function calculateBundleMatches(array $cartQtyByProduct): array
    {
        $bundles = $this->getActiveBundlesWithItems();
        $remaining = $cartQtyByProduct;
        $applied = [];
        $totalDiscount = 0.0;

        foreach ($bundles as $bundle) {
            if ($bundle->savings <= 0) continue; // ตั้งราคาชุดสูงกว่า/เท่าราคาปกติ ไม่มีประโยชน์ที่จะ apply ให้

            $canApply = true;
            foreach ($bundle->items as $item) {
                if (($remaining[$item->product_id] ?? 0) < $item->required_qty) {
                    $canApply = false;
                    break;
                }
            }

            if ($canApply) {
                foreach ($bundle->items as $item) {
                    $remaining[$item->product_id] -= $item->required_qty;
                }
                $applied[] = [
                    'bundle_id' => $bundle->id,
                    'name_th' => $bundle->name_th,
                    'name_en' => $bundle->name_en,
                    'original_total' => (float) $bundle->original_total,
                    'bundle_price' => (float) $bundle->bundle_price,
                    'savings' => (float) $bundle->savings,
                ];
                $totalDiscount += $bundle->savings;
            }
        }

        return [
            'applied' => $applied,
            'total_discount' => $totalDiscount,
            'remaining_qty' => $remaining,
            'all_bundles' => $bundles,
        ];
    }

    /**
     * หาบันเดิลที่ลูกค้ามีสินค้าในตะกร้าอยู่แล้วบางส่วน (แต่ยังไม่ครบชุด) เพื่อแนะนำ "ซื้อเพิ่มอีกนิดรับราคาชุด"
     */
    private function calculateBundleSuggestions(array $cartQtyByProduct, array $bundleMatch): array
    {
        $appliedIds = collect($bundleMatch['applied'])->pluck('bundle_id')->all();
        $suggestions = [];

        foreach ($bundleMatch['all_bundles'] as $bundle) {
            if (in_array($bundle->id, $appliedIds, true) || $bundle->savings <= 0) continue;

            $haveAny = false;
            $missing = [];
            foreach ($bundle->items as $item) {
                $have = $cartQtyByProduct[$item->product_id] ?? 0;
                if ($have > 0) $haveAny = true;
                if ($have < $item->required_qty) {
                    $missing[] = [
                        'product_id' => $item->product_id,
                        'name_th' => $item->name_th,
                        'name_en' => $item->name_en,
                        'price' => (float) $item->price,
                        'image_url' => $item->image_url ?? null, // 🌟 ใช้แสดงการ์ดสินค้าที่แนะนำให้ซื้อเพิ่ม
                        'need_qty' => $item->required_qty - $have,
                    ];
                }
            }

            if ($haveAny && !empty($missing)) {
                $suggestions[] = [
                    'bundle_id' => $bundle->id,
                    'name_th' => $bundle->name_th,
                    'name_en' => $bundle->name_en,
                    'bundle_price' => (float) $bundle->bundle_price,
                    'savings' => (float) $bundle->savings,
                    'missing_products' => $missing,
                ];
            }
        }

        return $suggestions;
    }

    /**
     * รวม quantity ต่อ product_id จากรายการในตะกร้า/ที่กำลังจะสั่งซื้อ — ข้ามรายการที่เป็นแพ็กเกจคิดราคาตามเดือน
     * (duration_months) เพราะราคาที่เก็บไว้ถูกคูณจำนวนเดือนไปแล้ว เทียบราคาต่อชิ้นปกติของบันเดิลไม่ได้ตรงๆ
     */
    private function buildBundleQtyMap($items): array
    {
        $qtyMap = [];
        foreach ($items as $item) {
            if (!empty($item->duration_months)) continue;
            $qtyMap[$item->product_id] = ($qtyMap[$item->product_id] ?? 0) + $item->quantity;
        }
        return $qtyMap;
    }

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

        // 🌟 4. เช็คสินค้าจับกลุ่ม (Bundle) — ถ้าสินค้าในตะกร้าครบชุดไหนแล้ว หักส่วนลดให้อัตโนมัติ
        // ถ้ามีแค่บางส่วน ส่งเป็นคำแนะนำให้ซื้อเพิ่มแทน (bundle_suggestions)
        $bundleQtyMap = $this->buildBundleQtyMap($items);
        $bundleMatch = $this->calculateBundleMatches($bundleQtyMap);
        $bundleDiscount = $bundleMatch['total_discount'];
        $bundleSuggestions = $this->calculateBundleSuggestions($bundleQtyMap, $bundleMatch);

        // 3. คำนวณยอดสุทธิ (Net Total) ป้องกันยอดติดลบ — รวมส่วนลด reward + bundle เข้าด้วยกัน
        $netTotal = max(0, $subtotal - $discountAmount - $bundleDiscount);

        return $this->successResponse([
            'items' => $items,
            'summary' => [
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'reward_title' => $rewardTitle,
                'bundle_discount_amount' => $bundleDiscount, // 🌟 ส่วนลดจากสินค้าจับกลุ่มที่ครบชุดแล้ว
                'applied_bundles' => $bundleMatch['applied'], // 🌟 รายชื่อชุด/ส่วนลดที่ apply ให้อัตโนมัติ
                'net_total' => $netTotal
            ],
            'bundle_suggestions' => $bundleSuggestions // 🌟 "ซื้อ X เพิ่มอีก รับราคาชุด Y ประหยัด Z" (แสดงในตะกร้า)
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

        // 🌟 ส่งข้อมูลสินค้าจับกลุ่มกลับไปทันทีหลังเพิ่มลงตะกร้า — แอปแสดง "ซื้อคู่กันรับราคาชุด" ได้เลยโดยไม่ต้องยิง GET /cart ซ้ำ
        // (โครงสร้างเดียวกับ GET /ecommerce/cart → bundle_suggestions / summary.applied_bundles)
        $cartItems = DB::table('cart_items')->where('cart_id', $cartId)->get(['product_id', 'quantity', 'duration_months']);
        $bundleQtyMap = $this->buildBundleQtyMap($cartItems);
        $bundleMatch = $this->calculateBundleMatches($bundleQtyMap);

        return $this->successResponse([
            'cart_count' => (int) $cartItems->sum('quantity'),
            'applied_bundles' => $bundleMatch['applied'],
            'bundle_discount_amount' => $bundleMatch['total_discount'],
            'bundle_suggestions' => $this->calculateBundleSuggestions($bundleQtyMap, $bundleMatch),
        ], 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว');
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
        $addresses = \App\Services\AddressService::activeQuery($request->user()->id) // 🌟 ไม่แสดงที่อยู่ที่ลบแล้ว
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->successResponse($addresses, 'Addresses retrieved');
    }

    // 🌟 1. ดึงรายละเอียดที่อยู่แบบเจาะจง (เพื่อเอาไปโชว์ในหน้าแก้ไข)
    public function getAddressDetail(Request $request, $id)
    {
        $address = \App\Services\AddressService::activeQuery($request->user()->id)
            ->where('id', $id)
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

        $address = \App\Services\AddressService::activeQuery($request->user()->id) // ที่อยู่ที่ลบแล้วแก้ไขไม่ได้
            ->where('id', $id)
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

    // 🌟 4. ลบที่อยู่ (คอมเมนต์ข้อ 8) — DELETE /ecommerce/addresses/{id} หรือ POST /ecommerce/addresses/{id}/delete
    // ที่อยู่ที่เคยใช้ในออเดอร์/แจ้งซ่อม/จองตู้เซฟ/ของรางวัล จะถูก soft delete (ซ่อนจากรายการ แต่เก็บไว้เป็นประวัติ)
    public function deleteAddress(Request $request, $id)
    {
        $mode = \App\Services\AddressService::delete($request->user()->id, (int) $id);

        if ($mode === null) return $this->errorResponse('ไม่พบข้อมูลที่อยู่นี้', 404);

        return $this->successResponse(['id' => (int) $id, 'mode' => $mode], 'ลบที่อยู่เรียบร้อยแล้ว');
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
            'tax_id' => 'nullable|string|max:20', // 🌟 เลขผู้เสียภาษี (สำหรับออกใบกำกับภาษี)
            'branch' => 'nullable|string|max:100', // 🌟 สาขา
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:20480' // สูงสุด 20MB
        ]);

        $user = $request->user();
        $selectedCartItemIds = $request->cart_id;

        $cartItems = DB::table('cart_items')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
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
            // 🌟 รับได้ทั้งโค้ด RWD-xxxx และรหัสที่แอดมินกรอกส่งให้ (voucher_code) — ใช้ได้เฉพาะคูปองที่มีมูลค่าส่วนลด
            $usedRewardCode = DB::table('customer_reward_codes')
                ->where(function ($q) use ($request) {
                    $q->where('code', $request->reward_code)->orWhere('voucher_code', $request->reward_code);
                })
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('discount_amount', '>', 0)
                ->first();

            if (!$usedRewardCode) {
                return $this->errorResponse('โค้ดส่วนลดนี้ไม่ถูกต้อง หรือถูกใช้งานไปแล้ว', 400);
            }

            // ได้ส่วนลดตามที่แลกมา (ไม่ต้องเช็คแต้มแล้วเพราะถูกหักแต้มไปตอนแลกแล้ว)
            $discount = (float)$usedRewardCode->discount_amount;
        }
        // ==========================================

        // 🌟 คำนวณส่วนลดจากสินค้าจับกลุ่ม (Bundle) ซ้ำอีกครั้งฝั่ง server จากรายการที่เลือก checkout จริง
        // (ห้ามเชื่อส่วนลดที่ client ส่งมา เพราะกระทบเงินจริงที่จะตัดผ่าน payment gateway)
        $bundleQtyMap = $this->buildBundleQtyMap($cartItems);
        $bundleMatch = $this->calculateBundleMatches($bundleQtyMap);
        $bundleDiscount = $bundleMatch['total_discount'];

        $discount = $discount + $bundleDiscount; // รวมส่วนลด reward + bundle เป็นยอดเดียว (เหมือนเดิม)

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
            // 🌟 บันทึก/อัปเดตเลขผู้เสียภาษีและสาขาลงโปรไฟล์ลูกค้า (ถ้าลูกค้าส่งมาตอน Checkout)
            $taxData = array_filter($request->only(['tax_id', 'branch']), fn ($v) => $v !== null && $v !== '');
            if (!empty($taxData)) {
                $taxData['updated_at'] = now();
                DB::table('customer_profiles')->updateOrInsert(['user_id' => $user->id], $taxData);
            }

            $orderId = DB::table('orders')->insertGetId([
                'order_number' => 'ORD-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'subtotal' => $subtotal,
                'discount' => $discount, // 🌟 บันทึกส่วนลดรวม (reward + bundle) ลงบิล
                'bundle_discount' => $bundleDiscount, // 🌟 แยกยอดส่วนลดจากบันเดิลไว้ต่างหากเพื่อตรวจสอบย้อนหลัง
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
                DB::table('cart_items')->where('id', $item->cart_item_id)->delete();
            }


            // 🌟 ใส่ของใหม่: เปลี่ยนสถานะโค้ดเป็นใช้งานแล้ว
            if ($usedRewardCode) {
                DB::table('customer_reward_codes')->where('id', $usedRewardCode->id)->update([
                    'status' => 'used',
                    'used_at' => now(),
                    'updated_at' => now()
                ]);
                \App\Services\RewardService::log($usedRewardCode->id, 'used', 'ใช้ส่วนลดกับคำสั่งซื้อ', 'customer', $user->id);
            }

            DB::commit();

            // 🌟 1. ดึงข้อมูลออเดอร์ที่เพิ่งสร้างเสร็จ
            $order = DB::table('orders')->where('id', $orderId)->first();
            $paymentUrl = null;

            // 🌟 2. ส่งลิงก์ WebView ถ้าลูกค้าเลือกจ่ายผ่าน BBL App to App
            // if ($request->payment_gateway === 'bbl') {
                $paymentUrl = url('/payment/bbl/redirect/' . $order->order_number);
            // } else {
            //     // สำหรับ Payment Gateway ตัวอื่นๆ ในอนาคต
            //     $paymentUrl = "https://placeholder-gateway.com/pay/" . $order->order_number;
            // }

            return $this->successResponse([
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'bundle_discount' => $bundleDiscount, // 🌟 ส่วนลดจากสินค้าจับกลุ่มที่ apply ให้ตอน checkout
                'applied_bundles' => $bundleMatch['applied'], // 🌟 ชื่อชุด/รายละเอียดที่ได้ส่วนลดไป
                'total_amount' => $totalAmount,
                'payment_url' => $paymentUrl // 🌟 ส่งลิงก์ WebView กลับไปให้แอป
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
            'reward_code' => 'nullable|string',
            'duration_months' => 'nullable|integer|in:1,3,6,12',
            'tax_id' => 'nullable|string|max:20', // 🌟 เลขผู้เสียภาษี (สำหรับออกใบกำกับภาษี)
            'branch' => 'nullable|string|max:100', // 🌟 สาขา
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
            // 🌟 รับได้ทั้งโค้ด RWD-xxxx และรหัสที่แอดมินกรอกส่งให้ (voucher_code) — ใช้ได้เฉพาะคูปองที่มีมูลค่าส่วนลด
            $usedRewardCode = DB::table('customer_reward_codes')
                ->where(function ($q) use ($request) {
                    $q->where('code', $request->reward_code)->orWhere('voucher_code', $request->reward_code);
                })
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('discount_amount', '>', 0)
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
            // 🌟 บันทึก/อัปเดตเลขผู้เสียภาษีและสาขาลงโปรไฟล์ลูกค้า (ถ้าลูกค้าส่งมาตอน Buy Now)
            $taxData = array_filter($request->only(['tax_id', 'branch']), fn ($v) => $v !== null && $v !== '');
            if (!empty($taxData)) {
                $taxData['updated_at'] = now();
                DB::table('customer_profiles')->updateOrInsert(['user_id' => $user->id], $taxData);
            }

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
                \App\Services\RewardService::log($usedRewardCode->id, 'used', 'ใช้ส่วนลดกับคำสั่งซื้อ', 'customer', $user->id);
            }

            DB::commit();

            // 🌟 1. ดึงข้อมูลออเดอร์ที่เพิ่งสร้างเสร็จ
            $order = DB::table('orders')->where('id', $orderId)->first();
            $paymentUrl = null;

            // 🌟 2. ส่งลิงก์ WebView ถ้าลูกค้าเลือกจ่ายผ่าน BBL App to App
            // if ($request->payment_gateway === 'bbl') {
                $paymentUrl = url('/payment/bbl/redirect/' . $order->order_number);
            // } else {
            //     // สำหรับ Payment Gateway ตัวอื่นๆ ในอนาคต
            //     $paymentUrl = "https://placeholder-gateway.com/pay/" . $order->order_number;
            // }

            return $this->successResponse([
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'payment_url' => $paymentUrl // 🌟 ส่งลิงก์ WebView กลับไปให้แอป
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

            // 🌟 แจ้งเตือนแผนก Security และบัญชี ที่ดูแลออเดอร์ โดยอัตโนมัติ (เมล QA ข้อ 5)
            // — จุดนี้การันตีว่าเป็นครั้งแรกที่ออเดอร์นี้เปลี่ยนเป็น completed เท่านั้น (เช็ค status
            // เดิมไปแล้วด้านบนก่อนเข้า transaction กันแจ้งซ้ำถ้า callback ยิงเข้ามาซ้ำ)
            StaffNotificationService::notifyRoles(
                ['security_admin', 'accounting'],
                'มีคำสั่งซื้อใหม่ที่ชำระเงินแล้ว',
                "คำสั่งซื้อ #{$order->order_number} ยอดรวม " . number_format($order->total_amount ?? 0, 2) . ' บาท',
                '/admin/orders/' . $order->id,
                'order'
            );

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

    // 1. ฟังก์ชันกดแลกของรางวัล (หักแต้มแล้วได้โค้ด / แลกสินค้า)
    public function redeemReward(Request $request)
    {
        $data = $request->validate([
            'reward_id' => 'required|integer',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'address_id' => 'nullable|integer',
            'address_text' => 'nullable|string',
        ]);

        // 🌟 logic รวมอยู่ที่ RewardService (เช็ค Tier ลูกค้า + ล็อก wallet กันกดซ้ำ + บันทึก timeline)
        // สินค้า: ส่งที่อยู่มาตอนนี้ หรือตอนกด "ใช้คูปอง" (POST /rewards/my-codes/{id}/use) ก็ได้
        $result = \App\Services\RewardService::redeem($request->user()->id, (int) $data['reward_id'], $data);

        return $result['ok']
            ? $this->successResponse($result['data'], $result['message'])
            : $this->errorResponse($result['message'], $result['status']);
    }

    // 2. คูปองส่วนลดที่ยังใช้ได้ (ใช้แสดงในหน้า checkout ให้เลือกโค้ด)
    public function getMyRewardCodes(Request $request)
    {
        $codes = \App\Services\RewardService::codesQuery()
            ->where('customer_reward_codes.user_id', $request->user()->id)
            ->where('customer_reward_codes.discount_amount', '>', 0)
            ->where('customer_reward_codes.status', 'active')
            ->orderBy('customer_reward_codes.created_at', 'desc')
            ->get()
            ->map(fn ($row) => \App\Services\RewardService::presentCode($row));

        return $this->successResponse($codes, 'ดึงรายการโค้ดส่วนลดที่ใช้งานได้สำเร็จ');
    }

    // 3. ของรางวัล/คูปองที่เคยแลกทั้งหมด — filter ได้ด้วย ?status=active,shipping ?reward_type=product|voucher|discount
    public function getMyRewardCodesAll(Request $request)
    {
        $query = \App\Services\RewardService::codesQuery()
            ->where('customer_reward_codes.user_id', $request->user()->id);

        if ($request->filled('status')) {
            $statuses = $request->query('status');
            $query->whereIn('customer_reward_codes.status', is_array($statuses) ? $statuses : explode(',', (string) $statuses));
        }
        if ($request->filled('reward_type')) {
            $query->where('rewards.reward_type', $request->query('reward_type'));
        }

        $codes = $query->orderBy('customer_reward_codes.created_at', 'desc')
            ->get()
            ->map(fn ($row) => \App\Services\RewardService::presentCode($row));

        return $this->successResponse($codes, 'ดึงรายการของรางวัลที่เคยแลกสำเร็จ');
    }

    // 4. รายละเอียดคูปอง/ของรางวัล 1 รายการ + timeline สถานะการจัดส่ง
    public function getMyRewardCodeDetail(Request $request, $id)
    {
        $row = \App\Services\RewardService::codesQuery()
            ->where('customer_reward_codes.user_id', $request->user()->id)
            ->where('customer_reward_codes.id', $id)
            ->first();

        if (!$row) return $this->errorResponse('ไม่พบคูปองนี้', 404);

        return $this->successResponse(\App\Services\RewardService::presentCode($row, true), 'ดึงรายละเอียดคูปองสำเร็จ');
    }

    // 5. ลูกค้ากด "ใช้คูปอง"
    //    - สินค้า: ส่งที่อยู่จัดส่ง (address_id หรือ address_text + customer_name + customer_phone) → สถานะ "ยืนยันการจัดส่ง"
    //      หลังจากนั้นสถานะ กำลังดำเนินการ / กำลังจัดส่ง / จัดส่งสำเร็จ เปลี่ยนได้จากหลังบ้านเท่านั้น
    //    - วอยเชอร์/ส่วนลด: เปลี่ยนเป็น "ใช้แล้ว" (วอยเชอร์ต้องได้รับรหัสจากแอดมินก่อน)
    public function useRewardCode(Request $request, $id)
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'address_id' => 'nullable|integer',
            'address_text' => 'nullable|string',
        ]);

        $result = \App\Services\RewardService::customerUse($request->user()->id, (int) $id, array_filter($data, fn ($v) => $v !== null));
        if (!$result['ok']) return $this->errorResponse($result['message'], $result['status']);

        $row = \App\Services\RewardService::codesQuery()->where('customer_reward_codes.id', $id)->first();

        return $this->successResponse(\App\Services\RewardService::presentCode($row, true), $result['message']);
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
                // 🌟 ค้นหาจากชื่อสินค้าเท่านั้น (ไม่ค้นใน description/detail แล้ว ตามคอมเมนต์ข้อ 7)
                $q->where('name_th', 'like', '%' . $keyword . '%')
                  ->orWhere('name_en', 'like', '%' . $keyword . '%');
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
        $rewardQuery = DB::table('rewards')
            ->where('is_active', true)
            ->where(function($q) use ($keyword) {
                // อิงจากโครงสร้างที่มี title_th, title_en (ค้นจากชื่ออย่างเดียว)
                $q->where('title_th', 'like', '%' . $keyword . '%')
                  ->orWhere('title_en', 'like', '%' . $keyword . '%');
            });
        // 🌟 ไม่แสดงรางวัลที่ Tier ของลูกค้ายังไม่ถึง
        \App\Services\RewardService::applyTierFilter($rewardQuery, optional($request->user('sanctum'))->id);
        $rewards = $rewardQuery
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
                      ->orWhere('title_en', 'like', '%' . $keyword . '%');
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

    // ==========================================
    // 7. รายการชำระเงินที่ยังไม่สำเร็จ (Pending Payments)
    // ==========================================
    public function getPendingPayments(Request $request)
    {
        $user = $request->user();

        // ดึงออเดอร์ที่สถานะเป็น 'pending_payment' หรือ 'failed'
        $pendingOrders = DB::table('orders')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending_payment', 'failed'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // หาจำนวนชิ้นสินค้าคร่าวๆ มาโชว์
                $totalItems = DB::table('order_items')->where('order_id', $order->id)->sum('quantity');
                
                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'total_items' => (int) $totalItems,
                    'created_at' => \Carbon\Carbon::parse($order->created_at)->format('d M Y - H:i น.')
                ];
            });

        return $this->successResponse($pendingOrders, 'ดึงรายการที่รอชำระเงินสำเร็จ');
    }

    // ==========================================
    // 8. ชำระเงินใหม่ (Retry Payment)
    // ==========================================
    public function retryPayment(Request $request, $id)
    {
        $request->validate([
            'payment_gateway' => 'required|string', // เผื่อลูกค้าอยากเปลี่ยนวิธีจ่ายเงิน เช่น จากบัตรเครดิต เป็น promptpay
        ]);

        $orderId = $id;
        $user = $request->user();

        // เช็คว่าออเดอร์นี้เป็นของลูกค้าคนนี้จริง และสถานะยังไม่ได้จ่ายเงิน
        $order = DB::table('orders')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return $this->errorResponse('ไม่พบคำสั่งซื้อนี้', 404);
        }

        if (!in_array($order->status, ['pending_payment', 'failed'])) {
            return $this->errorResponse('คำสั่งซื้อนี้ชำระเงินไปแล้ว หรือถูกยกเลิก ไม่สามารถชำระซ้ำได้', 400);
        }

        DB::beginTransaction();
        try {
            // อัปเดตช่องทางการชำระเงินใหม่ และอัปเดตเวลา
            DB::table('orders')->where('id', $id)->update([
                'payment_gateway' => $request->payment_gateway,
                'updated_at' => now()
            ]);

            DB::commit();

            // 🌟 1. ดึงข้อมูลออเดอร์ที่เพิ่งสร้างเสร็จ
            $order = DB::table('orders')->where('id', $orderId)->first();
            $paymentUrl = null;
            $subtotal = (float)$order->subtotal;
            $discount = (float)$order->discount;
            $totalAmount = (float)$order->total_amount;

            // 🌟 2. ส่งลิงก์ WebView ถ้าลูกค้าเลือกจ่ายผ่าน BBL App to App
            // if ($request->payment_gateway === 'bbl') {
                $paymentUrl = url('/payment/bbl/redirect/' . $order->order_number);
            // } else {
            //     // สำหรับ Payment Gateway ตัวอื่นๆ ในอนาคต
            //     $paymentUrl = "https://placeholder-gateway.com/pay/" . $order->order_number;
            // }

            return $this->successResponse([
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'payment_url' => $paymentUrl // 🌟 ส่งลิงก์ WebView กลับไปให้แอป
            ], 'Order created successfully. Please proceed to payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Checkout failed: ' . $e->getMessage(), 500);
        }
    }
}
