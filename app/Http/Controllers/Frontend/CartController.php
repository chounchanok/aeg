<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // แสดงหน้าตะกร้าสินค้า
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
                // เปลี่ยนจาก products.name เป็น name_th และ name_en
                ->select(
                    'cart_items.id as cart_item_id',
                    'products.id as product_id',
                    'products.name_th',
                    'products.name_en',
                    'products.price',
                    'cart_items.quantity',
                    'products.image_url'
                )
                ->get()
                ->map(function ($item) {
                    // สร้างตัวแปร name หลอกๆ ให้หน้า Blade นำไปใช้งานได้
                    $item->name = $item->name_th ?? $item->name_en ?? 'ไม่มีชื่อสินค้า';
                    return $item;
                });

            $totalAmount = $cartItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });
        }

        return view('frontend.cart', compact('cartItems', 'totalAmount'));
    }

    // เพิ่มสินค้าลงตะกร้า
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $product = DB::table('products')->where('id', $request->product_id)->first();

        if (!$product) return back()->withErrors(['error' => 'ไม่พบสินค้า']);

        // 1. หาตะกร้าของ User นี้
        $cart = DB::table('carts')->where('user_id', $user->id)->first();

        // 2. ถ้ายังไม่มีตะกร้า ให้สร้างใหม่
        if (!$cart) {
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
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
                'price' => ($product->price * $request->quantity),
                'duration_months' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect()->route('cart')->with('success', 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว');
    }

    // ลบสินค้าออกจากตะกร้า
    public function removeFromCart($cartItemId)
    {
        DB::table('cart_items')->where('id', $cartItemId)->delete();
        return back()->with('success', 'ลบสินค้าออกจากตะกร้าแล้ว');
    }
}
