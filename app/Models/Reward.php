<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $table = 'rewards';

    protected $fillable = [
        'title',
        'description',
        'points_required',
        'discount_amount', // 🌟 เพิ่มตรงนี้
        'image_url',
        'is_active',
        'return_policy',     // เงื่อนไขการยกเลิกหรือคืนคะแนน
        'shipping_fee',      // ค่าจัดส่ง (บาท)
        'delivery_estimate', // ระยะเวลาจัดส่ง
    ];
}