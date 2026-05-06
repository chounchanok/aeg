<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'address_id', 'subtotal', 
        'discount', 'total_amount', 'status', 'payment_gateway', 
        'gateway_transaction_id', 'gateway_response'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}