<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InsuranceAdminController extends Controller
{
    // 1. หน้าแสดงรายการประกันภัยทั้งหมด (Read)
    public function index()
    {
        $insurances = Insurance::orderBy('sort_order', 'asc')->paginate(10);
        return view('admin.insurance.index', compact('insurances'));
    }

    // 2. หน้าฟอร์มเพิ่มข้อมูลใหม่ (Create)
    public function create()
    {
        return view('admin.insurance.form');
    }

    // 3. บันทึกข้อมูลใหม่ลงฐานข้อมูล (Store)
    public function store(Request $request)
    {
        $request->validate([
            'title_th' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:102400', // อัปโหลดรูปได้สูงสุด 100MB
            'image_inside' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:102400', // อัปโหลดรูปได้สูงสุด 100MB
        ]);

        $data = $request->except(['_token', 'image', 'image_inside']);

        // จัดการอัปโหลดรูปภาพ
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('insurances', 'public');
            $data['image_url'] = url('storage/' . $path);
        }

        if ($request->hasFile('image_inside')) {
            $path = $request->file('image_inside')->store('insurances', 'public');
            $data['image_inside_url'] = url('storage/' . $path);
        }

        Insurance::create($data);

        return redirect()->route('admin.insurances.index')->with('success', 'เพิ่มข้อมูลประกันภัยเรียบร้อยแล้ว');
    }

    // 4. หน้าฟอร์มแก้ไขข้อมูล (Edit)
    public function edit($id)
    {
        $insurance = Insurance::findOrFail($id);
        return view('admin.insurance.form', compact('insurance'));
    }

    // 5. บันทึกการแก้ไขข้อมูล (Update)
    public function update(Request $request, $id)
    {
        $request->validate([
            'title_th' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:102400',
            'image_inside' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:102400',
        ]);

        $insurance = Insurance::findOrFail($id);
        $data = $request->except(['_token', '_method', 'image', 'image_inside']);

        // จัดการอัปโหลดรูปภาพใหม่ (ถ้ามีการแนบมา)
        if ($request->hasFile('image')) {
            // ลบรูปเก่าทิ้งก่อน (เพื่อไม่ให้รก Server)
            if ($insurance->image_url) {
                $oldPath = str_replace(url('storage') . '/', '', $insurance->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            // อัปโหลดรูปใหม่
            $path = $request->file('image')->store('insurances', 'public');
            $data['image_url'] = url('storage/' . $path);
        }


        if ($request->hasFile('image_inside')) {
            // ลบรูปเก่าทิ้งก่อน (เพื่อไม่ให้รก Server)
            if ($insurance->image_inside_url) {
                $oldPath = str_replace(url('storage') . '/', '', $insurance->image_inside_url);
                Storage::disk('public')->delete($oldPath);
            }

            // อัปโหลดรูปใหม่
            $path = $request->file('image_inside')->store('insurances', 'public');
            $data['image_inside_url'] = url('storage/' . $path);
        }
        // dd($request->hasFile('image_inside'), $data);

        $insurance->update($data);

        return redirect()->route('admin.insurances.index')->with('success', 'อัปเดตข้อมูลประกันภัยเรียบร้อยแล้ว');
    }

    // 6. ลบข้อมูล (Delete)
    public function destroy($id)
    {
        $insurance = Insurance::findOrFail($id);

        // ลบรูปภาพออกจาก Server
        if ($insurance->image_url) {
            $oldPath = str_replace(url('storage') . '/', '', $insurance->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $insurance->delete();

        return redirect()->route('admin.insurances.index')->with('success', 'ลบข้อมูลประกันภัยเรียบร้อยแล้ว');
    }
}