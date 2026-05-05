<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductAdminController extends Controller
{
    public function index()
    {
        $products = DB::table('products')->orderBy('created_at', 'desc')->get();
        return view('admin.products.index', [
            'products' => $products,
            'first_level_active_index' => 'products',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'first_level_active_index' => 'products',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_th' => 'required|string',
            'name_en' => 'required|string',
            'description_th' => 'nullable|string',
            'description_en' => 'nullable|string',
            'type' => 'required|in:service,package,equipment',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('products')->insert([
            'name_th' => $request->name_th,
            'name_en' => $request->name_en,
            'description_th' => $request->description_th,
            'description_en' => $request->description_en,
            'type' => $request->type,
            'price' => $request->price,
            'compare_at_price' => $request->compare_at_price,
            'point_earn' => $request->point_earn ?? 0,
            'image_url' => $imageUrl,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.products')->with('success', 'เพิ่มสินค้า/บริการ เรียบร้อยแล้ว');
    }

    public function edit($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        return view('admin.products.form', [
            'product' => $product,
            'first_level_active_index' => 'products',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_th' => 'required|string',
            'name_en' => 'required|string',
            'description_th' => 'nullable|string',
            'description_en' => 'nullable|string',
            'type' => 'required|in:service,package,equipment',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $product = DB::table('products')->where('id', $id)->first();
        $imageUrl = $product->image_url;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('products')->where('id', $id)->update([
            'name_th' => $request->name_th,
            'name_en' => $request->name_en,
            'description_th' => $request->description_th,
            'description_en' => $request->description_en,
            'type' => $request->type,
            'price' => $request->price,
            'compare_at_price' => $request->compare_at_price,
            'point_earn' => $request->point_earn ?? 0,
            'image_url' => $imageUrl,
            'is_active' => $request->has('is_active'),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.products')->with('success', 'อัปเดตข้อมูลสำเร็จ');
    }
}