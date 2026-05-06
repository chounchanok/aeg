<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    // ระบุชื่อตาราง (ป้องกัน Laravel ผันชื่อตารางผิด)
    protected $table = 'banners';

    // ระบุฟิลด์ที่อนุญาตให้ใช้งานได้ (อ้างอิงจากข้อมูลที่หน้าเว็บต้องใช้)
    protected $fillable = [
        'title',
        'image_url',
        'link_url',
        'sort_order',
        'is_active',
    ];
}
