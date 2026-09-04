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
        $categories = DB::table('service_categories')->orderBy('created_at', 'desc')->get();
        return view('admin.products.form', [
            'categories' => $categories,
            'first_level_active_index' => 'products',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 🌟 1. อัปเกรดฟังก์ชันบันทึกสินค้าใหม่ (Multiple Images)
    public function store(Request $request)
    {
        $request->validate([
            'name_th' => 'required|string',
            'name_en' => 'required|string',
            'description_th' => 'nullable|string',
            'description_en' => 'nullable|string',
            'type' => 'required|numeric',
            'price' => 'required|numeric',
            'stock_quantity' => 'nullable|integer|min:0',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'warranty_months' => 'nullable|integer|min:0',
            'return_policy_th' => 'nullable|string',
            'shipping_fee' => 'nullable|numeric|min:0',
            'install_fee' => 'nullable|numeric|min:0',
            'compatible_with' => 'nullable|string',
            'images' => 'nullable|array', // เปลี่ยนจาก image เป็น images (Array)
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:20480' // ตรวจสอบไฟล์ใน Array
        ]);

        DB::beginTransaction();
        try {
            // บันทึกข้อมูลสินค้าลงตาราง products หลักก่อน
            $productId = DB::table('products')->insertGetId([
                'name_th' => $request->name_th,
                'name_en' => $request->name_en,
                'description_th' => $request->description_th,
                'description_en' => $request->description_en,
                'type' => $request->type,
                'price' => $request->price,
                'compare_at_price' => $request->compare_at_price,
                'point_earn' => $request->point_earn ?? 0,
                'image_url' => null, // คอลัมน์เก่าปล่อยว่าง หรือใส่รูปแรกเป็น Cover ได้ด้านล่าง
                'is_contact_only' => $request->has('is_contact_only'),
                'is_active' => $request->has('is_active'),
                // 🌟 ข้อมูลเพิ่มเติมสำหรับตัดสินใจซื้อ (เมลข้อ 3)
                'stock_quantity' => $request->stock_quantity,
                'brand' => $request->brand,
                'model' => $request->model,
                'warranty_months' => $request->warranty_months,
                'return_policy_th' => $request->return_policy_th,
                'shipping_fee' => $request->shipping_fee,
                'install_fee' => $request->install_fee,
                'compatible_with' => $request->compatible_with,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // วนลูปอัปโหลดรูปภาพทั้งหมดเข้าตารางย่อย product_images
            if ($request->hasFile('images')) {
                $coverUrl = null;
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('products', 'public');
                    $imageUrl = url('storage/' . $path);

                    if ($index === 0) {
                        $coverUrl = $imageUrl; // เก็บรูปแรกสุดไว้เป็นรูปหน้าปก
                    }

                    DB::table('product_images')->insert([
                        'product_id' => $productId,
                        'image_url' => $imageUrl,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // สั่งอัปเดตรูปแรกเข้าไปที่ตารางหลัก (Fallback เผื่อหน้าเว็บเก่าดึงใช้)
                if ($coverUrl) {
                    DB::table('products')->where('id', $productId)->update(['image_url' => $coverUrl]);
                }
            }

            DB::commit();
            return redirect()->route('admin.products')->with('success', 'เพิ่มสินค้า/บริการ และรูปภาพเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        $categories = DB::table('service_categories')->orderBy('created_at', 'desc')->get();

        // ดึงรายการรูปภาพเก่ามาแสดงในหน้าแก้ไขด้วย
        $productImages = DB::table('product_images')->where('product_id', $id)->orderBy('sort_order', 'asc')->get();

        return view('admin.products.form', [
            'product' => $product,
            'productImages' => $productImages, // ส่งก้อนรูปภาพไปหน้า View
            'categories' => $categories,
            'first_level_active_index' => 'products',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 🌟 2. อัปเกรดฟังก์ชันแก้ไขข้อมูลสินค้า (Multiple Images)
    public function update(Request $request, $id)
    {
        $request->validate([
            'name_th' => 'required|string',
            'name_en' => 'required|string',
            'description_th' => 'nullable|string',
            'description_en' => 'nullable|string',
            'type' => 'required|numeric',
            'price' => 'required|numeric',
            'stock_quantity' => 'nullable|integer|min:0',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'warranty_months' => 'nullable|integer|min:0',
            'return_policy_th' => 'nullable|string',
            'shipping_fee' => 'nullable|numeric|min:0',
            'install_fee' => 'nullable|numeric|min:0',
            'compatible_with' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:20480'
        ]);

        DB::beginTransaction();
        try {
            // อัปเดตข้อมูลตารางหลัก
            DB::table('products')->where('id', $id)->update([
                'name_th' => $request->name_th,
                'name_en' => $request->name_en,
                'description_th' => $request->description_th,
                'description_en' => $request->description_en,
                'type' => $request->type,
                'price' => $request->price,
                'compare_at_price' => $request->compare_at_price,
                'point_earn' => $request->point_earn ?? 0,
                'is_contact_only' => $request->has('is_contact_only'),
                'is_active' => $request->has('is_active'),
                // 🌟 ข้อมูลเพิ่มเติมสำหรับตัดสินใจซื้อ (เมลข้อ 3)
                'stock_quantity' => $request->stock_quantity,
                'brand' => $request->brand,
                'model' => $request->model,
                'warranty_months' => $request->warranty_months,
                'return_policy_th' => $request->return_policy_th,
                'shipping_fee' => $request->shipping_fee,
                'install_fee' => $request->install_fee,
                'compatible_with' => $request->compatible_with,
                'updated_at' => now()
            ]);

            // ถ้าแอดมินอัปโหลดรูปภาพเซ็ตใหม่เข้ามา
            if ($request->hasFile('images')) {
                // ลบไฟล์รูปเก่าใน Storage ทิ้งเพื่อประหยัดพื้นที่ดิสก์
                $oldImages = DB::table('product_images')->where('product_id', $id)->get();
                foreach ($oldImages as $oldImg) {
                    $relativePath = str_replace(url('storage/'), '', $oldImg->image_url);
                    Storage::disk('public')->delete($relativePath);
                }

                // เคลียร์ข้อมูลรูปภาพเก่าในตารางย่อยทิ้ง
                DB::table('product_images')->where('product_id', $id)->delete();

                // วนลูปบันทึกไฟล์รูปภาพชุดใหม่
                $coverUrl = null;
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('products', 'public');
                    $imageUrl = url('storage/' . $path);

                    if ($index === 0) {
                        $coverUrl = $imageUrl;
                    }

                    DB::table('product_images')->insert([
                        'product_id' => $id,
                        'image_url' => $imageUrl,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // อัปเดตรูปหน้าปกชุดใหม่ลงตารางหลัก
                if ($coverUrl) {
                    DB::table('products')->where('id', $id)->update(['image_url' => $coverUrl]);
                }
            }

            DB::commit();
            return redirect()->route('admin.products')->with('success', 'อัปเดตข้อมูลและรูปภาพสินค้าสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * 🌟 เพิ่ม method นี้ที่ขาดหายไป — route resource ('admin.products.destroy') ประกาศไว้อยู่แล้ว
     * แต่ไม่เคยมี method จริงมาก่อน (จะ error ถ้าเรียกใช้) แก้พร้อมกันตอนขยาย schema สินค้า
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $images = DB::table('product_images')->where('product_id', $id)->get();
            foreach ($images as $img) {
                $relativePath = str_replace(url('storage/'), '', $img->image_url);
                Storage::disk('public')->delete($relativePath);
            }
            DB::table('product_images')->where('product_id', $id)->delete();
            DB::table('products')->where('id', $id)->delete();

            DB::commit();
            return redirect()->route('admin.products')->with('success', 'ลบสินค้า/บริการเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage());
        }
    }
}
