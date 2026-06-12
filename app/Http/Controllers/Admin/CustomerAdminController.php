<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerAdminController extends Controller
{
    // 1. หน้าแสดงรายชื่อลูกค้าทั้งหมด
    public function index()
    {
        $customers = DB::table('users')
            ->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')
            ->where('users.role', 'customer')
            ->select('users.id', 'users.username', 'users.email', 'users.phone', 'customer_profiles.first_name', 'customer_profiles.last_name', 'users.created_at')
            ->orderBy('users.created_at', 'desc')
            ->get();

        return view('admin.customers.index', [
            'customers' => $customers,
            'first_level_active_index' => 'customers',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 2. หน้าดูรายละเอียดลูกค้าและสินค้าที่ลูกค้ามี
    public function show($id)
    {
        $customer = DB::table('users')->leftJoin('customer_profiles', 'users.id', '=', 'customer_profiles.user_id')->where('users.id', $id)->select('users.*', 'customer_profiles.first_name', 'customer_profiles.last_name', 'customer_profiles.address', 'customer_profiles.profile_image_url')->first();
        if (!$customer) abort(404);

        $customerProducts = DB::table('customer_products')->where('customer_id', $id)->orderBy('created_at', 'desc')->get();
        $masterProducts = DB::table('products')->where('is_active', true)->get();

        // 🌟 ดึงข้อมูล Master ของประกันและตู้เซฟ
        $masterInsurances = DB::table('insurances')->get();
        $masterLockers = DB::table('smart_lockers')->get();

        return view('admin.customers.show', [
            'customer' => $customer,
            'customerProducts' => $customerProducts,
            'masterProducts' => $masterProducts,
            'masterInsurances' => $masterInsurances, // 🌟 ส่งตัวแปรเพิ่ม
            'masterLockers' => $masterLockers,       // 🌟 ส่งตัวแปรเพิ่ม
            'first_level_active_index' => 'customers',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    // 3. ฟังก์ชันบันทึกการเพิ่มสินค้าให้ลูกค้า
    public function storeProduct(Request $request, $id)
    {
        $request->validate([
            'master_product_id' => 'required',
            'serial_number' => 'nullable|string|unique:customer_products,serial_number',
            'purchase_date' => 'nullable|date',
            'warranty_expire_date' => 'nullable|date',
        ]);

        // ดึงชื่อสินค้าจาก Master มาบันทึก (ตามที่ออกแบบ DB ไว้ว่าเก็บแค่ชื่อ)
        $masterProduct = DB::table('products')->where('id', $request->master_product_id)->first();

        DB::table('customer_products')->insert([
            'customer_id' => $id,
            'product_name' => $masterProduct->name_th . ' / ' . $masterProduct->name_en,
            'serial_number' => $request->serial_number,
            'purchase_date' => $request->purchase_date,
            'warranty_expire_date' => $request->warranty_expire_date,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มสินค้าให้ลูกค้าเรียบร้อยแล้ว');
    }

    // ฟังก์ชันบันทึกการเพิ่ม ประกันภัย ให้ลูกค้า
    public function storeInsurance(Request $request, $id)
    {
        $request->validate(['master_insurance_id' => 'required', 'purchase_date' => 'required|date']);

        $insurance = DB::table('insurances')->where('id', $request->master_insurance_id)->first();
        $name = $insurance->title ?? $insurance->name_th ?? 'ประกันภัย';

        DB::table('customer_products')->insert([
            'customer_id' => $id,
            'product_name' => '[ประกันภัย] ' . $name, // 🌟 ใส่ Prefix
            'serial_number' => $request->policy_number, // ใช้ช่อง serial_number เก็บเลขกรมธรรม์
            'purchase_date' => $request->purchase_date,
            'warranty_expire_date' => $request->warranty_expire_date,
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มข้อมูลประกันภัยให้ลูกค้าเรียบร้อยแล้ว');
    }

    // ฟังก์ชันบันทึกการเพิ่ม ตู้เซฟนิรภัย ให้ลูกค้า
    public function storeLocker(Request $request, $id)
    {
        $request->validate(['master_locker_id' => 'required', 'purchase_date' => 'required|date']);

        $locker = DB::table('smart_lockers')->where('id', $request->master_locker_id)->first();
        $name = $locker->name ?? $locker->locker_name ?? 'ตู้เซฟนิรภัย';

        DB::table('customer_products')->insert([
            'customer_id' => $id,
            'product_name' => '[ตู้เซฟเช่า] ' . $name, // 🌟 ใส่ Prefix
            'serial_number' => $request->locker_number, // ใช้ช่อง serial_number เก็บหมายเลขตู้/พิน
            'purchase_date' => $request->purchase_date,
            'warranty_expire_date' => $request->warranty_expire_date,
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มข้อมูลตู้เซฟนิรภัยให้ลูกค้าเรียบร้อยแล้ว');
    }
}
