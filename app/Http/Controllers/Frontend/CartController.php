<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // ค้นหาตะกร้าของ User คนนี้ หรือสร้างใหม่ถ้ายังไม่มี
        $cart = Cart::with('items.product')->firstOrCreate(['user_id' => $user->id]);

        return view('frontend.cart', compact('cart'));
    }
}