<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmartLockerAdminController extends Controller
{
    public function index()
    {
        $lockers = DB::table('smart_lockers')->orderBy('type')->orderBy('locker_number')->get();

        return view('admin.smart-lockers.index', [
            'lockers' => $lockers,
            'first_level_active_index' => 'smart-lockers',
            'second_level_active_index' => '',
            'third_level_active_index' => ''
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'locker_number' => 'required|unique:smart_lockers',
            'type' => 'required|in:PRIME,PRIVILEGE',
            'title_th' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048'
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('smart-lockers', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('smart_lockers')->insert([
            'locker_number' => $request->locker_number,
            'type' => $request->type,
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'price' => $request->price,
            'image_url' => $imageUrl,
            'status' => $request->status ?? 'available',
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'เพิ่มตู้เซฟเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'locker_number' => 'required|unique:smart_lockers,locker_number,' . $id,
            'title_th' => 'required|string',
            'price' => 'required|numeric'
        ]);

        $locker = DB::table('smart_lockers')->where('id', $id)->first();
        $imageUrl = $locker->image_url;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('smart-lockers', 'public');
            $imageUrl = url('storage/' . $path);
        }

        DB::table('smart_lockers')->where('id', $id)->update([
            'locker_number' => $request->locker_number,
            'type' => $request->type,
            'title_th' => $request->title_th,
            'title_en' => $request->title_en,
            'price' => $request->price,
            'image_url' => $imageUrl,
            'status' => $request->status ?? 'available',
            'is_active' => $request->has('is_active'),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'อัปเดตตู้เซฟเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        DB::table('smart_lockers')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'ลบตู้เซฟเรียบร้อยแล้ว');
    }
}