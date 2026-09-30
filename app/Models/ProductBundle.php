<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_th', 'name_en', 'bundle_price', 'is_active', 'sort_order',
    ];

    public function items()
    {
        return $this->hasMany(ProductBundleItem::class);
    }
}
