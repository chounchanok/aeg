<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceContract extends Model
{
    protected $fillable = [
        'contract_number',
        'user_id',
        'customer_product_id',
        'title',
        'description',
        'billing_amount',
        'billing_day',
        'payment_method',
        'start_date',
        'end_date',
        'status',
        'next_billing_month',
        'last_invoiced_month',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_month' => 'date',
        'last_invoiced_month' => 'date',
        'billing_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
