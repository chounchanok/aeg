<?php

namespace App\Support;

/**
 * เลือกรูปตามภาษา (คอมเมนต์ข้อ 9) — ถ้าไม่มีรูปภาษาอังกฤษ fallback เป็นรูปภาษาไทย
 * เพิ่ม field ให้แอปใช้ได้ทันที: display_image_url / display_image_url_m (เลือกภาษาตาม Accept-Language แล้ว)
 */
class LocalizedImage
{
    public static function isEnglish(?string $lang): bool
    {
        return str_starts_with(strtolower((string) $lang), 'en');
    }

    public static function banner(object $banner, ?string $lang): object
    {
        $en = self::isEnglish($lang);
        $banner->image_url_en = $banner->image_url_en ?? null;
        $banner->image_url_m_en = $banner->image_url_m_en ?? null;
        $banner->display_image_url = ($en && !empty($banner->image_url_en)) ? $banner->image_url_en : $banner->image_url;
        $mobileTh = $banner->image_url_m ?? null;
        $mobileEn = $banner->image_url_m_en ?: $banner->image_url_en;
        $banner->display_image_url_m = ($en && !empty($mobileEn)) ? $mobileEn : ($mobileTh ?: $banner->display_image_url);
        return $banner;
    }

    public static function popup(object $popup, ?string $lang): object
    {
        $popup->image_url_en = $popup->image_url_en ?? null;
        $popup->display_image_url = (self::isEnglish($lang) && !empty($popup->image_url_en)) ? $popup->image_url_en : $popup->image_url;
        return $popup;
    }
}
