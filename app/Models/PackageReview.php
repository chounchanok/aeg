<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageReview extends Model
{
    use HasFactory;

    protected $fillable = ['order_item_id', 'user_id', 'install_rating', 'sales_rating', 'review_text'];

    // ผู้ที่รีวิว
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // แพ็กเกจที่โดนรีวิว
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
