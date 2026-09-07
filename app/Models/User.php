<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username', // เพิ่มคอลัมน์นี้
        'email',
        'password',
        'phone', // เพิ่มคอลัมน์นี้
        'role', // เพิ่มคอลัมน์นี้
        'is_active', // เพิ่มคอลัมน์นี้
        'google_id', // เพิ่มคอลัมน์นี้
        'line_id', // เพิ่มคอลัมน์นี้
        'facebook_id', // เพิ่มคอลัมน์นี้
        'apple_id', // เพิ่มคอลัมน์นี้
        'phone_verified_at', // เพิ่มคอลัมน์นี้
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==========================================
    // RBAC (Role-Based Access Control) — ระบบสิทธิ์ละเอียดระดับโมดูล
    // แยกจากคอลัมน์ 'role' เดิม (customer/technician/admin/super_admin/content_admin)
    // ซึ่งยังใช้แค่แยก "ลูกค้า vs พนักงาน" เหมือนเดิม ไม่กระทบกัน
    // ==========================================

    /**
     * บทบาท (role) ทั้งหมดที่ user คนนี้สวมอยู่ (สวมได้พร้อมกันหลายอัน)
     */
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class, 'user_roles', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * เช็คว่า user มี role (key) นี้อยู่ไหม เช่น $user->hasRole('security_admin')
     */
    public function hasRole(string $roleKey): bool
    {
        return $this->roles()->where('key', $roleKey)->exists();
    }

    /**
     * เช็คว่า user มีสิทธิ์ (permission key) นี้หรือไม่ เช่น $user->hasPermission('products.manage')
     * role ที่ตั้ง is_full_access = true (เช่น 'it') จะผ่านทุก permission โดยอัตโนมัติ
     */
    public function hasPermission(string $permissionKey): bool
    {
        // ใช้ permissionKeys() ที่ cache ไว้ต่อ request แทนการ query ใหม่ทุกครั้ง —
        // เพราะตอนนี้มีการเช็คสิทธิ์หลายจุดใน 1 หน้า (middleware + @can ในเมนู/ปุ่มต่างๆ)
        $keys = $this->permissionKeys();

        return in_array('*', $keys, true) || in_array($permissionKey, $keys, true);
    }

    /**
     * เช็คสิทธิ์แบบรวมเงื่อนไข backward-compat: users.role = 'super_admin' ผ่านทุกอย่างเสมอ
     * (เหมือน CheckPermission middleware) — ใช้ใน Blade ผ่าน @can('xxx.manage') ได้เลย
     * (ดู Gate::before ใน AppServiceProvider)
     */
    public function canAccess(string $permissionKey): bool
    {
        return $this->role === 'super_admin' || $this->hasPermission($permissionKey);
    }

    /**
     * cache ผลลัพธ์ permissionKeys() ต่อ instance (หมดอายุพร้อม request) — เรียก forgetPermissionCache()
     * ถ้าเพิ่งเปลี่ยน role ของ user คนเดียวกันใน request เดียวกัน
     */
    protected ?array $permissionKeyCache = null;

    public function forgetPermissionCache(): void
    {
        $this->permissionKeyCache = null;
    }

    /**
     * รายการ permission key ทั้งหมดที่ user มี (ใช้สำหรับ filter เมนู sidebar)
     * คืนค่า ['*'] ถ้า user มี role แบบ full access (IT)
     */
    public function permissionKeys(): array
    {
        if ($this->permissionKeyCache !== null) {
            return $this->permissionKeyCache;
        }

        $roleIds = $this->roles()->pluck('roles.id');

        if ($roleIds->isEmpty()) {
            return $this->permissionKeyCache = [];
        }

        $hasFullAccess = \App\Models\Role::whereIn('id', $roleIds)->where('is_full_access', true)->exists();
        if ($hasFullAccess) {
            return $this->permissionKeyCache = ['*'];
        }

        return $this->permissionKeyCache = \App\Models\Permission::whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('roles.id', $roleIds);
            })
            ->pluck('key')
            ->toArray();
    }
}
