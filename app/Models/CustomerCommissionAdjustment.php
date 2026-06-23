<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCommissionAdjustment extends Model
{
    protected $fillable = [
        'customer_commission_id',
        'adjustment_code',
        'adjustment_type',
        'amount',
        'reason',
        'created_by',
    ];
}
