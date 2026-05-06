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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // ตรวจสอบ Role ของผู้ใช้งานเพื่อแยกการ Redirect
            if (Auth::user()->role === 'customer') {
                return redirect()->intended('/'); // ลูกค้าไปหน้า Frontend
            }

            // ถ้าไม่ใช่ customer (เป็น admin/staff) ไปหน้า Backend
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $role = Auth::user()->role ?? 'customer'; // เก็บ role ไว้ก่อน logout

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect กลับไปหน้า Login
        return redirect('/login');
    }
}
