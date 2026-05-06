<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // ดึงข้อมูลผู้ใช้ปัจจุบันไปแสดงที่หน้า My Account
    public function index()
    {
        $user = Auth::user();
        return view('frontend.my-account', compact('user'));
    }

    // อัปเดตข้อมูลผู้ใช้
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            // อนุญาตให้แก้ไขรหัสผ่านได้ (ถ้ากรอกมา)
            'password' => 'nullable|string|min:8|confirmed', 
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];

        if ($request->filled('password')) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return back()->with('success', 'อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้ว');
    }
}