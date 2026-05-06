<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // ถ้าล็อกอินแล้ว และ "ไม่ใช่" customer ให้ผ่านได้
        if (Auth::check() && Auth::user()->role !== 'customer') {
            return $next($request);
        }

        // ถ้าเป็น customer แอบเข้า admin ให้เตะกลับไปหน้าหลัก
        return redirect('/');
    }
}
