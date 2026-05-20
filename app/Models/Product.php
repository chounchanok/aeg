<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_th', 'name_en', 'description_th', 'description_en', 'is_recommended',
        'type', 'price', 'compare_at_price', 'image_url', 
        'point_earn', 'is_active'
    ];

    public function category()
    {
        // สั่งให้มันรู้ว่าคอลัมน์ 'type' ในตาราง products ไปตรงกับ 'id' ของ categories
        return $this->belongsTo(ServiceCategory::class, 'type', 'id');
    }
}