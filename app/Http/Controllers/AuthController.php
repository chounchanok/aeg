<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginView()
    {
        // ส่งตัวแปร layout ไปให้ View
        return view('login.main', [
            'layout' => 'base', // ใช้ base layout เพราะหน้า login ไม่ต้องการเมนู sidebar
            'dark_mode' => false,
            'color_scheme' => 'default'
        ]); 
    }

    public function login(Request $request)
    {
        // ฝั่ง Frontend (Axios) ส่ง key มาชื่อ 'email' เราจะรับค่านี้มาตรวจสอบ
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'กรุณากรอกชื่อผู้ใช้งานหรืออีเมล',
            'password.required' => 'กรุณากรอกรหัสผ่าน'
        ]);

        // ลองล็อกอินด้วย username ก่อน ถ้าไม่ได้ค่อยลองล็อกอินด้วย email
        $loginSuccess = Auth::attempt(['username' => $request->email, 'password' => $request->password]) ||
                        Auth::attempt(['email' => $request->email, 'password' => $request->password]);

        if ($loginSuccess) {
            $request->session()->regenerate();
            // ตอบกลับเป็น JSON เพื่อให้ Axios ฝั่ง Frontend ทำงานต่อ (location.href = '/')
            return response()->json(['message' => 'Logged in successfully.']);
        }

        // ถ้าล็อกอินไม่ผ่าน ส่ง Error 422 กลับไปให้ Axios แสดงผลตัวแดง
        return response()->json([
            'message' => 'Wrong email or password.',
            'errors' => [
                'email' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง',
                'password' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง'
            ]
        ], 422);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}