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
            $user = Auth::user();

            // 🌟 ช่างซ่อม: ใช้ได้เฉพาะเมนู "งานของฉัน" (+ แจ้งเตือนของตัวเอง) — ไม่เห็นข้อมูลลูกค้า/งานทั้งหมด
            //    ยกเว้นช่างที่ IT ผูกแผนก (RBAC) ไว้ให้ จะเข้าเมนูตามสิทธิ์ของแผนกนั้นได้ด้วย
            if ($user->role === 'technician' && !$this->technicianMayAccess($request, $user)) {
                return redirect()->route('admin.tech.jobs');
            }

            return $next($request);
        }

        // ถ้าเป็น customer แอบเข้า admin ให้เตะกลับไปหน้าหลัก
        return redirect('/');
    }

    private function technicianMayAccess(Request $request, $user): bool
    {
        $name = (string) optional($request->route())->getName();
        if (str_starts_with($name, 'admin.tech.') || str_starts_with($name, 'admin.staff-notifications.')) {
            return true;
        }
        // มีแผนก/สิทธิ์ที่ได้รับเพิ่ม → ให้ middleware permission ของแต่ละหน้าตัดสินต่อ
        // (หน้าที่ไม่ผูก permission เช่น dashboard / ลูกค้า ยังไม่ให้เข้า)
        if (method_exists($user, 'permissionKeys') && !empty($user->permissionKeys())) {
            $mw = collect(optional($request->route())->gatherMiddleware() ?? []);
            return $mw->contains(fn ($m) => is_string($m) && str_starts_with($m, 'permission:'));
        }
        return false;
    }
}
