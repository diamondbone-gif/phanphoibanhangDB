<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCommissionRecovery extends Model
{
    protected $fillable = [
        'customer_commission_adjustment_id',
        'recovery_code',
        'amount',
        'recovery_method',
        'recovered_date',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'recovered_date' => 'date',
    ];
}
