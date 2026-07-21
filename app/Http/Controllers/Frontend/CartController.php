<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // แสดงหน้าตะกร้าสินค้า
    public function index()
    {
        $user = Auth::user();
        $cart = DB::table('carts')->where('user_id', $user->id)->first();

        $cartItems = collect();
        $totalAmount = 0;

        if ($cart) {
            $cartItems = DB::table('cart_items')
                ->join('products', 'cart_items.product_id', '=', 'products.id')
                ->where('cart_items.cart_id', $cart->id)
                ->select(
                    'cart_items.id as cart_item_id',
                    'products.id as product_id',
                    'products.name_th',
                    'products.name_en',
                    'cart_items.price', // 🌟 จุดที่ 1: ดึงราคาที่คูณเดือนแล้วจาก cart_items
                    'cart_items.duration_months', // 🌟 จุดที่ 2: ดึงระยะเวลาเดือนออกมาด้วย
                    'cart_items.quantity',
                    'products.image_url'
                )
                ->get()
                ->map(function ($item) {
                    $item->name = $item->name_th ?? $item->name_en ?? 'ไม่มีชื่อสินค้า';
                    return $item;
                });

            $totalAmount = $cartItems->sum(function ($item) {
                return $item->price * $item->quantity; // ตอนนี้จะคูณด้วยราคาที่รวมเดือนแล้วครับ
            });
        }

        return view('frontend.cart', compact('cartItems', 'totalAmount'));
    }

    // เพิ่มสินค้าลงตะกร้า
    public function addToCart(Request $request)
    {
        // 1. รับค่า duration_months เพิ่มเข้ามา
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'duration_months' => 'nullable|integer|min:1' // 🌟 เพิ่มตรงนี้
        ]);

        $user = Auth::user();
        $product = DB::table('products')->where('id', $request->product_id)->first();

        if (!$product) return back()->withErrors(['error' => 'ไม่พบสินค้า']);

        // 🌟 2. คำนวณราคา: ถ้าระบุเดือนมา เอาไปคูณราคาตั้งต้น
        $quantity = $request->quantity ?? 1;
        $duration = $request->duration_months ?? 1; // ถ้าไม่ได้ส่งมาให้ถือว่าเป็น 1
        $unitPrice = $product->price * $duration * $quantity; // ราคาต่อหน่วยที่คูณเดือนแล้ว

        // 3. หาตะกร้าของ User นี้
        $cart = DB::table('carts')->where('user_id', $user->id)->first();

        // 4. ถ้ายังไม่มีตะกร้า ให้สร้างใหม่
        if (!$cart) {
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $cart = DB::table('carts')->where('id', $cartId)->first();
        }

        // 5. เช็คว่ามีสินค้านี้ในตะกร้า "ด้วยระยะเวลาเดียวกัน" หรือไม่
        $existingItem = DB::table('cart_items')
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('duration_months', $duration) // 🌟 แยกไอเทมตามจำนวนเดือน
            ->first();

        if ($existingItem) {
            // มีอยู่แล้ว อัปเดตแค่จำนวน
            DB::table('cart_items')->where('id', $existingItem->id)
                ->increment('quantity', $quantity);
        } else {
            // ยังไม่มี เพิ่มเข้าไปใหม่
            DB::table('cart_items')->insert([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $unitPrice, // 🌟 เก็บราคาต่อหน่วยที่คูณจำนวนเดือนแล้ว
                'duration_months' => $duration, // 🌟 เก็บรายละเอียดเดือนลงตะกร้า
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect()->route('cart')->with('success', 'เพิ่มแพ็กเกจลงตะกร้าเรียบร้อยแล้ว');
    }

    // ลบสินค้าออกจากตะกร้า
    public function removeFromCart($cartItemId)
    {
        DB::table('cart_items')->where('id', $cartItemId)->delete();
        return back()->with('success', 'ลบสินค้าออกจากตะกร้าแล้ว');
    }
}
