<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * เช็คสิทธิ์ระดับโมดูล (RBAC) ต่อจาก middleware 'admin' (CheckAdminRole) เดิม
 * ใช้แบบ: Route::middleware('permission:products.manage')->group(...)
 *
 * หมายเหตุช่วงเปลี่ยนผ่าน: user ที่มี users.role = 'super_admin' (ตามคอลัมน์ role เดิม
 * ก่อนมีระบบ RBAC นี้) จะได้สิทธิ์ผ่านทุกอย่างเสมอ เพื่อไม่ให้แอดมินเดิมถูกล็อกออกจากระบบ
 * ทันทีหลัง deploy จนกว่าฝ่าย IT จะ assign RBAC role ใหม่ (เช่น 'it') ให้ครบทุกคน
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        if ($user->role === 'super_admin') {
            return $next($request);
        }

        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้ กรุณาติดต่อฝ่าย IT เพื่อขอสิทธิ์การใช้งาน');
    }
}
