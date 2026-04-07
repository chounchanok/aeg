<?php

namespace App\Http\Controllers;

use App\Http\Request\LoginRequest;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginView()
    {
        return view('login.main', [
            'layout' => 'login'
        ]);
    }

    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function email()
    {
        return 'email'; // ✅ เปลี่ยนจาก 'email' เป็น 'username'
    }


    public function login(\Illuminate\Http\Request $request)
    {
        // ตรวจสอบค่า email และ password
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // รับค่า email และ password
        $credentials = $request->only('email', 'password'); 

        // ลองล็อกอิน
        if (!\Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'Wrong email or password.']);
        }

        return redirect()->intended('/'); // ส่งไปยังหน้าหลักหลังล็อกอินสำเร็จ
    }

    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        \Auth::logout();
        return redirect('login');
    }
}
