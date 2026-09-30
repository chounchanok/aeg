<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'type',
        'user_id',
        'service_contract_id',
        'billing_month',
        'issue_date',
        'due_date',
        'customer_name_snapshot',
        'customer_tax_id_snapshot',
        'customer_branch_snapshot',
        'customer_address_snapshot',
        'subtotal',
        'vat_amount',
        'total_amount',
        'payment_method',
        'status',
        'paid_at',
        'gateway_transaction_id',
        'gateway_response',
        'payment_slip_url',
        'admin_note',
        'generated_by',
    ];

    protected $casts = [
        'billing_month' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'gateway_response' => 'array',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contract()
    {
        return $this->belongsTo(ServiceContract::class, 'service_contract_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
