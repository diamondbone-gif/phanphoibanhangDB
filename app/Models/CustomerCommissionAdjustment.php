<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCommissionAdjustment extends Model
{
    protected $fillable = [
        'customer_commission_id',
        'customer_order_return_id',
        'adjustment_code',
        'adjustment_type',
        'amount',
        'recovered_amount',
        'status',
        'reason',
        'created_by',
        'recovered_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'recovered_amount' => 'decimal:2',
        'recovered_at' => 'datetime',
    ];
}
