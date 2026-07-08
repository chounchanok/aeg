<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartLockerCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'title_th', 'title_en', 'description_th', 'description_en', 'image_url', 'is_active'
    ];

    // เชื่อมกลับไปหาล็อกเกอร์ (1 หมวดหมู่ มีหลายล็อกเกอร์)
    public function lockers()
    {
        return $this->hasMany(SmartLocker::class, 'category_id');
    }
}