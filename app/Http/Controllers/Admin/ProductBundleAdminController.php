<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 🌟 สินค้าจับกลุ่ม (Bundle) — แอดมินเลือกสินค้า 2 ชิ้นขึ้นไปมารวมเป็นชุด แล้วตั้ง "ราคาชุด" เอง
 * (ไม่ใช่ % ส่วนลด) ฝั่งมือถือจะไปคำนวณ/แนะนำจากตาราง product_bundles + product_bundle_items นี้
 * ที่ EcommerceController::getCart() / checkout()
 */
class ProductBundleAdminController extends Controller
{
    public function index()
    {
        $bundles = DB::table('product_bundles')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($bundles as $bundle) {
            $bundle->items = DB::table('product_bundle_items')
                ->join('products', 'product_bundle_items.product_id', '=', 'products.id')
                ->where('product_bundle_items.product_bundle_id', $bundle->id)
                ->select('products.id as product_id', 'products.name_th', 'products.price', 'product_bundle_items.quantity')
                ->get();

            $bundle->original_total = $bundle->items->sum(fn ($i) => $i->price * $i->quantity);
            $bundle->savings = max(0, $bundle->original_total - $bundle->bundle_price);
        }

        // 🌟 สินค้าที่เลือกเข้าบันเดิลได้ — กัน is_contact_only ออก เพราะสินค้ากลุ่มนั้นต้องขอใบเสนอราคา
        // ไม่ผ่านตะกร้าปกติ จึงไม่มีทางถูกซื้อพร้อมกันในตะกร้าเพื่อรับส่วนลดบันเดิลได้อยู่แล้ว
        $products = DB::table('products')
            ->where('is_active', true)
            // ->where('is_contact_only', false)
            ->orderBy('name_th')
            ->get(['id', 'name_th', 'price']);

        return view('admin.product-bundles.index', [
            'bundles' => $bundles,
            'products' => $products,
            'first_level_active_index' => 'product-bundles',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'bundle_price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'product_ids' => 'required|array|min:2',
            'product_ids.*' => 'integer|exists:products,id',
        ], [
            'product_ids.min' => 'กรุณาเลือกสินค้าอย่างน้อย 2 รายการเข้าชุด',
        ]);

        DB::beginTransaction();
        try {
            $bundleId = DB::table('product_bundles')->insertGetId([
                'name_th' => $request->name_th,
                'name_en' => $request->name_en,
                'bundle_price' => $request->bundle_price,
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->sort_order ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->syncBundleItems($bundleId, $request->product_ids);

            DB::commit();
            return redirect()->route('admin.product-bundles')->with('success', 'เพิ่มสินค้าจับกลุ่มเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'bundle_price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'product_ids' => 'required|array|min:2',
            'product_ids.*' => 'integer|exists:products,id',
        ], [
            'product_ids.min' => 'กรุณาเลือกสินค้าอย่างน้อย 2 รายการเข้าชุด',
        ]);

        $bundle = DB::table('product_bundles')->where('id', $id)->first();
        if (!$bundle) {
            return redirect()->route('admin.product-bundles')->with('error', 'ไม่พบชุดสินค้านี้ในระบบ');
        }

        DB::beginTransaction();
        try {
            DB::table('product_bundles')->where('id', $id)->update([
                'name_th' => $request->name_th,
                'name_en' => $request->name_en,
                'bundle_price' => $request->bundle_price,
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->sort_order ?? 0,
                'updated_at' => now(),
            ]);

            DB::table('product_bundle_items')->where('product_bundle_id', $id)->delete();
            $this->syncBundleItems($id, $request->product_ids);

            DB::commit();
            return redirect()->route('admin.product-bundles')->with('success', 'อัปเดตสินค้าจับกลุ่มเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::table('product_bundle_items')->where('product_bundle_id', $id)->delete();
        DB::table('product_bundles')->where('id', $id)->delete();

        return redirect()->route('admin.product-bundles')->with('success', 'ลบสินค้าจับกลุ่มเรียบร้อยแล้ว');
    }

    private function syncBundleItems(int $bundleId, array $productIds): void
    {
        $rows = collect($productIds)->unique()->map(fn ($productId) => [
            'product_bundle_id' => $bundleId,
            'product_id' => $productId,
            'quantity' => 1, // 🌟 v1: นับเป็นชุดต่อ 1 ชิ้นต่อสินค้า ยังไม่รองรับ qty มากกว่า 1 ต่อชิ้นจากหน้าแอดมิน
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all();

        DB::table('product_bundle_items')->insert($rows);
    }
}
