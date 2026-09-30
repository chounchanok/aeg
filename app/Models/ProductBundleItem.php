<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_bundle_id', 'product_id', 'quantity',
    ];

    public function bundle()
    {
        return $this->belongsTo(ProductBundle::class, 'product_bundle_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
