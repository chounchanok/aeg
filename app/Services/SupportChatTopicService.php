<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;

/**
 * ตัวช่วยจัดการ "หัวข้อแชทติดต่อสอบถาม" (config/support_chat.php)
 * ใช้ร่วมกันทั้งฝั่งลูกค้า (เว็บ/แอป: เลือกหัวข้อ, validate) และหลังบ้าน (กรองให้แต่ละแผนกเห็นเฉพาะหัวข้อของตัวเอง)
 */
class SupportChatTopicService
{
    /** @return array<string, array> key => ['label', 'label_en', 'icon', 'roles'] */
    public static function topics(): array
    {
        return config('support_chat.topics', []);
    }

    public static function defaultTopic(): string
    {
        return config('support_chat.default_topic', 'general');
    }

    public static function isValid(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::topics());
    }

    public static function label(?string $key): string
    {
        return self::topics()[$key]['label'] ?? ($key ?: '-');
    }

    /** role key ของแผนกที่รับผิดชอบหัวข้อนี้ */
    public static function roleKeysForTopic(string $key): array
    {
        return self::topics()[$key]['roles'] ?? [];
    }

    /**
     * รายการหัวข้อสำหรับส่งให้ลูกค้าเลือก (เว็บ/แอป) — เฉพาะข้อมูลที่จำเป็น
     */
    public static function forCustomer(): array
    {
        $list = [];
        foreach (self::topics() as $key => $t) {
            $list[] = [
                'key' => $key,
                'label' => $t['label'],
                'label_en' => $t['label_en'] ?? $t['label'],
                'icon' => $t['icon'] ?? null,
            ];
        }
        return $list;
    }

    /**
     * หัวข้อที่ user (พนักงาน) คนนี้เห็น/ตอบได้ — IT (full access) และ super_admin เดิม เห็นทุกหัวข้อ
     * @return string[] topic keys
     */
    public static function allowedTopicsForUser(User $user): array
    {
        $all = array_keys(self::topics());

        if (self::hasFullAccess($user)) {
            return $all;
        }

        $userRoleKeys = $user->roles()->pluck('key')->all();

        return array_values(array_filter($all, function ($key) use ($userRoleKeys) {
            return count(array_intersect(self::roleKeysForTopic($key), $userRoleKeys)) > 0;
        }));
    }

    public static function canAccessTopic(User $user, ?string $topic): bool
    {
        // IT / super_admin เปิดได้ทุกหัวข้อ รวมถึงหัวข้อเก่าที่ไม่อยู่ใน config แล้ว (ข้อมูลเดิมในตาราง)
        if (self::hasFullAccess($user)) {
            return $topic !== null && $topic !== '';
        }

        return self::isValid($topic) && in_array($topic, self::allowedTopicsForUser($user), true);
    }

    /**
     * IT (role แบบ full access) หรือ users.role = super_admin เดิม → เห็น/ตอบได้ทุกหัวข้อ
     */
    public static function hasFullAccess(User $user): bool
    {
        return $user->role === 'super_admin' || in_array('*', $user->permissionKeys(), true);
    }

    /**
     * ชื่อแผนก (roles.name) ของแต่ละหัวข้อ สำหรับแสดงในหลังบ้าน เช่น ['insurance' => 'Insurance', ...]
     * @return array<string, string>
     */
    public static function departmentNames(): array
    {
        $roleNames = Role::pluck('name', 'key')->all();
        $out = [];
        foreach (self::topics() as $key => $t) {
            $names = array_map(fn ($r) => $roleNames[$r] ?? $r, $t['roles'] ?? []);
            $out[$key] = implode(', ', $names) ?: '-';
        }
        return $out;
    }
}
