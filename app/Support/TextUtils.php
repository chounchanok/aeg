<?php

namespace App\Support;

class TextUtils
{
    /**
     * แปลงลิงก์ URL (http/https) ที่อยู่ในข้อความธรรมดาให้กลายเป็นลิงก์ที่คลิกได้
     *
     * เพื่อความปลอดภัย (ป้องกัน XSS จากข้อความที่แอดมิน/ระบบกรอกเข้ามาในตาราง notifications)
     * ฟังก์ชันนี้จะ escape ข้อความต้นฉบับทั้งหมดก่อนเสมอ แล้วค่อยแปลงเฉพาะส่วนที่เป็น URL
     * ให้กลายเป็น <a> tag ทีหลัง ดังนั้นต้อง render ผลลัพธ์ด้วย {!! !!} ใน Blade (ไม่ใช่ {{ }})
     * เพราะ escape เองไว้แล้วในฟังก์ชันนี้
     */
    public static function linkify(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // 1. Escape ข้อความทั้งหมดก่อน ป้องกันการฝัง HTML/JS แปลกปลอมมากับข้อความ
        $escaped = e($text);

        // 2. หา URL (http:// หรือ https://) ในข้อความที่ escape แล้ว แล้วแปลงเป็นลิงก์
        $pattern = '/(https?:\/\/[^\s<]+)/i';

        return preg_replace_callback($pattern, function ($matches) {
            $url = $matches[1];

            // ตัดเครื่องหมายวรรคตอนท้ายประโยคที่อาจติดมากับ URL ออก เช่น . , ! ? ; :
            $trailing = '';
            while ($url !== '' && preg_match('/[.,!?;:]$/', $url)) {
                $trailing = substr($url, -1) . $trailing;
                $url = substr($url, 0, -1);
            }

            if ($url === '') {
                return $matches[1];
            }

            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer nofollow" style="word-break: break-all;">' . $url . '</a>' . $trailing;
        }, $escaped);
    }
}
