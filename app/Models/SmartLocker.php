<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartLocker extends Model
{
    use HasFactory;

    protected $table = 'smart_lockers';

    protected $fillable = [
        'locker_number',
        'type',
        'category', // 🌟 เพิ่มคอลัมน์ใหม่ตรงนี้
        'title_th',
        'title_en',
        'description_th',
        'description_en',
        'price',
        'image_url',
        'status',
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(SmartLockerCategory::class, 'category_id');
    }
}